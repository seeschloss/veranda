<?php // vim: ft=php:et:sw=2:sts=2:ts=2

$response = [];

if (!isset($_FILES) or empty($_FILES)) {
  $debug_filename = "/tmp/plop.photo";
  if (isset($_SERVER['HTTP_X_BOARD_ID'])) {
    $debug_filename .= "-".str_replace(":", "", $_SERVER['HTTP_X_BOARD_ID']);
  }

  $body = file_get_contents('php://input');
  file_put_contents($debug_filename, $body);
  if (strlen($body) > 0) {
    if ($body[0] == "\xFF" and $body[1] == "\xD8" and $body[2] == "\xFF") {
      // JPEG
      $data = $body;
    } else {
      $data = base64_decode($body);
    }
  } else {
    $data = "";
    file_put_contents("/tmp/body_empty_info", json_encode($_SERVER));
  }
  if ($data) {
    file_put_contents($debug_filename.".data", $data);

    $timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : time();
    $period = isset($_REQUEST['period']) ? $_REQUEST['period'] : $place->period($timestamp);

    $photo = new Photo();
    $photo->place_id = $place->id;
    $photo->timestamp = $timestamp;
    $photo->period = $period;
    $photo->save($data);

    $response[] = [
      'photo_id' => $photo->id,
      'place_id' => $place->id,
      'place_name' => $place->name,
      'period' => $photo->period,
      'timestamp' => $photo->timestamp,
    ];
  } else {
    http_response_code(415);
  }
} else {
  $timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : time();
  $period = isset($_REQUEST['period']) ? $_REQUEST['period'] : $place->period($timestamp);

  foreach ($_FILES as $file) {
    $photo = new Photo();
    $photo->place_id = $place->id;
    $photo->timestamp = $timestamp;
    $photo->period = $period;
    $photo->save(file_get_contents($file['tmp_name']));
    unlink($file['tmp_name']);

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

