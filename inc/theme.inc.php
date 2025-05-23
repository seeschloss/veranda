<?php

class Theme {
	public $topbar = "";
	public $content = "";

	public $content_file = null;
	public $content_env = [];

	public $head = "";

	public $admin = false;

	function __construct() {
	}

	function title() {
		return $GLOBALS['config']['title'];
	}

	function topbar() {
		$topbar = <<<HTML
			<h1><a href="{$GLOBALS['config']['base_path']}/">{$this->title()}</a></h1>
HTML;

		return $topbar;
	}

	function sidebar_admin() {
		$plants_list = implode("", array_map(function($plant) {
			return <<<HTML
				<li><a href="{$GLOBALS['config']['base_path']}/admin/plant/{$plant->id}">{$plant->name}</a></li>
HTML;
		}, Plant::select([], 'name')));

		$places_list = implode("", array_map(function($place) {
			return <<<HTML
				<li><a href="{$GLOBALS['config']['base_path']}/admin/place/{$place->id}">{$place->name}</a></li>
HTML;
		}, Place::select([], 'name')));

		$sensors_list = implode("", array_map(function($sensor) {
			return <<<HTML
				<li><a href="{$GLOBALS['config']['base_path']}/admin/sensor/{$sensor->id}">{$sensor->name}</a></li>
HTML;
		}, Sensor::select(['sensors.archived' => 0], 'name')));

		$devices_list = implode("", array_map(function($device) {
			return <<<HTML
				<li><a href="{$GLOBALS['config']['base_path']}/admin/device/{$device->id}">{$device->name}</a></li>
HTML;
		}, Device::select([], 'name')));

		return <<<HTML
	<ul>
		<li class="submenu">
			<a href="{$GLOBALS['config']['base_path']}/admin/plants">{$GLOBALS['__']('Plants')}</a>
			<input id="submenu-plants" type="checkbox" class="handle" /><label for="submenu-plants"></label><ul>{$plants_list}</ul>
		</li>
		<li class="submenu">
			<a href="{$GLOBALS['config']['base_path']}/admin/places">{$GLOBALS['__']('Places')}</a>
			<input id="submenu-places" type="checkbox" class="handle" /><label for="submenu-places"></label><ul>{$places_list}</ul>
		</li>
		<li class="submenu">
			<a href="{$GLOBALS['config']['base_path']}/admin/sensors">{$GLOBALS['__']('Sensors')}</a>
			<input id="submenu-sensors" type="checkbox" class="handle" /><label for="submenu-sensors"></label><ul>{$sensors_list}</ul>
		</li>
		<li class="submenu">
			<a href="{$GLOBALS['config']['base_path']}/admin/devices">{$GLOBALS['__']('Devices')}</a>
			<input id="submenu-devices" type="checkbox" class="handle" /><label for="submenu-devices"></label><ul>{$devices_list}</ul>
		</li>
		<li><a href="{$GLOBALS['config']['base_path']}/admin/alerts">{$GLOBALS['__']('Alerts')}</a></li>
		<li><a href="{$GLOBALS['config']['base_path']}/admin/photos">{$GLOBALS['__']('Photos')}</a></li>
		<li><a href="{$GLOBALS['config']['base_path']}/admin/videos">{$GLOBALS['__']('Videos')}</a></li>
		<li><a href="{$GLOBALS['config']['base_path']}/admin/dashboard">{$GLOBALS['__']('Dashboard')}</a></li>
		<li class='admin-logout'><a href="https://logout@veranda.seos.fr/admin/logout">{$GLOBALS['__']('Logout')}</a></li>
	</ul>
HTML;
	}

