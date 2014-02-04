<?php
/*
 * Zap.
 *
 * @copyright 2014 Jeff Turcotte
 * @license see LICENSE file included with this package
 */

namespace Zap;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Jest\Injector;

/**
 * Base Application Layer
 *
 * @package Zap
 *
 * @author Jeff Turcotte <jeff.turcotte@gmail.com>
 **/
class App extends Injector implements HttpKernelInterface
{
	/**
	 * The resolvers that have been queued up
	 *
	 * @var array
	 */
	protected $resolvers = array();


	/**
	 * Constructor
	 *
	 * @return Resolver
	 */
	public function __construct()
	{
		$this[__CLASS__] = $this;
	}


	/**
	 * Invokes a single Callable resolver
	 *
	 * @param $resolver ResolverInterface
	 *     A resolver
	 *
	 * @return mixed
	 *     The first non-null value returned from a resolver chain
	 **/
	public function call(ResolverInterface $resolver)
	{
		$return = $this->invoke([$resolver, 'resolve']);

		if ($return instanceof ResolverInterface) {
			return $this->call($return);
		}

		return $return;
	}


	/**
	 * Push a Resolver onto the queue
	 *
	 * @param $resolver mixed
	 *     A Callable or an array of Callables
	 *
	 * @return void
	 **/
	public function push(ResolverInterface $resolver)
	{
		array_push($this->resolvers, $resolver);
	}


	/**
	 * Clear out all of the queued resolvers
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->resolvers = array();
	}


	/**
	 * Resolves the app with all registered dependencies
	 *
	 * Runs all resolvers and returns the first non-null, non-ResolverInterface value received
	 * If a null value is returned, drop through to the next resolver.
	 * In this fashion, one can craft ResolverInterface middleware
	 *
	 * @return mixed
	 *     The first non-null value returned from a resolver
	 **/
	public function resolve()
	{
		foreach($this->resolvers as $resolver) {
			$return = $this->call($resolver);
			if ($return !== null) {
				return $return;
			}
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		$current = isset($this[get_class($request)])
			? $this[get_class($request)]
			: $request;

		$this[get_class($request)] = $request;

		$response = $this->resolve();

		if (!($response instanceof Response)) {
			throw new \LogicException('No Response returned from the Resolver stack');
		}

		$this[get_class($current)] = $current;

		return $response;
	}


	/**
	 * Runs the app
	 *
	 * @param request Symfony\Component\HttpFoundation\Request
	 */
	public function run(Request $request = null)
	{
		$request = $request ?: Request::createFromGlobals();

		return $this->handle($request);
	}
}
