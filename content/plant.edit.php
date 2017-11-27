<?php // vim: ft=php:et:sw=2:sts=2:ts=2

switch ($_POST['submit']) {
  case "water":
    $plant = new Plant();
    if ($plant->load(['id' => $_POST['id']])) {
      $watering = new Watering();
      $watering->plant_id = $plant->id;
      $watering->date = $time;
      $watering->insert();
    }
    break;
  case "delete":
    $plant = new Plant();
    $plant->load(['id' => $_POST['id']]);
    $plant->delete();
    break;
  case "insert":
  case "update":
    $plant = new Plant();
    $plant->load(['id' => $_POST['id']]);
    if ($_POST['id'] > 0) {
      $plant->load(['id' => $_POST['id']]);
    } else {
      $plant->name = "New plant";
    }

    if (isset($_POST['coordinates'])) {
      $coordinates = json_decode($_POST['coordinates']);
      $plant->box_x = $coordinates->x;
      $plant->box_y = $coordinates->y;
      $plant->box_width = $coordinates->width;
      $plant->box_height = $coordinates->height;
    }

    if (isset($_POST['name'])) {
        $plant->name = $_POST['name'];
    }

    if (isset($_POST['latin_name'])) {
        $plant->latin_name = $_POST['latin_name'];
    }

    if (isset($_POST['planted'])) {
        $parts = explode('-', $_POST['planted']);
        $plant->planted = (int)gmmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
    }

    if (isset($_POST['comment'])) {
        $plant->comment = $_POST['comment'];
    }

    $plant->save();
    break;
}

$plants = Plant::select();
echo json_encode(array_values($plants));

