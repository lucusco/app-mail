<?php

namespace App\Mail\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class App
{
    private $dispatcher;
    private $matcher;
    private $controllerResolver;
    private $argumentResolver;

    public function __construct(EventDispatcher $dispatcher, UrlMatcher $matcher, ControllerResolver $controllerResolver, ArgumentResolver $argumentResolver)
    {
        $this->dispatcher = $dispatcher;
        $this->matcher = $matcher;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
    }

    public function run(Request $request) 
    {
        //Update RequestContext of UrlMatcher based on Request
        $this->matcher->getContext()->fromRequest($request);

        try {
            // If matches, add all route params to the request
            $request->attributes->add($this->matcher->match($request->getPathInfo()));
    
            // Get the controller/arguments defined for the route
            $controller = $this->controllerResolver->getController($request);
            $arguments = $this->argumentResolver->getArguments($request, $controller);
    
            // Call method and return
            $response = call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException $e) {
            $response = new Response('Not Found! >>> '.$e->getMessage(), 404);
        } catch (MethodNotAllowedException $e) {
            $response = new Response('Method Not Allowed >>> '.$e->getMessage(), 405);
        } catch (Exception $e) {
            $response = new Response('An error occurred >>> '.$e->getMessage(), 500);
        }

        // dispatch a response event
        $this->dispatcher->dispatch(new ResponseEvent($response, $request), 'response');

        return $response;
    }
}
