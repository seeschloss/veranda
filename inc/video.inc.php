<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Video extends Record {
	public static $table = "videos";
	public static $directory = __DIR__.'/../photos';

	public $id;
	public $start = 0;
	public $stop = 0;
	public $path = "";
	public $fps = 10;
	public $quality = "";

	public $photos = [];
	public $blur = false;

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
			$this->path = self::$directory.'/'.gmdate('Y-m-d', $this->start);

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

		$this->make_legend($legend_file);
		$this->make_video($video_file);

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

	function make_legend($path) {
		if (empty($this->photos)) {
			$this->photos = Photo::select(['timestamp BETWEEN '.(int)$this->start.' AND '.(int)$this->stop], 'timestamp ASC');
		}

		$playlist = tempnam("/tmp", "veranda");

		$temporary_files = [];

		$lines = [];
		foreach ($this->photos as $photo) {
			if ($this->quality == "hd") {
				$legend_frame = imagecreatetruecolor(360, 1440);
			} else {
				$legend_frame = imagecreatetruecolor(360, 1440);
			}

			$legend_frame = imagecreatetruecolor(360, 1440);
			$white = imagecolorallocate($legend_frame, 255, 255, 255);
			//imagettftext($legend_frame, 10, 0, 0, 0, $white, "/usr/share/fonts/TTF/verdana.ttf", date("Y-m-d H:i:s", $photo->timestamp));
			imagestring($legend_frame, 5, 0, 0, date("Y-m-d H:i:s", $photo->timestamp), $white);
			
			$temporary_file = tempnam("/tmp", "veranda_legend_frame"); unlink($temporary_file); $temporary_file .= '.png';
			$temporary_files[] = $temporary_file;
			imagepng($legend_frame, $temporary_file);

			$lines[] = "file '{$temporary_file}'\n";
		}

		file_put_contents($playlist, implode("duration ".(1/$this->fps)."\n", $lines));

		`/usr/bin/ffmpeg -y -safe 0 \
			-f concat -i "{$playlist}" -vsync vfr \
			-c:v vp9 -crf 30 -b:v 0 -threads 8 -pix_fmt yuv420p \
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

		$lines = array_map(function($photo) {
			return "file '{$photo->best_quality()}'\n";
		}, $this->photos);

		file_put_contents($playlist, implode("duration ".(1/$this->fps)."\n", $lines));

		if ($this->quality == "hd") {
			$vf = "";
		} else {
			$vf = "-vf scale=1080:1440";
		}

		if ($this->blur) {
			$filter = "-filter:v setpts=0.5*PTS,hqdn3d=4:3:6:4";
		} else {
			$filter = "";
		}

		`/usr/bin/ffmpeg -y -safe 0 \
			-f concat -i "{$playlist}" -vsync vfr \
			-c:v vp9 -crf 30 -b:v 0 {$vf} {$filter} -threads 8 -pix_fmt yuv420p \
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
}

