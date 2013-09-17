<?php
/*
 * This file is part of the Zap package.
 *
 * (c) Jeff Turcotte <jeff.turcotte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zap;

use Pimple;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Psr\Log\NullLogger;

/**
 * Configures and manages an application
 *
 * @package Zap
 *
 * @author Jeff Turcotte <jeff.turcotte@gmail.com>
 **/
class App extends Kernel
{	
	/**
	 * Constructor
	 *
	 * @return Zap\App
	 */
	public function __construct()
	{
		$defaults = [
			'namespace' => 'app'
		];

		$this['Zap\App'] = $this->share(function() {
			return $this;
		});

		$this['Symfony\Component\HttpFoundation\Request'] = $this->share(function() {
			return Request::createFromGlobals();
		});

		$this['Symfony\Component\HttpFoundation\Response'] = $this->share(function() {
			return new Response();
		});

		$this['Symfony\Component\Routing\RequestContext'] = $this->share(function(Request $request) {
			$context = new RequestContext();
			$context->fromRequest(Request::createFromGlobals());
			return $context;
		});

		$this['Symfony\Component\Routing\Generator\UrlGenerator'] = $this->share(function(RequestContext $context, RouteCollection $collection) {
			return new UrlGenerator($collection, $context);
		});

		$this['Symfony\Component\Routing\RouteCollection'] = $this->share(function() {
			return new RouteCollection();
		});

		$this['Zap\Resolver\Rewrite'] = $this->share(function(RouteCollection $collection) {
			return new Resolver\Rewrite($collection);
		});

		$this['Zap\Resolver\Mount'] = $this->share(function(RouteCollection $collection) {
			return new Resolver\Mount($collection);
		});

		$this['Zap\Resolver\AppView'] = $this->share(function(Pimple $config) {
			return new Resolver\AppView($config['namespace']);
		});

		$this['Pimple'] = $this->share(function() use ($defaults) {
			return new Pimple($defaults);
		});

		$this['Psr\Log\LoggerInterface'] = $this->share(function() {
			return new NullLogger();
		});

		$this->push(function() {
			return $this['Zap\Resolver\Rewrite'];
		});

		$this->push(function() {
			return $this['Zap\Resolver\Mount'];
		});

		$this->push(function() {
			return $this['Zap\Resolver\AppView'];
		});
	}

	public function mount($uri, Callable $callable, $callback = null)
	{
		$this['Zap\Resolver\Mount']->add($uri, $callable, $callback);
	}

	public function rewrite($incoming, $outgoing, $callback = null)
	{
		$this['Zap\Resolver\Rewrite']->add($incoming, $outgoing, $callback);
	}
}
