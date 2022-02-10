<?php

namespace App\Mail\Controllers;

use App\Mail\Classes\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController
{
    public static function home(): Response
    {
        ob_start();
        include __DIR__ . '/../Views/index.html';

        return new Response(ob_get_clean());
    }

    public static function send(Request $req): Response
    {
        $fromName = $req->request->get('name');
        $toEmail = $req->request->get('email');
        $subject = $req->request->get('subject');
        $message = $req->request->get('message');

        $email = new Email();
        $email->to($toEmail, $fromName);
        $email->content($subject, $message);

        if (!$email->send()) {
            return new Response($email->errorMessage);
        }
        
        return new Response('Email sent!');
    }
}
