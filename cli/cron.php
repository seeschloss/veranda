#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

$cron = new Cron(time());
$cron->execute();
