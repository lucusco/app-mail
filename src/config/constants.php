<?php

/* 
 * EMAIL
 */
define('CONF_EMAIL_PORT', 587);
define('CONF_EMAIL_ENCRYPTION', 'tls');
define('CONF_EMAIL_FROM_NAME', 'Developer Team');
define('CONF_EMAIL_CHARSET', 'UTF-8');

/* 
 * DIRECTORIES
 */
define('BASE_DIR', dirname(dirname(__DIR__)) . '/');
define('SRC_DIR', BASE_DIR . 'src/');
define('LOG_DIR', BASE_DIR . 'logs/');
define('ENVS_DIR', SRC_DIR . 'config/envs/');

/* 
 * LOGS 
 */
define('DEBUG_LEVEL',     'debug');
define('INFO_LEVEL',      'info');
define('NOTICE_LEVEL',    'notice');
define('WARNING_LEVEL',   'warning');
define('ERROR_LEVEL',     'error');
define('CRITICAL_LEVEL',  'critical');
define('ALERT_LEVEL',     'alert');
define('EMERGENCY_LEVEL', 'emergency');
