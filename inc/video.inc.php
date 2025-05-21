<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Video extends Record {
	public static $table = "videos";
	public static $relations = ['place_id' => 'Place'];
	public static $directory = __DIR__.'/../photos';

	public $id;
	public $place_id;
	public $start = 0;
	public $stop = 0;
	public $path = "";
	public $fps = 10;
	public $quality = "";

	public $photos = [];
	public $blur = false;

	public $hardware_encoding = true;
	public $encoding_format = "x265";

	public $files = [];

	private $_legend_file = "";

	function ffmpeg_bin() {
		return $this->hardware_encoding ? "/usr/bin/ffmpeg -vaapi_device /dev/dri/renderD128" : "/usr/bin/ffmpeg";
	}

	function ffmpeg_filter_append() {
		return $this->hardware_encoding ? "format=nv12|vaapi,hwupload" : "";
	}

	function ffmpeg_codec() {
		if ($this->hardware_encoding) {
			return "-an -c:v hevc_vaapi -profile:v main -pix_fmt vaapi -movflags +faststart";
		}

		switch ($this->encoding_format) {
			case "mp4":
			case "x265":
				return "-an -c:v libx265 -crf 24 -profile:v main -pix_fmt yuv420p -movflags +faststart";
			case "av1":
				return "-an -c:v libsvtav1 -qp 30 -tile-columns 2 -tile-rows 2 -pix_fmt yuv420p";
		}
	}

	function ffmpeg_extension() {
		return "mp4";
	}

	function set_filename($filename) {
		$this->path = self::$directory.'/'.$filename;

		if ($this->quality) {
			$this->path .= '-' . $this->quality;
		}

		$this->path .= '.'.$this->ffmpeg_extension();
	}

	function path() {
		if (empty($this->path)) {
			$this->path = self::$directory.'/'.gmdate('Y-m-d', $this->start).'-'.$this->start;

			if ($this->quality) {
				$this->path .= '-' . $this->quality;
			}

			$this->path .= '.'.$this->ffmpeg_extension();
		}

		return $this->path;
	}

	function make_with_legend() {
		$legend_file = tempnam("/tmp", "veranda_legend"); unlink($legend_file); $legend_file .= '.'.$this->ffmpeg_extension();
		$video_file = tempnam("/tmp", "veranda_legend"); unlink($video_file); $video_file .= '.'.$this->ffmpeg_extension();

		$this->make_video($video_file);
		$height = (int)`/usr/bin/ffprobe -v quiet -print_format flat -select_streams v:0 -show_entries stream=height '{$video_file}' | cut -d= -f2`;
		$this->make_legend($legend_file, $height);

		$this->stack_videos($legend_file, $video_file, $this->path());

		unlink($legend_file);
		unlink($video_file);

		$this->path = realpath($this->path());
		return file_exists($this->path);
	}

	function stack_videos($left, $right, $destination) {
		$filter_complex = "[0][1] hstack";
		if ($this->ffmpeg_filter_append()) {
			$filter_complex .= ",".$this->ffmpeg_filter_append();
		}

		`{$this->ffmpeg_bin()} -y \
			-i {$left} -i {$right} \
			-filter_complex "{$filter_complex}" \
			{$this->ffmpeg_codec()} \
			-r {$this->fps} \
			"{$destination}"`;

		return file_exists($destination);
	}

	function make_legend($path, $height) {
		if (empty($this->photos)) {
			$this->photos = Photo::select(['timestamp BETWEEN '.(int)$this->start.' AND '.(int)$this->stop], 'timestamp ASC');
		}

		$playlist = tempnam("/tmp", "veranda");

		$temporary_files = [];

		$lines = [];
		foreach ($this->photos as $photo) {
			$legend_frame = imagecreatetruecolor(360, 720);
			$white = imagecolorallocate($legend_frame, 255, 255, 255);

			$text_y = 20;
			$bounds = imagettftext($legend_frame, 14, 0, 5, $text_y, $white, __DIR__."/../fonts/verdana.ttf", $photo->place()->name);

			$text_y += ($bounds[1] - $bounds[7]) + 5;
			imagettftext($legend_frame, 14, 0, 5, $text_y, $white, __DIR__."/../fonts/verdana.ttf", gmdate("Y-m-d H:i:s", $photo->timestamp));
			
			$temporary_file = tempnam("/tmp", "veranda_legend_frame"); unlink($temporary_file); $temporary_file .= '.png';
			$temporary_files[] = $temporary_file;
			imagepng($legend_frame, $temporary_file);

			$lines[] = "file '{$temporary_file}'\n";
		}

		$vf = "-vf scale=-2:$height";

		file_put_contents($playlist, implode("duration ".(1/$this->fps)."\n", $lines));

		$keyrate = $this->fps/2; // one keyframe every 0,5 second

		`{$this->ffmpeg_bin()} -loglevel fatal -y -safe 0 \
			-f concat -i "{$playlist}" -r {$this->fps} -g {$keyrate} \
			{$this->ffmpeg_codec()} {$vf} \
			"{$path}"`;

		unlink($playlist);

		foreach ($temporary_files as $temporary_file) {
			unlink($temporary_file);
		}

		return file_exists($path);
	}

	function make() {
		$this->make_video($this->path());

		$this->path = realpath($this->path());
		return file_exists($this->path);
	}

	function make_video($path) {
		if (empty($this->photos)) {
			$this->photos = Photo::select(['timestamp BETWEEN '.(int)$this->start.' AND '.(int)$this->stop], 'timestamp ASC');
		}

		$playlist = tempnam("/tmp", "veranda");

		if (empty($this->files) and $this->photos) {
			$this->files = array_map(function($photo) { return $photo->best_quality(); }, $this->photos);
		}

		$this->files = array_filter($this->files, function($file) { return file_exists($file) and filesize($file) > 0; });

		file_put_contents($playlist, implode("duration ".(1/$this->fps)."\n", array_map(function($file) { return "file '{$file}'\n"; }, $this->files)));

		$video_filters = [
			"deflicker=size=7:mode=median",
		];

		if ($this->quality == "hd") {
		} else if (!$this->quality) {
			$video_filters[] = "scale=1080:-2";
		} else {
			$video_filters[] = "scale=".$this->quality;
		}

		if ($this->blur) {
			$video_filters[] = "setpts=0.5*PTS,hqdn3d=4:3:6:4";
		}

		if ($this->ffmpeg_filter_append()) {
			$video_filters[] = $this->ffmpeg_filter_append();
		}
		
		if (count($video_filters)) {
			$video_filters = "-filter:v ".join(",", array_map(fn($a) => "'{$a}'", $video_filters));
		} else {
			$video_filters = "";
		}

		$keyrate = $this->fps/2; // one keyframe every 0,5 second

		`{$this->ffmpeg_bin()} -y -safe 0 \
			-f concat -i "{$playlist}" -r {$this->fps} -g {$keyrate} \
			{$this->ffmpeg_codec()} {$video_filters} \
			"{$path}"`;

		unlink($playlist);

		$place = current($this->photos)->place();
		if ($place->mask) {
			$file = new File();
			$file->load(['id' => $place->mask]);

			$video_file = tempnam("/tmp", "veranda_legend"); unlink($video_file); $video_file .= '.'.$this->ffmpeg_extension();
			rename($path, $video_file);

			$filter_complex = "[0:v][1:v] overlay=0:0";
			if ($this->ffmpeg_filter_append()) {
				$filter_complex .= ",".$this->ffmpeg_filter_append();
			}

			`{$this->ffmpeg_bin()} -loglevel fatal -y \
				-i "{$video_file}" -i "{$file->path}" \
				{$this->ffmpeg_codec()} \
				-filter_complex "{$filter_complex}" \
				"{$path}"`;

			unlink($video_file);
		}

		return file_exists($path);
	}

	function save() {
		$this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'place_id' => (int)$this->place_id,
			'start' => (int)$this->start,
			'stop' => (int)$this->stop,
			'path' => $db->escape($this->path),
			'fps' => (int)$this->fps,
			'quality' => $db->escape($this->quality),
		];

		$query = 'INSERT INTO `'.self::$table.'` (' . implode(',', array_keys($fields))   . ') '.
		                                 'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	static function grid_row_header_admin() {
		return [
			'place' => __('Place'),
			'start' => __('Start'),
			'stop' => __('Stop'),
			'link' => __('Link'),
		];
	}

	function grid_row_admin() {
		return [
			'place' => "<a href='{$GLOBALS['config']['base_path']}/admin/place/{$this->place_id}/videos'>{$this->place->name}</a>",
			'start' => $this->start ? gmdate("r", $this->start) : "",
			'stop' => gmdate("r", $this->stop),
			'link' => "<a href='{$GLOBALS['config']['base_path']}/video/{$this->place_id}/{$this->id}'>/video/{$this->place_id}/{$this->id}</a>",
		];
	}

	static function select_latest_by_place($conditions = []) {
		$latest_videos = [];

		foreach (Place::select($conditions) as $place) {
			$videos = self::select(['place_id' => $place->id, 'start > 0'], 'id DESC', 1);
			if (count($videos)) {
				$latest_videos[] = array_shift($videos);
			}

			$videos = self::select(['place_id' => $place->id, 'start' => 0], 'id DESC', 1);
			if (count($videos)) {
				$latest_videos[] = array_shift($videos);
			}
		}

		return $latest_videos;
	}
}

