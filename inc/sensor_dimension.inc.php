<?php

class Sensor_Dimension extends Sensor {
	public $dimension;

	function unit() {
		switch ($this->dimension) {
			case 'value':
				return parent::unit();
			case 'cost':
				return '€';
		}
	}

	function label() {
		if ($this->dimension != 'value') {
			return $this->name.' ('._a('sensor-dimensions', $this->dimension).')';
		}

		return parent::label();
	}

	function axis_label() {
		if ($this->dimension != 'value') {
			return parent::label().' - '._a('sensor-dimensions', $this->dimension);
		}

		return parent::label();
	}

	function convert_dimension($data) {
		if (is_array($data)) {
			switch ($this->dimension) {
				case 'cost':
					$data['value'] = $data['value'] * $this->parameters['price'];
					break;
				case 'value':
					break;
			}
		}

		return $data;
	}

	function data_at($timestamp) {
		$data = parent::data_at($timestamp);

		return $this->dimension == 'value' ? $data : $this->convert_dimension($data);
	}

	function data_after($timestamp) {
		$data = parent::data_after($timestamp);

		return $this->dimension == 'value' ? $data : $this->convert_dimension($data);
	}

	function data_between($start, $stop, $interval = 0, $group_function = null) {
		$data = parent::data_between($start, $stop, $interval, $group_function);

		if ($this->dimension == 'value') {
			return $data;
		}

		foreach ($data as $id => &$point) {
			$data[$id] = $this->convert_dimension($point);
		}

		return $data;
	}
}
