<?php
/**
 * Created by JetBrains PhpStorm.
 * User: madesst
 * Date: 28.01.13
 * Time: 21:37
 * To change this template use File | Settings | File Templates.
 */
namespace Madesst\SecurityExtraBundle\Common;
use Symfony\Component\HttpFoundation\RequestMatcher as sfRequestMatcher;
use Symfony\Component\HttpFoundation\Request;

class RequestMatcher extends sfRequestMatcher
{
	/**
	 * @var string
	 */
	private $route_name;

	/**
	 * Adds a check for the route name.
	 *
	 * @param string $regexp A Regexp
	 */
	public function matchPath($regexp)
	{
		if (substr($regexp, 0, 1) == '@') {
			$this->route_name = substr($regexp, 1);
		} else {
			parent::matchPath($regexp);
		}
	}

	public function matches(Request $request)
	{
		if ($this->route_name) {
			/**
			 * If our path check is true - parent's path check must return true also (only path check, not other checks)
			 */
			if ($this->checkRouteMatch($request->get('_route'))) {
				parent::matchPath('^/');
				return parent::matches($request);
			}

			return false;
		}

		return parent::matches($request);
	}

	private function checkRouteMatch($current_route_name)
	{
		if ($this->route_name == $current_route_name) {
			return true;
		} elseif (function_exists('fnmatch')) {
			return fnmatch($this->route_name, $current_route_name);
		}

		return false;
	}
}