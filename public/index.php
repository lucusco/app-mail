<?php

require dirname(__DIR__) . '/vendor/autoload.php';

# Load envs
require SRC_DIR . 'config/loadEnvs.php';

# Run in background to consume e-mail queue
exec('php ' . SRC_DIR . 'Helpers/ConsumeQueue.php &');

use App\Mail\Http\App;
use App\Mail\Http\HandleResponse;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

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

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new HandleResponse());

$app = new App($dispatcher, $matcher, $controllerResolver, $argumentResolver);

/** @var Response $response */
$response = $app->run($request);
if ($response !== null) {
    $response->send();
}