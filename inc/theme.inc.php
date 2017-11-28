<?php

class Theme {
	public $title = "Véranda";

	public $topbar = "";
	public $content = "";

	public $content_file = null;
	public $content_env = [];

	public $head = "";

	public $admin = false;

	function __construct() {
	}

	function title() {
		return $this->title;
	}

	function topbar() {
		$topbar = <<<HTML
			<h1><a href="/">Véranda</a></h1>
HTML;

		return $topbar;
	}

	function sidebar_admin() {
		$plants_list = implode("", array_map(function($plant) {
			return <<<HTML
				<li><a href="/admin/plant/{$plant->id}">{$plant->name}</a></li>
HTML;
		}, Plant::select([], 'name')));

		$places_list = implode("", array_map(function($place) {
			return <<<HTML
				<li><a href="/admin/place/{$place->id}">{$place->name}</a></li>
HTML;
		}, Place::select([], 'name')));

		$sensors_list = implode("", array_map(function($sensor) {
			return <<<HTML
				<li><a href="/admin/sensor/{$sensor->id}">{$sensor->name}</a></li>
HTML;
		}, Sensor::select([], 'name')));

		$devices_list = implode("", array_map(function($device) {
			return <<<HTML
				<li><a href="/admin/device/{$device->id}">{$device->name}</a></li>
HTML;
		}, Device::select([], 'name')));

		return <<<HTML
	<ul>
		<li class="submenu">
			<a href="/admin/plants">{$GLOBALS['__']('Plants')}</a>
			<input id="submenu-plants" type="checkbox" class="handle" /><label for="submenu-plants"></label><ul>{$plants_list}</ul>
		</li>
		<li class="submenu">
			<a href="/admin/places">{$GLOBALS['__']('Places')}</a>
			<input id="submenu-places" type="checkbox" class="handle" /><label for="submenu-places"></label><ul>{$places_list}</ul>
		</li>
		<li class="submenu">
			<a href="/admin/sensors">{$GLOBALS['__']('Sensors')}</a>
			<input id="submenu-sensors" type="checkbox" class="handle" /><label for="submenu-sensors"></label><ul>{$sensors_list}</ul>
		</li>
		<li class="submenu">
			<a href="/admin/devices">{$GLOBALS['__']('Devices')}</a>
			<input id="submenu-devices" type="checkbox" class="handle" /><label for="submenu-devices"></label><ul>{$devices_list}</ul>
		</li>
		<li><a href="/admin/photos">{$GLOBALS['__']('Photos')}</a></li>
		<li><a href="/admin/dashboard">{$GLOBALS['__']('Dashboard')}</a></li>
		<li class='admin-logout'><a href="https://logout@veranda.seos.fr/admin/logout">{$GLOBALS['__']('Logout')}</a></li>
	</ul>
HTML;
	}

	function sidebar_public() {
		$plants_list = implode("", array_map(function($plant) {
			return <<<HTML
				<li><a href="/plant/{$plant->id}">{$plant->name}</a></li>
HTML;
		}, Plant::select([], 'name')));

		$places_list = implode("", array_map(function($place) {
			return <<<HTML
				<li><a href="/place/{$place->id}">{$place->name}</a></li>
HTML;
		}, Place::select(['public' => 1], 'name')));

		$sensors_list = implode("", array_map(function($sensor) {
			return <<<HTML
				<li><a href="/sensor/{$sensor->id}">{$sensor->name}</a></li>
HTML;
		}, Sensor::select()));

		$devices_list = implode("", array_map(function($device) {
			return <<<HTML
				<li><a href="/device/{$device->id}">{$device->name}</a></li>
HTML;
		}, Device::select([], 'name')));

		return <<<HTML
	<ul>
		<li><a href="/">{$GLOBALS['__']('Home')}</a></li>
		<li class="submenu">
			<a href="/plants">{$GLOBALS['__']('Plants')}</a>
			<input id="submenu-plants" type="checkbox" class="handle" /><label for="submenu-plants"></label><ul>{$plants_list}</ul>
		</li>
		<li class="submenu">
			<a href="/places">{$GLOBALS['__']('Places')}</a>
			<input id="submenu-places" type="checkbox" class="handle" /><label for="submenu-places"></label><ul>{$places_list}</ul>
		</li>
		<li class="submenu">
			<a href="/sensors">{$GLOBALS['__']('Sensors')}</a>
			<input id="submenu-sensors" type="checkbox" class="handle" /><label for="submenu-sensors"></label><ul>{$sensors_list}</ul>
		</li>
		<li class="submenu">
			<a href="/devices">{$GLOBALS['__']('Devices')}</a>
			<input id="submenu-devices" type="checkbox" class="handle" /><label for="submenu-devices"></label><ul>{$devices_list}</ul>
		</li>
		<li><a href="/photos">{$GLOBALS['__']('Photos')}</a></li>
		<li class='admin-login'><a href="/admin">{$GLOBALS['__']('Log in')}</a></li>
	</ul>
HTML;
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
			<span class="location">Lille / 50.585, 3.4525</span>
HTML;

		return $footer;
	}

	function html() {
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<title>{$this->title()}</title>
		<link rel="stylesheet" href="/css/style.css" />
		<link rel="shortcut icon" type="image/jpeg" href="/ginkgo.png?" />
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

	function bare() {
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

