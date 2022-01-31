<?php

namespace App\Mail\Controllers;

use Symfony\Component\HttpFoundation\Request;

class EmailController
{
    public static function handle(Request $req)
    {
        echo 'Handle';
    }

    public static function send(Request $req)
    {
        echo 'Send';
    }
}
