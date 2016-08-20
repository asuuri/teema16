<?php

require_once 'config.php';
require_once 'src/twitter.php';

$twitter = new Twitter($config);

$handle = $twitter->connect();

var_dump($handle);