	function sidebar_public() {
		$html = <<<HTML
			<ul>
				<li><a href="{$GLOBALS['config']['base_path']}/">{$GLOBALS['__']('Home')}</a></li>
HTML;

		if ($plants = Plant::select([], 'name')) {
			$plants_list = implode("", array_map(function($plant) {
				return <<<HTML
					<li><a href="{$GLOBALS['config']['base_path']}/plant/{$plant->id}">{$plant->name}</a></li>
HTML;
			}, $plants));

			$html .= <<<HTML
			<li class="submenu">
				<a href="{$GLOBALS['config']['base_path']}/plants">{$GLOBALS['__']('Plants')}</a>
				<input id="submenu-plants" type="checkbox" class="handle" /><label for="submenu-plants"></label><ul>{$plants_list}</ul>
			</li>
HTML;
		}

		if ($places = Place::select(['places.public' => 1], 'name')) {
			$places_list = implode("", array_map(function($place) {
				return <<<HTML
					<li><a href="{$GLOBALS['config']['base_path']}/place/{$place->id}">{$place->name}</a></li>
HTML;
			}, $places));

			$html .= <<<HTML
			<li class="submenu">
				<a href="{$GLOBALS['config']['base_path']}/places">{$GLOBALS['__']('Places')}</a>
				<input id="submenu-places" type="checkbox" class="handle" /><label for="submenu-places"></label><ul>{$places_list}</ul>
			</li>
HTML;
		}

		if ($sensors = Sensor::select(['places.public' => 1, 'sensors.archived' => 0], 'name')) {
			$sensors_list = implode("", array_map(function($sensor) {
				return <<<HTML
					<li><a href="{$GLOBALS['config']['base_path']}/sensor/{$sensor->id}">{$sensor->name}</a></li>
HTML;
			}, $sensors));

			$html .= <<<HTML
			<li class="submenu">
				<a href="{$GLOBALS['config']['base_path']}/sensors">{$GLOBALS['__']('Sensors')}</a>
				<input id="submenu-sensors" type="checkbox" class="handle" /><label for="submenu-sensors"></label><ul>{$sensors_list}</ul>
			</li>
HTML;
		}

		if ($devices = Device::select(['places.public' => 1], 'name')) {
			$devices_list = implode("", array_map(function($device) {
				return <<<HTML
					<li><a href="{$GLOBALS['config']['base_path']}/device/{$device->id}">{$device->name}</a></li>
HTML;
			}, $devices));

			$html .= <<<HTML
			<li class="submenu">
				<a href="{$GLOBALS['config']['base_path']}/devices">{$GLOBALS['__']('Devices')}</a>
				<input id="submenu-devices" type="checkbox" class="handle" /><label for="submenu-devices"></label><ul>{$devices_list}</ul>
			</li>
HTML;
		}

		if ($photos = Photo::select_latest_by_place(['places.public' => 1], 'name')) {
			$html .= <<<HTML
			<li><a href="{$GLOBALS['config']['base_path']}/photos">{$GLOBALS['__']('Photos')}</a></li>
HTML;
		}

		$html .= <<<HTML
		<li class='admin-login'><a href="{$GLOBALS['config']['base_path']}/admin">{$GLOBALS['__']('Log in')}</a></li>
	</ul>
HTML;

		return $html;
	}

	function sidebar() {
		$sidebar = "";

		if ($this->admin) {
			$sidebar .= $this->sidebar_admin();
		} else {
			$sidebar .= $this->sidebar_public();
		}

		return $sidebar;
	}

	function footer() {
		$footer = <<<HTML
			<a href="mailto:see@seos.fr">see@seos.fr</a> &mdash;
			<a href="https://ssz.fr">ssz.fr</a> &mdash;
			<span class="location">{$GLOBALS['config']['location']['name']} / {$GLOBALS['config']['location']['latitude']}, {$GLOBALS['config']['location']['longitude']}</span>
HTML;

		return $footer;
	}

	function css() {
		$html = "";

		return $html;
	}

	function js() {
		$html = "";

		return $html;
	}

	function html() {
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<title>{$this->title()}</title>
		<link rel="stylesheet" href="{$GLOBALS['config']['base_path']}/css/style.css" />
		<link rel="shortcut icon" type="image/jpeg" href="{$GLOBALS['config']['favicon_url']}" />
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1.0">
		{$this->head}
	</head>
	<body>
		<div id="topbar">{$this->topbar()}</div>
		<div id="middle">
			<div id="sidebar">{$this->sidebar()}</div>
			<div id="content-box">
				<div id="content">{$this->content_string()}</div>
				<div id="footer">{$this->footer()}</div>
			</div>
		</div>
	</body>
</html>
HTML;

		return $html;
	}

	function json_to_bare($key) {
		$json = json_decode($this->content_string());
		return ($json->{$key} ?? "")."\n";
	}

	function bare() {
		return $this->content_string()."\n";
	}

	function json() {
		return $this->content_string();
	}


	function content_string() {
		if ($this->content_file and file_exists(__DIR__.'/../content/'.$this->content_file)) {
			ob_start();
			foreach ($this->content_env as $key => $value) {
				${$key} = $value;
			}
			require __DIR__.'/../content/'.$this->content_file;
			return ob_get_clean();
		} else {
			return $this->content;
		}
	}
}

