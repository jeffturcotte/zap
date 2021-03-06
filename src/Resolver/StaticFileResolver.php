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

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Zap\ResolverInterface;

class StaticFileResolver implements ResolverInterface
{
	protected $path;


	/**
	 * Constructor
	 *
	 * @param $path string
	 *     The absolute root path of where to serve files from
	 *
	 * @return void
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}


	/**
	 * Returns a response for a static/binary file
	 *
	 * @param $req Request
	 *     The current request object
	 *
	 * @return BinaryFileResponse
	 *     The response object
	 */
	public function resolve(Request $req)
	{
		$file = $this->path . $req->getRequestUri();

		if (!file_exists($file)) return;

		return new BinaryFileResponse($file);
	}
}
