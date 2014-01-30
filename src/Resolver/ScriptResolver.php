<?php
namespace Zap\Resolver;

use Symfony\Component\HttpFoundation\Request as Request;
use Zap\App as App;

class ScriptResolver
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function __invoke(Request $req)
	{
		$file = $this->path . $req->getRequestUri() . '.php';

		if (!file_exists($file)) {
			return;
		}

		return include $file;
	}
}
