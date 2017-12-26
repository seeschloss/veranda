<?php // vim: ft=php:et:sw=2:sts=2:ts=2

$response = [];

if (!isset($_FILES) or empty($_FILES)) {
  http_response_code(400);
} else {
  $timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : time();
  $period = isset($_REQUEST['period']) ? $_REQUEST['period'] : $place->period($timestamp);

  foreach ($_FILES as $file) {
    $photo = new Photo();
    $photo->place_id = $place->id;
    $photo->timestamp = $timestamp;
    $photo->period = $period;
    $photo->save(file_get_contents($file['tmp_name']));

    $response[] = [
      'photo_id' => $photo->id,
      'place_id' => $place->id,
      'place_name' => $place->name,
      'period' => $photo->period,
      'timestamp' => $photo->timestamp,
    ];
  }
}

$old_serialize_precision = ini_get('serialize_precision');
ini_set('serialize_precision', 8);
echo json_encode($response, JSON_PRETTY_PRINT);
ini_set('serialize_precision', $old_serialize_precision);

