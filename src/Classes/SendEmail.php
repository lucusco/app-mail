<?php

namespace App\Mail\Classes;

use App\Mail\Classes\Log;
use App\Mail\Classes\LogInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class SendEmail
{
	private PHPMailer $mail;
	private LogInterface $log;

	private bool $hasAddress;
	private bool $hasContent;

	public string $errorMessage;

	public function __construct($fromName = CONF_EMAIL_FROM_NAME)
	{
		$this->mail = new PHPMailer(true);
		$this->mail->CharSet = CONF_EMAIL_CHARSET;

		$this->mail->isSMTP();
		$this->mail->SMTPAuth   = true;
		$this->mail->SMTPSecure = CONF_EMAIL_ENCRYPTION;

		$this->mail->Host       = $_ENV['CONF_EMAIL_HOST'];
		$this->mail->Username   = $_ENV['CONF_EMAIL_USER'];
		$this->mail->Password   = $_ENV['CONF_EMAIL_PASS'];
		$this->mail->Port       = CONF_EMAIL_PORT;

		$this->mail->setFrom($_ENV['CONF_EMAIL_FROM'], $fromName);

		$this->hasAddress = false;
		$this->hasContent = false;
		$this->errorMessage = '';

		$this->log = new Log(LOG::LOG_EMAIL);
		$this->log->addMessage(INFO_LEVEL, "New e-mail object created now");
	}

	/**
	 * Add a "To" address and a Name
	 *
	 * @param string $address
	 * @param string $name
	 */
	public function to(string $address, string $name): void
	{
		if (filter_var($address, FILTER_VALIDATE_EMAIL) && !empty($name)) {
			$this->mail->addAddress($address, $name);
			$this->hasAddress = true;
			$this->log->addMessage(NOTICE_LEVEL, "Added address $address and name $name to e-mail");
		}
	}

	/**
	 * Set email subject and content
	 *
	 * @param string $subjetc
	 * @param string $message
	 */
	public function content(string $subjetc, string $message): void
	{
		if (!empty($subjetc) && !empty($message)) {
			$this->mail->isHTML(true);
			$this->mail->Subject = $subjetc;
			$this->mail->Body = $message;
			$this->hasContent = true;
			$this->log->addMessage(NOTICE_LEVEL, "Added subject $subjetc and content to e-mail");
		}
	}

	/**
	 * Send Email
	 */
	public function send(): bool
	{
		try {
			if ($this->hasRequiredFields()) {
				return $this->mail->send();
			}
			$this->errorMessage = "Required Fields Missing!";
			return false;

		} catch (Exception $e) {
			$this->errorMessage = $e->getMessage();
			return false;
		}
	}

	/**
	 * Add an attachment
	 *
	 * @param string $pathToFile
	 * @param string $filename
	 */
	public function attach(string $pathToFile, string $filename): void
	{
		if (!empty($pathToFile) && !empty($filename)) {
			$this->mail->addAttachment($pathToFile, $filename);
		}
	}

	/**
	 * Validate required fields
	 *
	 * @return bool
	 */
	private function hasRequiredFields(): bool
	{
		return ($this->hasAddress && $this->hasContent);
	}
}
