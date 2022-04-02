<?php

namespace App\Mail\Controllers;

use App\Mail\Classes\Email;
use App\Mail\Views\AppViews;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController
{
    public static function home(): Response
    {
        $twig = AppViews::getTemplate();
        return new Response($twig->render('index.html.twig', [
            'title' => 'Sender App'
        ]));
    }

     public static function handle(Request $req): Response
    {
        $route = $req->attributes->get('_route');
        $message = ($route == 'success') ? 'Email enviado para a fila de envios' : 'Erro ao salvar dados do email, contate o administrador';
        $alert = ($route == 'success') ? 'success' : 'danger';
        
        $twig = AppViews::getTemplate();
        return new Response($twig->render('index.html.twig', [
            'title' => 'Sender App',
            'message' => $message,
            'alert' => $alert
        ]));   
    }

    public static function send(Request $req): RedirectResponse
    {
        $fromName = $req->request->get('name');
        $toEmail = $req->request->get('email');
        $subject = $req->request->get('subject');
        $message = $req->request->get('message');

        $email = new Email();
        $email->to($toEmail, $fromName);
        $email->content($subject, $message);

        if ($email->persist() !== true) {
            return new RedirectResponse('https://localhost/appmail/error', 302);
        }
        
        return new RedirectResponse('https://localhost/appmail/success', 302);
    }
}
