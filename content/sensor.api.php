<?php // vim: ft=php:et:sw=2:sts=2:ts=2

$response = [];

$value = isset($_REQUEST['value']) ? (float)$_REQUEST['value'] : null;
$timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : time();
$battery = isset($_REQUEST['battery']) ? (float)$_REQUEST['battery'] : null;

if ($value) {
  $response = $sensor->record_data($value, $timestamp, $battery);

  if ($response > 0) {
    http_response_code(201);
  } else if ($response === 0) {
    http_response_code(202);
  } else {
    http_response_code(500);
  }
}

$data = $sensor->data_at($timestamp);
$response = [
  'sensor_id' => $sensor->id,
  'sensor_name' => $sensor->name,
  'value' => (float)$data['value'],
  'timestamp' => (int)$data['timestamp'],
  'battery' => (float)$data['battery'],
  'raw' => (float)$data['raw'],
];

$old_serialize_precision = ini_get('serialize_precision');
ini_set('serialize_precision', 8);
echo json_encode($response, JSON_PRETTY_PRINT);
ini_set('serialize_precision', $old_serialize_precision);

