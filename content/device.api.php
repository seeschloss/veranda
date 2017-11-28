<?php // vim: ft=php:et:sw=2:sts=2:ts=2

if (isset($_REQUEST['state'])) {
  if ($_REQUEST['state'] == "nop") {
    $device->record_state($device->state_at(time())['state'], time());
  } else {
    $device->record_state($_REQUEST['state'], time());
  }
} else {
  echo $device->action();
  echo "\n";
}


