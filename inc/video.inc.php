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

	public $files = [];

	private $_legend_file = "";

	function set_filename($filename) {
		$this->path = self::$directory.'/'.$filename;

		if ($this->quality) {
			$this->path .= '-' . $this->quality;
		}

		$this->path .= '.webm';
	}

	function path() {
		if (empty($this->path)) {
			$this->path = self::$directory.'/'.gmdate('Y-m-d', $this->start).'-'.$this->start;

			if ($this->quality) {
				$this->path .= '-' . $this->quality;
			}

			$this->path .= '.webm';
		}

		return $this->path;
	}

	function make_with_legend() {
		$legend_file = tempnam("/tmp", "veranda_legend"); unlink($legend_file); $legend_file .= '.webm';
		$video_file = tempnam("/tmp", "veranda_legend"); unlink($video_file); $video_file .= '.webm';

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
		`/usr/bin/ffmpeg -y \
			-i {$left} -i {$right} \
			-filter_complex '[0][1]hstack' \
			-c:v vp9 -crf 30 -b:v 0 -threads 8 -pix_fmt yuv420p \
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

		`/usr/bin/ffmpeg -y -safe 0 \
			-f concat -i "{$playlist}" -vsync vfr \
			-c:v vp9 -crf 30 -b:v 0 {$vf} -threads 8 -pix_fmt yuv420p \
			-r {$this->fps} \
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

		if ($this->files) {
			$lines = array_map(function($file) {
				return "file '{$file}'\n";
			}, $this->files);
		} else if ($this->photos) {
			$lines = array_map(function($photo) {
				return "file '{$photo->best_quality()}'\n";
			}, $this->photos);
		}

		file_put_contents($playlist, implode("duration ".(1/$this->fps)."\n", $lines));

		if ($this->quality == "hd") {
			$vf = "";
		} else if (!$this->quality) {
			$vf = "-vf scale=1080:-2";
		} else {
			$vf = "-vf scale=".$this->quality;
		}

		if ($this->blur) {
			$filter = "-filter:v setpts=0.5*PTS,hqdn3d=4:3:6:4";
		} else {
			$filter = "";
		}

		`/usr/bin/ffmpeg -y -safe 0 \
			-f concat -i "{$playlist}" -vsync vfr \
			-c:v vp8 -crf 30 -b:v 0 {$vf} {$filter} -threads 8 -pix_fmt yuv420p \
			-r {$this->fps} \
			"{$path}"`;

		unlink($playlist);

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
			$videos = self::select(['place_id' => $place->id, 'start > 0'], 'stop DESC', 1);
			if (count($videos)) {
				$latest_videos[] = array_shift($videos);
			}

			$videos = self::select(['place_id' => $place->id, 'start' => 0], 'stop DESC', 1);
			if (count($videos)) {
				$latest_videos[] = array_shift($videos);
			}
		}

		return $latest_videos;
	}
}

