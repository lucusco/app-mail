<?php

require dirname(__DIR__) . '/vendor/autoload.php';

# Load envs
require SRC_DIR . 'config/loadEnvs.php';

use App\Mail\Classes\SendEmail;

# Basic Test
# TODO: Frontend
$email = new SendEmail;
$email->to('lc_bueno@hotmail.com', 'Louis');
$email->content('Dev Test', "<h3>Uhull!</h3><p><b>Testing</b></p>");
var_dump($email,
		$email->errorMessage);

