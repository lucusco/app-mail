<?php

namespace App\Mail\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController
{
    public static function home()
    {
        ob_start();
        include __DIR__ . '/../Views/index.html';

        return (new Response(ob_get_clean()))->send();
    }

    public static function send(Request $req)
    {
        var_dump($req->request->all());
    }
}
