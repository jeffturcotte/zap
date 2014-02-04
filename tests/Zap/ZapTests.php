<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zap\App;

class ZapTests extends PHPUnit_Framework_TestCase
{
	public function testClassExists()
	{
		$this->assertEquals(TRUE, class_exists('\Zap\App'));
	}

	public function testReturningResponse()
	{
		$app = new App();
		$app->push(new ClosureResolver(function(Request $req) {
			return new Response($req->getUri());
		}));
		$req = Request::create('/hello-world', 'GET', array('name' => 'Jim'));

		$resp = $app->run($req);

		$this->assertEquals('Symfony\Component\HttpFoundation\Response', get_class($resp));
		$this->assertEquals('http://localhost/hello-world?name=Jim', $resp->getContent());
	}

	public function testReturningNull()
	{
		$app = new App();
		$app->push(new ClosureResolver(function(App $app) {
			$example = new Example();
			$example['foo'] = 'bar';
			$app['Example'] = $example;
		}));

		$app->push(new ClosureResolver(function(Example $example) {
			return new Response($example['foo']);
		}));

		$resp = $app->run();

		$this->assertEquals('Symfony\Component\HttpFoundation\Response', get_class($resp));
		$this->assertEquals('bar', $resp->getContent());
	}

	public function testReturningResolver()
	{
		$app = new App();
		$app->push(new ClosureResolver(function() {
			return new ClosureResolver(function() {
				return new Response('hello world');
			});
		}));

		$resp = $app->run();

		$this->assertEquals('Symfony\Component\HttpFoundation\Response', get_class($resp));
		$this->assertEquals('hello world', $resp->getContent());
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testNoMatch()
	{
		$app = new App();
		$app->run();
	}
}
