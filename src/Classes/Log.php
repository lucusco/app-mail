<?php

namespace App\Mail\Classes;

use ReflectionClass;
use Monolog\Logger;
use App\Mail\Classes\LogInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

final class Log implements LogInterface
{
	private Logger $log;

	public const LOG_EMAIL = 'mail.log';

	public const LOG_QUEUE = 'queue.log';

	private const PERMITTED_LEVELS = [
		'debug',		//(100): Detailed debug information
		'info',			//(200): Interesting events. Examples: User logs in, SQL logs.
		'notice',		//(250): Normal but significant events.
		'warning',		//(300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, undesirable things that are not necessarily wrong.
		'error',		//(400): Runtime errors that do not require immediate action but should typically be logged and monitored.
		'critical',		//(500): Critical conditions. Example: Application component unavailable, unexpected exception.
		'alert',		//(550): Action must be taken immediately
		'emergency'		//(600): Emergency: system is unusable.
	];

	public function __construct(string $logName)
	{
		if (!$this->logNameIsValid($logName)) {
			return;
		}

		$dateFormat = "d-m-Y H:i:s";
		$output = "%datetime% :: %level_name% :: %message% %context% %extra%\n";
		$formatter = new LineFormatter($output, $dateFormat, false, true);

		$stream = new StreamHandler(LOG_DIR . $logName, Logger::DEBUG);
		$stream->setFormatter($formatter);

		$this->log = new Logger($logName);
		$this->log->pushHandler($stream);
	}

	/**
	 * Append message to log
	 *
	 * @param string $level
	 * @param string $message
	 * @param array $extra
	 */
	public function addMessage(string $level, string $message, array $extra = []): void
	{
		if (!in_array($level, self::PERMITTED_LEVELS)) {
			return;
		}

		$message = filter_var($message, FILTER_SANITIZE_STRING);
		$extra = filter_var_array($extra, FILTER_SANITIZE_STRING);

		$this->log->$level($message, $extra);
	}

	/* 
	 *  Flushing/cleaning all buffers, resetting internal state
	 */
	public function reset(): void
	{
		$this->log->reset();
	}

	/**
	 * Return the class constants
	 *
	 * @return array
	 */
	private static function getConstants(): array
	{
		$oClass = new ReflectionClass(__CLASS__);
		return $oClass->getConstants();
	}

	/**
	 * Checks if the log name is valid
	 *
	 * @param string $name
	 * @return bool
	 */
	private function logNameIsValid(string $name): bool
	{
		return in_array($name, self::getConstants());
	}
}
