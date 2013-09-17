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

class Mount {
	protected $shared;

	public function __construct(RouteCollection $collection)
	{
		$this->shared = $collection;
		$this->collection = new RouteCollection();
	}

	public function add($url, Callable $controller, $callback = null)
	{
		$options = (array) $options;

		$route = new Route($url, ['_controller' => $controller]);

		if (is_string($callback)) {
			$name = $callback;
		} else if (is_a($callback, 'Closure')) {
			$name = call_user_func_array($callback, [$route]);
		}

		// use the object hash as the name if one wasn't set
		$name = $name ?: spl_object_hash($incoming);

		$this->shared->add($name, $route);
		$this->collection->add($name, $route);

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

	public function __invoke(App $app, Request $req)
	{
		return $this->route($req);
	}
}
