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

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Zap\App;
use Zap\ResolverInterface;

class ClosureResolver implements ResolverInterface
{
	protected $closure;


	public function __construct(Closure $closure)
	{
		$this->closure = $closure;
	}


	public function resolve(App $app)
	{
		return $app->invoke($this->closure);
	}
}
