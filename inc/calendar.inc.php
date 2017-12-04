<?php

require_once __DIR__.'/floreal.inc.php';

class FlorealCalendar {
	private $republican_year;

	public $events = [];

	function __construct($republican_year) {
		$this->republican_year = $republican_year;
	}

	function html() {
		$html = '<section>';

		$html .= '<h1>An '.$this->republican_year.' &mdash; '._to_roman($this->republican_year).'</h1>';

		for ($month = 1; $month <= 13; $month++) {
			$calendar_month = new FlorealCalendarMonth($this->republican_year, $month);
			$calendar_month->events = $this->events;
			$html .= $calendar_month->table();
		}

		$html .= '</section>';

		return $html;
	}
}

class FlorealCalendarMonth {
	private $republican_year;
	private $republican_month;

	public $events = [];

	function __construct($republican_year, $republican_month) {
		$this->republican_year = $republican_year;
		$this->republican_month = $republican_month;
	}

	function max_decade_days() {
		if ($this->republican_month > 0 and $this->republican_month < 13) {
			return 10;
		}

		$republican_date = new FlorealDate($this->republican_year, $this->republican_month, 1);
		return $republican_date->is_year_sextile() ? 6 : 5;
	}

	function table() {
		$decades = 3;
		if ($this->republican_month == 13 || $this->republican_month == 0) {
			$decades = 1;
		}

		switch ($this->republican_month) {
			case 1:
			case 2:
			case 3:
				$class = 'automne';
				break;
			case 4:
			case 5:
			case 6:
				$class = 'hiver';
				break;
			case 7:
			case 8:
			case 9:
				$class = 'printemps';
				break;
			case 10:
			case 11:
			case 12:
				$class = 'ete';
				break;
			case 13:
				$class = 'complementaire';
				break;
		}

		$html = '<table class="month '.$class.'">';
		$html .= $this->table_head();
		for ($republican_decade = 0; $republican_decade < $decades; $republican_decade++) {
			$html .= $this->table_row($republican_decade);
		}
		$html .= '</table>';

		return $html;
	}

	function table_head() {
		$last_day = $this->max_decade_days();

		if ($this->republican_month > 0 and $this->republican_month < 13) {
			$republican_date = new FlorealDate($this->republican_year, $this->republican_month, 1);
			$month_name = $this->republican_month.". ".ucfirst($republican_date->republican_month_name());
		} else {
			$month_name = "Jours complémentaires";
		}

		$html = '<thead>';
		$html .= '<tr class="month-name">';
		$html .= '<th colspan="'.$last_day.'">';
		$html .= $month_name;
		$html .= '</th>';
		$html .= '</tr>';

		$html .= '<tr class="day-names">';

		for ($republican_day = 1; $republican_day <= $last_day; $republican_day++) {
			$republican_date = new FlorealDate($this->republican_year, $this->republican_month, $republican_day);

			$html .= '<th title="'.ucfirst($republican_date->republican_day_name()).'">';
			$html .= chr(ord('A') + $republican_day - 1);
			$html .= '</th>';
		}

		$html .= '</tr>';

		$html .= '</thead>';

		return $html;
	}

	function table_row($republican_decade) {
		$last_day = $this->max_decade_days();

		$html = '<tr>';
		for ($republican_decade_day = 1; $republican_decade_day <= $last_day; $republican_decade_day++) {
			$republican_date = new FlorealDate($this->republican_year, $this->republican_month, $republican_decade * 10 + $republican_decade_day);
			list($day_name, $day_category) = $republican_date->republican_day_title();

			$timestamp = $republican_date->timestamp();

			$class = strtr($day_category, [
				'é' => 'e',
				'è' => 'e',
			]);

			if ($timestamp == strtotime("today midnight")) {
				$class .= " today";
			}

			$georgian_day = date('N', $timestamp);
			if ($georgian_day == 6 or $georgian_day == 7) {
				$class .= " weekend";
			}

			$day_events = "";

			if (isset($this->events[$timestamp])) {
				$class .= " event";
				$day_events = "<ul class='events'><li>".join("</li><li>", $this->events[$timestamp])."</li></ul>";
			}

			$html .= '<td class="'.$class.'" title="'._mb_ucfirst($day_name).'">';
			$html .= $republican_decade * 10 + $republican_decade_day;
			$html .= $day_events;
			$html .= '</td>';
		}
		$html .= '</tr>';

		return $html;
	}
}

function _mb_ucfirst($string) {
	return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1));
}

