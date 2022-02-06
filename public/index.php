<?php

require dirname(__DIR__) . '/vendor/autoload.php';

# Load envs
require SRC_DIR . 'config/loadEnvs.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

try {
    // Load routes
    $fileLocator = new FileLocator(array(SRC_DIR . '/Http/'));
    $loader = new YamlFileLoader($fileLocator);
    $routes = $loader->load('routes.yaml');

    // RequestContext (required for UrlMatcher to work)
    $context = new RequestContext();
    $request = Request::createFromGlobals();
    $context->fromRequest($request);

    $matcher = new UrlMatcher($routes ,$context);

    // Current route
    $routeRequested = $context->getPathInfo();

    // If route matches, add all route params to the request
    $request->attributes->add($matcher->match($routeRequested));

    $argumentResolver = new ArgumentResolver();
    $controllerResolver = new ControllerResolver();

    // Get the controller/arguments defined for the route
    $controller = $controllerResolver->getController($request);
    $arguments = $argumentResolver->getArguments($request, $controller);

    // Call 
    call_user_func_array($controller, $arguments);
} catch (ResourceNotFoundException $e) {
    return (new Response('Not Found! >>> '.$e->getMessage(), 404))->send();
} catch (MethodNotAllowedException $e) {
    return (new Response('Method Not Allowed >>> '.$e->getMessage(), 405))->send();
} catch (Exception $e) {
    return (new Response('An error occurred >>> '.$e->getMessage(), 500))->send();
}
