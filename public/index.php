<?php

require dirname(__DIR__) . '/vendor/autoload.php';

# Load envs
require SRC_DIR . 'config/loadEnvs.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

try {
    // Load routes
    $fileLocator = new FileLocator(array(SRC_DIR . '/Http/'));
    $loader = new YamlFileLoader($fileLocator);
    $routes = $loader->load('routes.yaml');

    // Init RequestContext object
    $context = new RequestContext();
    $request = Request::createFromGlobals();
    $context->fromRequest($request);

    // Init UrlMatcher object
    $matcher = new UrlMatcher($routes, $context);

    // Find the current route
    $parameters = $matcher->match($context->getPathInfo());

    // Call 
    list($controller, $method) = explode('::', $parameters['_controller']);
    call_user_func(array($controller, $method), $request);

} catch (ResourceNotFoundException $e) {
    echo $e->getMessage();
    http_response_code(404);
} catch (MethodNotAllowedException $e) {
    echo 'Method Not Allowed';
    http_response_code(405);
} catch (Exception $e) {
    echo $e->getMessage();
}
