<?php

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

try {
	$dotenv = Dotenv::createImmutable(ENVS_DIR);
	$dotenv->load();

	$dotenv->required([
		'CONF_EMAIL_FROM',
		'CONF_EMAIL_HOST',
		'CONF_EMAIL_USER',
		'CONF_EMAIL_PASS',
        'CONF_DB_HOST',
        'CONF_DB_PORT',
        'CONF_DB_NAME',
        'CONF_DB_USER',
        'CONF_DB_PASS'
	]);

} catch (InvalidPathException $e) {
	echo '.env file not found!';
	die;
} catch (RuntimeException $e) {
	echo 'Possible .env variable missing. Please check your .env file.';
	die;
}
