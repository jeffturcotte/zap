<?php
$autoloader = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloader)) {
	die("You must install the dependencies with composer before running the test");
}

include $autoloader;
