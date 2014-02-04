<?php
namespace Zap\Resolver;

use Symfony\Component\HttpFoundation\Request as Request;
use Zap\App as App;

class IncludeResolver
{
	protected $path;
	protected $extension;

	public function __construct($path, $extension = 'php')
	{
		$this->path = $path;
		$this->extension = $extension;
	}

	public function resolve(Request $req)
	{
		$file = sprintf(
			'%s%s.%s',
			$this->path,
			$req->getRequestUri(),
			$this->extension
		);

		if (!file_exists($file)) {
			return;
		}
		
		// protect against path traversal
		if (strpos(realpath($file), $path)) !== 0) {
			return;
		}

		return include $file;
	}
}
