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
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\Urlgenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Zap\App as App;

class RouteResolver {
	protected $shared;

	public function __construct(RouteCollection $collection)
	{
		$this->shared = $collection;
		$this->collection = new RouteCollection();
	}

	public function add($url, $name, $controller)
	{
		$options = (array) $options;

		$route = new Route($url, [
			'_controller' => $controller
		]);

		$this->shared->add($name, $route);
		$this->collection->add($name, $route);

		return $route;
	}

	public function route(Request $request)
	{
		$context = new RequestContext();
		$context->fromRequest($request);

		try {
			$matcher = new UrlMatcher($this->collection, $context);
			$attributes = $matcher->match($request->getPathInfo());
		} catch (ResourceNotFoundException $e) {
			return;
		}

		$route = $attributes['_route'];
		unset($attributes['_route']);

		$controller = $attributes['_controller'];
		unset($attributes['_controller']);

		foreach($attributes as $key => $value) {
			$request->query->set($key, $value);
		}

		return $controller;
	}

	public function __invoke(Request $req)
	{
		return $this->route($req);
	}
}
