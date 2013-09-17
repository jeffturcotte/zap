<?php
namespace Zap\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Zap\App;

class RestfulController extends Controller
{
	/**
	 * @{inheritdoc}
	 *
	 * @return whatever is returned
	 */
	public function __invoke(App $app, Request $req)
	{
		$controller = parent::__invoke($app, $req);
		return $app->call([$controller, $req->getMethod()]);
	}


	/**
	 * @{inheritdoc}
	 */
	protected function validateClass($class, Request $req)
	{
		if (class_exists($class) && method_exists($class, $req->getMethod())) {
			return true;
		}

		return false;
	}
}
