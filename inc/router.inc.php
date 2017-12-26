<?php

class Router {
	public $site = null;
	public $theme = null;

	public $routes = [];

	public $json = false;
	public $bare = false;

	public function __construct($site, $theme) {
		$this->site = $site;
		$this->theme = $theme;

		$this->routes = [
			'/data/device/([0-9]+)' => [$this, 'handle_device_data'],
			'/data/sensor/([0-9]+)' => [$this, 'handle_sensor_data'],
			'/data/place/([0-9]+)/photo' => [$this, 'handle_place_photo'],

			'/admin/device/([0-9]+)' => [$this, 'show_admin_device'],
			'/admin/devices' => [$this, 'show_admin_devices'],
			'/admin/sensor/([0-9]+)' => [$this, 'show_admin_sensor'],
			'/admin/sensors' => [$this, 'show_admin_sensors'],
			'/admin/plant/([0-9]+)/locate' => [$this, 'show_admin_plant_locate'],
			'/admin/plant/([0-9]+)' => [$this, 'show_admin_plant'],
			'/admin/plants' => [$this, 'show_admin_plants'],
			'/admin/place/([0-9]+)/photos/[0-9]*' => [$this, 'show_admin_place_photos_day'],
			'/admin/place/([0-9]+)/photos' => [$this, 'show_admin_place_photos'],
			'/admin/place/([0-9]+)' => [$this, 'show_admin_place'],
			'/admin/places' => [$this, 'show_admin_places'],
			'/admin/photo/([0-9]+)' => [$this, 'show_admin_photo'],
			'/admin/photos' => [$this, 'show_admin_photos'],
			'/admin/videos' => [$this, 'show_admin_videos'],
			'/admin/alert/([0-9]+)' => [$this, 'show_admin_alert'],
			'/admin/alerts' => [$this, 'show_admin_alerts'],
			'/admin/chart/([0-9]+)' => [$this, 'show_admin_chart'],
			'/admin/dashboard-photo/([0-9]+)' => [$this, 'show_admin_dashboard_photo'],
			'/admin/dashboard' => [$this, 'show_admin_dashboard'],
			'/admin/logout' => [$this, 'handle_admin_logout'],
			'/admin' => [$this, 'show_admin_plants'],

			'/water' => [$this, 'show_water'],

			'/video/([0-9]+)/([0-9]+)' => [$this, 'show_video'],
			'/photo/([0-9]+)/([0-9]+)/[a-z]*' => [$this, 'show_photo'],
			'/photo/([0-9]+)/([0-9]+)' => [$this, 'show_photo'],
			'/photos/?' => [$this, 'show_photos'],
			'/plant/([0-9]+)' => [$this, 'show_plant'],
			'/plants' => [$this, 'show_plants'],
			'/place/([0-9]+)' => [$this, 'show_place'],
			'/places' => [$this, 'show_places'],
			'/sensor/([0-9]+)' => [$this, 'show_sensor'],
			'/sensors' => [$this, 'show_sensors'],
			'/device/([0-9]+)' => [$this, 'show_device'],
			'/devices' => [$this, 'show_devices'],
			'/calendar' => [$this, 'show_calendar'],
			'/dashboard' => [$this, 'show_dashboard'],

			'/' => [$this, 'show_dashboard'],
		];

		if (isset($_SERVER['HTTP_ACCEPT']) and $_SERVER['HTTP_ACCEPT'] == 'application/json') {
			$this->json = true;
		}

		if (isset($_SERVER['HTTP_X_MODAL']) and $_SERVER['HTTP_X_MODAL'] == 'modal') {
			$this->bare = true;
		}
	}

	public function handle($uri, $get, $post) {
		$uri = str_replace($GLOBALS['config']['base_path'], '', $uri);

		$uri = strtok($uri, '?');

		if (strpos($uri, '/admin') === 0) {
			$this->auth_admin($uri);
		} else if (strpos($uri, '/data') === 0) {
			$this->auth_api();
		}

		foreach ($this->routes as $pattern => $function) {
			if (preg_match('/^' . str_replace('/', '\/', $pattern) . '$/', $uri)) {
				return $function(explode('/', $uri), $get, $post);
			}
		}

		header("HTTP/1.0 404 Not Found");
	}

