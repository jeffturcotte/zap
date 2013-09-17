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

/**
 * Handles Control Flow and Dependency Injection for an app.
 *
 * @package Zap
 *
 * @author Jeff Turcotte <jeff.turcotte@gmail.com>
 **/
class Kernel extends \Jest\Injector
{
	/**
	 * The resolvers that have been queued up
	 *
	 * @var array
	 */
	protected $resolvers = array();


	/**
	 * Invokes the app
	 *
	 * @return mixed
	 *     The value returned from running the app
	 */
	public function __invoke()
	{
		return $this->run();
	}


	/**
	 * Invokes a single Callable resolver
	 *
	 * @param $resolver Callable
	 *     A resolver Callable
	 *
	 * @return mixed
	 *     The first non-null value returned from a resolver chain
	 **/
	public function call(Callable $resolver)
	{
		$return = $this->invoke($resolver);

		if (is_callable($return)) {
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
	public function push($resolver)
	{
		$this->resolvers = array_merge(
			$this->resolvers,
			(array) $resolver
		);
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
	 * Runs all resolvers and returns the first 
	 * non-Callable, non-null value, or Exception received.
	 * If a null value is returned, drop through to the
	 * next resolver. In this fashion, one can craft
	 * Resolver 'Middleware'.
	 *
	 * @return mixed
	 *     The first non-null value returned from a resolver
	 **/
	public function run()
	{
		try {
			foreach($this->resolvers as $resolver) {
				$return = $this->call($resolver);
				if ($return !== NULL) {
					return $return;
				}
			}
		} catch (\Exception $e) {
			return $e;
		}
	}
}
