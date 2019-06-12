<?php // vim: ft=php:et:sw=2:sts=2:ts=2

$response = [];

if (!isset($_REQUEST['value'])) {
  http_response_code(400);
} else {
  $timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : time();
  $battery = isset($_REQUEST['battery']) ? (float)$_REQUEST['battery'] : null;
  $response = $sensor->record_data($_REQUEST['value'], $timestamp, $battery);

  if ($response > 0) {
    http_response_code(201);
    $data = $sensor->data_at($timestamp);
    //var_dump($data);
    $response = [
      'sensor_id' => $sensor->id,
      'sensor_name' => $sensor->name,
      'value' => (float)$data['value'],
      'timestamp' => (int)$data['timestamp'],
      'battery' => (float)$data['battery'],
      'raw' => (float)$data['raw'],
    ];
  } else if ($response === 0) {
    http_response_code(202);
  } else {
    http_response_code(500);
  }
}

$old_serialize_precision = ini_get('serialize_precision');
ini_set('serialize_precision', 8);
echo json_encode($response, JSON_PRETTY_PRINT);
ini_set('serialize_precision', $old_serialize_precision);

