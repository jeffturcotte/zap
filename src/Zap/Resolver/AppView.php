<?php
/*
 * This file is part of the Zap package.
 *
 * (c) Jeff Turcotte <jeff.turcotte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zap\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Zap\App;
use Zap\ResolverInterface;

class AppView
{
	protected $namespace;


	/**
	 * The Constructor
	 *
	 * @param $namespace string
	 *     The namespace to whitelist
	 */
	public function __construct($namespace)
	{
		if (empty($namespace)) {
			throw new \InvalidArgumentException(
				'The namespace must not be empty'
			);
		}

		$this->namespace = $namespace;
	}


	/**
	 * Take the current request URI, translate it into a class name, and
	 * return an instance of the class. All segments of the URL will be
	 * upper camelized, treating dashes as word separators.
	 *
	 * Example #1: /users/edit -> \Users\Edit
	 * Example #2: /api/access-groups/delete -> \Api\AccessGroups\Delete
	 *
	 * @param $app Zap\App
	 *     The application instance
	 *
	 * @param $req Symfont\Components\HttpFoundation\Request
	 *     The current HTTP request
	 *
	 * @return mixed
	 *     null if class doesn't exist, otherwise a new instance of the class
	 */
	public function __invoke(App $app, Request $req)
	{
		$uri = $req->getPathInfo();

		// camelize each piece of the uri
		$pieces = explode('/', $uri);
		$pieces = array_slice($pieces, 1);
		$pieces = array_map(array($this, 'camelize'), $pieces);
				
		// generate class name, ensure that the configured
		// namespace has been prefixed for security
		$class  = '\\' . $this->namespace . '\\' . join('\\', $pieces);

		// check for class with autoloader
		if (!$this->validateClass($class, $req)) {
			return;
		}

		// if the class has been set up as a
		// dependency, return that instead.
		if (isset($app[$class])) {
			return $app[$class];
		}

		return new $class();
	}


	/**
	 * Validate that the class should be instantiated
	 *
	 * @param $class string
	 *     The class to validate
	 *
	 * @param $req Symfony\Component\HttpFoundation\Request
	 *     The current request
	 *
	 * @return boolean
	 *     Whether or not the class is valid
	 */
	protected function validateClass($class, Request $req)
	{
		if (class_exists($class)) {
			return true;
		}
		return false;
	}


	/**
	 * UpperCamelize a string
	 *
	 * @param $string string
	 *    The string to camelize
	 *
	 * @return string
	 *    The upper-camelized string
	 */
	protected function camelize($string) {
		$string = ucfirst($string);
		return preg_replace_callback(
			'/-([a-z0-9])/i',
			function($c) {
				return strtoupper($c[1]); 
			}, $string
		);
	}
}
