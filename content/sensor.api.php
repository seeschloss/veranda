<?php // vim: ft=php:et:sw=2:sts=2:ts=2

$response = [];

if (!isset($_REQUEST['value'])) {
  http_response_code(400);
} else {
  $timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : time();
  $battery = isset($_REQUEST['battery']) ? (float)$_REQUEST['battery'] : null;
  if ($sensor->record_data($_REQUEST['value'], $timestamp, $battery)) {
    http_response_code(202);
    $response = [
      'sensor_id' => $sensor->id,
      'sensor_name' => $sensor->name,
      'value' => (float)$_REQUEST['value'],
      'timestamp' => (int)$timestamp,
      'battery' => (float)$battery,
    ];
  } else {
    http_response_code(500);
  }
}

$old_serialize_precision = ini_get('serialize_precision');
ini_set('serialize_precision', 8);
echo json_encode($response, JSON_PRETTY_PRINT);
ini_set('serialize_precision', $old_serialize_precision);

