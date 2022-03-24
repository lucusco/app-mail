<?php

namespace App\Mail\Views;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class AppViews
{
    public static function getTemplate()
    {
        $loader = new FilesystemLoader(SRC_DIR . 'Views/Templates/');

        return new Environment($loader, [
            'cache' => SRC_DIR . 'Views/Compiled/',
            'auto_reload' => true
        ]);
    }
}
