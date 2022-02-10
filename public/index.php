<?php

require dirname(__DIR__) . '/vendor/autoload.php';

# Load envs
require SRC_DIR . 'config/loadEnvs.php';

use App\Mail\Http\App;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

// Load routes
$fileLocator = new FileLocator(array(SRC_DIR . '/Http/'));
$loader = new YamlFileLoader($fileLocator);
$routes = $loader->load('routes.yaml');

// RequestContext (required for UrlMatcher to work)
$context = new RequestContext();
$request = Request::createFromGlobals();
//$context->fromRequest($request); (done in App class)

// Required App objects
$matcher = new UrlMatcher($routes ,$context);
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

$app = new App($matcher, $controllerResolver, $argumentResolver);

/** @var Response $response */
$response = $app->run($request);
$response->send();
