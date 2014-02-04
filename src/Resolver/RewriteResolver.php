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
use Zap\App;

class RewriteResolver {
	protected $shared;
	protected $incoming;
	protected $outgoing;


	/**
	 * Constructor
	 *
	 * @param $collection RouteCollection
	 *     A route collection in which to add routes to
	 *
	 * @return void
	 */
	public function __construct(RouteCollection $collection)
	{
		$this->shared   = $collection;
		$this->incoming = new RouteCollection();
		$this->outgoing = new RouteCollection();
	}

	/**
	 * Adds a rewrite to the rewriter
	 *
	 * @param $incoming string
	 *     The incoming URL
	 *
	 * @param $outgoing string
	 *     The outgoing URL
	 *
	 * @param $callback string|Closure
	 *     If a string: Will set the name for the route
	 *     If a Closure: Will take two arguments, the incoming and outgoing routes. 
	 *                   The returned value from the closure will be used as the route name
	 *
	 * @return void
	 */
	public function add($incoming, $outgoing, $name)
	{
		$incoming = new Route($incoming);
		$outgoing = new Route($outgoing);

		$this->shared->add($name, $incoming);
		$this->incoming->add($name, $incoming);
		$this->outgoing->add($name, $outgoing);

		return $incoming;
	}


	/**
	 * Rewrites a request based on the routes
	 *
	 * @param $request Symfony\Component\HttpFoundation\Request
	 *    The request to alter
	 *
	 * @return Symfony\Component\HttpFoundation\Request
	 *    The altered (or same if no route was found) request
	 */
	public function rewrite(Request $request)
	{
		// create the context from the current request
		$context = new RequestContext();
		$context->fromRequest($request);

		try {
			$matcher = new UrlMatcher($this->incoming, $context);
			$attributes = $matcher->match($request->getPathInfo());
		} catch (ResourceNotFoundException $e) {
			// no matches, return the current request
			return $request;
		}

		$generator = new UrlGenerator($this->outgoing, $context);

		// get the route and remove it from attributes
		$route = $attributes['_route'];
		unset($attributes['_route']);

		// get the vars from the outgoing url
		$vars = $this->outgoing->get($route)->compile()->getPathVariables();

		// generate the new link
		$url = $generator->generate($route, $attributes);

		// duplicate the request and set all the new attributes/params
		$request = $request->duplicate();
		$request->server->set('REQUEST_URI', $url);

		// set all the attributes on the new request
		foreach($attributes as $key => $value) {
			$request->query->set($key, $value);
		}

		return $request;
	}

	/**
	 * Configures the new rewritten request as the App dependency.
	 * Requests will always drop through this resolver.
	 *
	 * @param $app Zap\App
	 *     The application instance
	 *
	 * @param $req Symfony\Component\HttpFoundation\Request
	 *    The current HTTP request
	 *
	 * @return void
	 */
	public function resolve(App $app, Request $req)
	{
		$app['Symfony\Component\HttpFoundation\Request'] = $app->share(function() use ($req) {
			return $this->rewrite($req);
		});
	}
}
