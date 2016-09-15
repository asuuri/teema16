<?php

require_once 'config.php';
require_once 'src/twitter.php';

$twitter = new Twitter($config);

$twitter->connect();
