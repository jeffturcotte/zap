<?php
namespace Zap\Resolver;

use Symfony\Component\HttpFoundation\BinaryFileResponse as BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request as Request;

class SaticFile
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function __invoke(Request $req)
	{
		$file = $this->path . $req->getRequestUri();

		if (!file_exists($file)) return;

		$response = new BinaryFileResponse($file);
		$response->prepare($req);
		return $response;
	}
}
