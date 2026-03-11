<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class File extends Record {
	public static $table = "files";
	public static $directory = __DIR__.'/../files';

	public $id;
	public $path;
	public $name;
	public $size;

	function save($data) {
		$this->save_file($data);
		$this->insert();
	}

	function save_file($data) {
		self::write_to($this->path(), $data);
		$this->size = strlen($data);
	}

	static function write_to($file, $data) {
		file_put_contents($file, $data);
	}

	function path() {
		if (!$this->path) {
			$directory = self::$directory.'/'.gmdate('Y-m-d');

			if (!file_exists($directory)) {
				mkdir($directory);
			}

			$this->path = $directory.'/'.$this->name;
		}

		return $this->path;
	}

	function url($ssl = true) {
		$url = $GLOBALS['config']['base_path']."/file/{$this->id}/{$this->name}";

		if (!$ssl) {
			$url = str_replace("https://", "http://", $url);
		}

		return $url;
	}

	function insert() {
		$db = new DB();

		$fields = [
			'path' => $db->escape($this->path),
			'name' => $db->escape($this->name),
			'size' => (int)$this->size,
		];

		$query = 'INSERT INTO `'.self::$table.'` (' . implode(',', array_keys($fields))   . ') '.
		                                 'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function parse_firmware_version() {
		if (file_exists($this->path()) and filesize($this->path()) < 1024 * 1024 * 50) {
			$data = file_get_contents($this->path());

			$tag = "ATHENA_FIRMWARE_VERSION:";

			if (($pos = strpos($data, $tag)) !== false) {
				if (($end = strpos($data, "\0", $pos)) !== false) {
					$version = substr($data, $pos + strlen($tag), $end - $pos - strlen($tag));

					return $version;
				}
			}
		}

		return null;
	}
}
