<?php
$autoloader = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloader)) {
	die("You must install the dependencies with composer before running the test");
}

include $autoloader;

class ClosureResolver implements \Zap\ResolverInterface {
	private $callback = null;

	public function __construct($callback)
	{
		$this->callback = $callback;
	}

	public function resolve(\Zap\App $app)
	{
		return $app->invoke($this->callback);
	}
}

class Example extends ArrayObject {}
