<?php

namespace App\Mail\Classes;

interface LogInterface
{
	public function __construct(string $logName);

	/**
	 * Append message to log
	 *
	 * @param string $level
	 * @param string $message
	 * @param array $extra
	 */
	public function addMessage(string $level, string $message, array $extra = []): void;

	/* 
	 *  Flushing/cleaning all buffers, resetting internal state
	 */
	public function reset(): void;
}
