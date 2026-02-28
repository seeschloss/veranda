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
		$menu_entries = [];

		if ($GLOBALS['config']['features']['plants']) {
			$menu_entries['plants'] = [
				'id' => "plants",
				'title' => $GLOBALS['__']('Plants'),
				'path' => $GLOBALS['config']['base_path'].'/admin/plants',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/admin/plant/{$entry->id}\">{$entry->name}</a></li>",
						Plant::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['places']) {
			$menu_entries['places'] = [
				'id' => "places",
				'title' => $GLOBALS['__']('Places'),
				'path' => $GLOBALS['config']['base_path'].'/admin/places',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/admin/place/{$entry->id}\">{$entry->name}</a></li>",
						Place::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['sensors']) {
			$menu_entries['sensors'] = [
				'id' => "sensors",
				'title' => $GLOBALS['__']('Sensors'),
				'path' => $GLOBALS['config']['base_path'].'/admin/sensors',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/admin/sensor/{$entry->id}\">{$entry->name}</a></li>",
						Sensor::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['devices']) {
			$menu_entries['devices'] = [
				'id' => "devices",
				'title' => $GLOBALS['__']('Devices'),
				'path' => $GLOBALS['config']['base_path'].'/admin/devices',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/admin/devices/{$entry->id}\">{$entry->name}</a></li>",
						Device::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['alerts']) {
			$menu_entries['alerts'] = [
				'id' => "alerts",
				'title' => $GLOBALS['__']('Alerts'),
				'path' => $GLOBALS['config']['base_path'].'/admin/alerts',
			];
		}

		if ($GLOBALS['config']['features']['photos']) {
			$menu_entries['photos'] = [
				'id' => "photos",
				'title' => $GLOBALS['__']('Photos'),
				'path' => $GLOBALS['config']['base_path'].'/admin/photos',
			];
		}

		if ($GLOBALS['config']['features']['videos']) {
			$menu_entries['videos'] = [
				'id' => "videos",
				'title' => $GLOBALS['__']('Videos'),
				'path' => $GLOBALS['config']['base_path'].'/admin/videos',
			];
		}

		$menu_entries['dashboard'] = [
			'id' => "dashboard",
			'title' => $GLOBALS['__']('Dashboard'),
			'path' => $GLOBALS['config']['base_path'].'/admin/dashboard',
		];

		$entries_html = join("", array_map(function($entry) {
			$submenu = "";

			if (isset($entry['submenu'])) {
				$submenu = <<<HTML
					<input id="submenu-{$entry['id']}" type="checkbox" class="handle" /><label for="submenu-{$entry['id']}"></label><ul>{$entry['submenu']}</ul>
HTML;
			}

			return <<<HTML
				<li class="submenu">
					<a href="{$entry['path']}">{$entry['title']}</a>
					{$submenu}
				</li>
HTML;
		}, $menu_entries));

		return <<<HTML
	<ul>
		{$entries_html}
		<li class='admin-logout'><a href="https://logout@athena.seos.fr/admin/logout">{$GLOBALS['__']('Logout')}</a></li>
	</ul>
HTML;
	}

	function sidebar_public() {
		$menu_entries = [];

		if ($GLOBALS['config']['features']['plants']) {
			$menu_entries['plants'] = [
				'id' => "plants",
				'title' => $GLOBALS['__']('Plants'),
				'path' => $GLOBALS['config']['base_path'].'/plants',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/plant/{$entry->id}\">{$entry->name}</a></li>",
						Plant::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['places']) {
			$menu_entries['places'] = [
				'id' => "places",
				'title' => $GLOBALS['__']('Places'),
				'path' => $GLOBALS['config']['base_path'].'/places',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/place/{$entry->id}\">{$entry->name}</a></li>",
						Place::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['sensors']) {
			$menu_entries['sensors'] = [
				'id' => "sensors",
				'title' => $GLOBALS['__']('Sensors'),
				'path' => $GLOBALS['config']['base_path'].'/sensors',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/sensor/{$entry->id}\">{$entry->name}</a></li>",
						Sensor::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['devices']) {
			$menu_entries['devices'] = [
				'id' => "devices",
				'title' => $GLOBALS['__']('Devices'),
				'path' => $GLOBALS['config']['base_path'].'/devices',
				'submenu' => implode("",
					array_map(
						fn($entry) => "<li><a href=\"{$GLOBALS['config']['base_path']}/devices/{$entry->id}\">{$entry->name}</a></li>",
						Device::select([], 'name')
					)
				),
			];
		}

		if ($GLOBALS['config']['features']['photos']) {
			$menu_entries['photos'] = [
				'id' => "photos",
				'title' => $GLOBALS['__']('Photos'),
				'path' => $GLOBALS['config']['base_path'].'/photos',
			];
		}

		$entries_html = join("", array_map(function($entry) {
			$submenu = "";

			if (isset($entry['submenu'])) {
				$submenu = <<<HTML
					<input id="submenu-{$entry['id']}" type="checkbox" class="handle" /><label for="submenu-{$entry['id']}"></label><ul>{$entry['submenu']}</ul>
HTML;
			}

			return <<<HTML
				<li class="submenu">
					<a href="{$entry['path']}">{$entry['title']}</a>
					{$submenu}
				</li>
HTML;
		}, $menu_entries));

		return <<<HTML
	<ul>
		<li><a href="{$GLOBALS['config']['base_path']}/">{$GLOBALS['__']('Home')}</a></li>
		{$entries_html}
		<li class='admin-login'><a href="{$GLOBALS['config']['base_path']}/admin">{$GLOBALS['__']('Log in')}</a></li>
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