	function auth_admin($uri) {
		$htpasswd = __DIR__.'/../cfg/htpasswd';

		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="'.utf8_decode($GLOBALS['config']['title']).'"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'You need to be authentified to view this section';
			exit;
		} else if ($_SERVER['PHP_AUTH_USER'] == "logout") {
			if ($uri == "/admin/logout") {
				header('HTTP/1.0 401 Unauthorized');
				// At this point, we manually redirect the user to the home
				// taking care to strip off the possible "logout@" authentication
				// that some browsers silently carry on.
				echo <<<HTML
					<script>
						window.location = window.location.protocol + "//" + window.location.host + "/";
					</script>
					Logging out..
HTML;
			} else {
				header('HTTP/1.0 401 Unauthorized');
				header('WWW-Authenticate: Basic realm="'.utf8_decode($GLOBALS['config']['title']).'"');
			}
			die();
		} else {
			$submitted_user = $_SERVER['PHP_AUTH_USER'];
			$submitted_pass = $_SERVER['PHP_AUTH_PW'];

			$ok = false;

			foreach (file($htpasswd) as $line) {
				list($user, $pass) = explode(":", trim($line), 2);

				if ($user == $submitted_user) {
					if (Crypto::check_htpasswd_pass($user, $submitted_pass, $pass)) {
						$ok = true;
					}
				}
			}

			if (!$ok) {
				header('HTTP/1.0 401 Unauthorized');
				header('WWW-Authenticate: Basic realm="Veranda"');
				echo "You are not authorized to access this page\n";
				die();
			}
		}
	}

	public function handle_admin_logout($parts, $get, $post) {
		$this->theme->content = '';

		header("Location: {$GLOBALS['config']['base_path']}/");

		return true;
	}

	public function show_dashboard($parts, $get, $post) {
		$this->theme->content_file = 'dashboard.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_calendar($parts, $get, $post) {
		$this->theme->content_file = 'calendar.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_home($parts, $get, $post) {
		$this->theme->content_file = 'home.php';
		$this->theme->head .= '<link rel="stylesheet" href="/css/home.css" />';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_device($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_device.php';

		$device_id = (int)$parts[3];

		$device = new Device();
		$device->load(['id' => $device_id]);

		$this->theme->content_env = ['device' => $device];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_devices($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_devices.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_sensor($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_sensor.php';

		$sensor_id = (int)$parts[3];

		$sensor = new Sensor();
		$sensor->load(['id' => $sensor_id]);

		$this->theme->content_env = ['sensor' => $sensor];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_sensors($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_sensors.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_plant_locate($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_plant_locate.php';

		$plant_id = (int)$parts[3];

		$plant = new Plant();
		$plant->load(['id' => $plant_id]);

		$this->theme->content_env = ['plant' => $plant];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else if ($this->bare) {
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_plant($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_plant.php';

		$plant_id = (int)$parts[3];

		$plant = new Plant();
		$plant->load(['id' => $plant_id]);

		$this->theme->content_env = ['plant' => $plant];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_plants($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_plants.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_place($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_place.php';

		$place_id = (int)$parts[3];

		$place = new Place();
		$place->load(['id' => $place_id]);

		$this->theme->content_env = ['place' => $place];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_place_photos_day($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_place_photos_day.php';

		$place_id = (int)$parts[3];

		$place = new Place();
		$place->load(['id' => $place_id]);

		$this->theme->content_env = ['place' => $place, 'day' => (int)$parts[5]];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_place_photos($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_place_photos.php';

		$place_id = (int)$parts[3];

		$place = new Place();
		$place->load(['id' => $place_id]);

		$this->theme->content_env = ['place' => $place];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_places($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_places.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_photo($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_photo.php';

		$photo_id = (int)$parts[3];

		$photo = new Photo();
		$photo->load(['id' => $photo_id]);

		$this->theme->content_env = ['photo' => $photo];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_photos($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_photos.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_videos($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_videos.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_alert($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_alert.php';

		$alert_id = (int)$parts[3];

		$alert = new Alert();
		$alert->load(['id' => $alert_id]);

		$this->theme->content_env = ['alert' => $alert];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_alerts($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_alerts.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_dashboard($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_dashboard.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_chart($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_chart.php';

		$chart_id = (int)$parts[3];

		$chart = new Chart();
		$chart->load(['id' => $chart_id]);

		$this->theme->content_env = ['chart' => $chart];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_admin_dashboard_photo($parts, $get, $post) {
		$this->theme->admin = true;
		$this->theme->content_file = 'admin_dashboard_photo.php';

		$dashboard_photo_id = (int)$parts[3];

		$dashboard_photo = new Dashboard_Photo();
		$dashboard_photo->load(['id' => $dashboard_photo_id]);

		$this->theme->content_env = ['dashboard_photo' => $dashboard_photo];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_photo($parts, $get, $post) {
		$place_id = (int)$parts[2];
		$photo_id = (int)$parts[3];

		$photo = new Photo();
		$photo->load(['id' => $photo_id]);

		if ($photo->place_id == $place_id) {
			header("Content-Type: image/jpeg");
			if (isset($parts[4]) and $parts[4] == "original") {
				header("Content-Length: ".filesize($photo->path_original));
				readfile($photo->path_original);
			} else if (isset($parts[4])) {
				header("Content-Length: ".filesize($photo->path($parts[4])));
				readfile($photo->path($parts[4]));
			} else {
				header("Content-Length: ".filesize($photo->best_quality()));
				readfile($photo->best_quality());
			}
		} else {
			http_response_code(404);
		}
		
		return true;
	}

	public function show_video($parts, $get, $post) {
		$place_id = (int)$parts[2];
		$video_id = (int)$parts[3];

		$video = new Video();
		$video->load(['id' => $video_id]);

		if ($video->place_id == $place_id) {
			header("Content-Type: video/webm");
			header("Content-Length: ".filesize($video->path));
			readfile($video->path);
		} else {
			http_response_code(404);
		}
		
		return true;
	}

	public function show_plants($parts, $get, $post) {
		$this->theme->content_file = 'plants.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_places($parts, $get, $post) {
		$this->theme->content_file = 'places.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_sensors($parts, $get, $post) {
		$this->theme->content_file = 'sensors.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_devices($parts, $get, $post) {
		$this->theme->content_file = 'devices.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_photos($parts, $get, $post) {
		$this->theme->content_file = 'photos.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_plant($parts, $get, $post) {
		$this->theme->content_file = 'plant.php';

		$plant_id = (int)$parts[2];

		$plant = new Plant();
		$plant->load(['id' => $plant_id]);

		$this->theme->content_env = ['plant' => $plant];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_place($parts, $get, $post) {
		$this->theme->content_file = 'place.php';

		$place_id = (int)$parts[2];

		$place = new Place();
		$place->load(['id' => $place_id]);

		if (!$place->public) {
			http_response_code(404);
		} else {
			$this->theme->content_env = ['place' => $place];

			if ($this->json) {
				header('Content-Type: application/json;charset=UTF-8');
				print $this->theme->bare();
			} else {
				print $this->theme->html();
			}
		}

		return true;
	}

	public function show_sensor($parts, $get, $post) {
		$this->theme->content_file = 'sensor.php';

		$sensor_id = (int)$parts[2];

		$sensor = new Sensor();
		$sensor->load(['id' => $sensor_id]);

		$this->theme->content_env = ['sensor' => $sensor];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	public function show_device($parts, $get, $post) {
		$this->theme->content_file = 'device.php';

		$device_id = (int)$parts[2];

		$device = new Device();
		$device->load(['id' => $device_id]);

		$this->theme->content_env = ['device' => $device];

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}

	function auth_api() {
		$submitted_api_key = "";
		if (isset($_REQUEST['key'])) {
			$submitted_api_key = $_REQUEST['key'];
		}

		if (isset($_SERVER['HTTP_X_API_KEY'])) {
			$submitted_api_key = $_SERVER['HTTP_X_API_KEY'];
		}

		if (!empty($GLOBALS['config']['api-key'])) {
			if ($GLOBALS['config']['api-key'] != $submitted_api_key) {
				header('HTTP/1.0 403 Forbidden');
				die();
			}
		}
	}

	public function handle_device_data($parts, $get, $post) {
		$device_id = (int)$parts[3];

		$device = new Device();
		if ($device->load(['id' => $device_id])) {
			$this->theme->content_file = 'device.api.php';
			$this->theme->content_env = ['device' => $device];
		} else {
			http_response_code(404);
			$this->theme->content = '';
		}

		header('Content-Type: application/json;charset=UTF-8');
		print $this->theme->bare();

		return true;
	}

	public function handle_sensor_data($parts, $get, $post) {
		$sensor_id = (int)$parts[3];

		$sensor = new Sensor();
		if ($sensor->load(['id' => $sensor_id])) {
			$this->theme->content_file = 'sensor.api.php';
			$this->theme->content_env = ['sensor' => $sensor];
		} else {
			http_response_code(404);
			$this->theme->content = '';
		}

		header('Content-Type: application/json;charset=UTF-8');
		print $this->theme->bare();

		return true;
	}

	public function handle_place_photo($parts, $get, $post) {
		$place_id = (int)$parts[3];

		$place = new Place();
		if ($place->load(['id' => $place_id])) {
			$this->theme->content_file = 'place_photo.api.php';
			$this->theme->content_env = ['place' => $place];
		} else {
			http_response_code(404);
			$this->theme->content = '';
		}

		header('Content-Type: application/json;charset=UTF-8');
		print $this->theme->bare();

		return true;
	}

	public function show_water($parts, $get, $post) {
		$this->theme->content_file = 'water.php';

		if ($this->json) {
			header('Content-Type: application/json;charset=UTF-8');
			print $this->theme->bare();
		} else {
			print $this->theme->html();
		}

		return true;
	}
}
