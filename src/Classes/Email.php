<?php

namespace App\Mail\Classes;

use App\Mail\Classes\Log;
use App\Mail\Classes\LogInterface;
use App\Mail\Database\Connection;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PDOException;

final class Email
{
	private PHPMailer $mail;
	private LogInterface $log;

	private bool $hasAddress;
	private bool $hasContent;

	public string $errorMessage;

	public function __construct(string $fromName = CONF_EMAIL_FROM_NAME)
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

		$this->initLog();
	}

    public function persist()
    {
        if (!$this->hasRequiredFields()) {
            $this->log->addMessage(WARNING_LEVEL, "Error 'Required Fields Missing' on function persist");
            $this->errorMessage = "Required Fields Missing!";
            return false;
        }

        $emailData = [
            'status' => 100,
            'send_to' => implode(',', array_keys($this->mail->getAllRecipientAddresses())),
            'from_name' => $this->mail->FromName,
            'subject' => $this->mail->Subject,
            'message' => base64_encode($this->mail->Body)
        ];

        try {
            $conn = Connection::getConnection();
            $stmt = $conn->prepare('INSERT INTO email (status, send_to, from_name, subject, message) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute(array_values($emailData));
            $idEmail = $conn->lastInsertId();

            $queue = new EmailQueues();
            $queue->publishMessageToSendEmail(json_encode([
                'date' => date('Y-m-d H:i:s'),
                'message' => 'New email to send',
                'id' => $idEmail
            ]));

            return true;

        } catch (PDOException $e) {
            echo $e->getMessage();
            die;
        }
    }

	/**
	 * Add a "To" address and an optional From Name
	 *
	 * @param string $address
	 * @param string $fromName
	 */
	public function to(string $address, string $fromName = null): void
	{
		if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
			return;
		}
		
		$this->mail->addAddress($address, htmlspecialchars($fromName, ENT_QUOTES));
        if ($fromName) {
            $this->mail->FromName = filter_var($fromName, FILTER_SANITIZE_STRING);
        }
		
        $this->hasAddress = true;
		$this->log->addMessage(NOTICE_LEVEL, "Added address '$address' to e-mail");
	}

	/**
	 * Set email subject and content
	 *
	 * @param string $subjetc
	 * @param string $message
	 */
	public function content(string $subjetc, string $message): void
	{
		if (empty($subjetc) || empty($message)) {
			return;
		}

		$this->mail->isHTML(false);
		$this->mail->Subject = htmlspecialchars($subjetc, ENT_QUOTES);
		$this->mail->Body = htmlspecialchars($message, ENT_QUOTES);
		$this->hasContent = true;
		$this->log->addMessage(NOTICE_LEVEL, "Added subject '$subjetc' and content to e-mail");
	}

	/**
	 * Send Email
	 */
	public function send(): bool
	{
		try {
			if (!$this->hasRequiredFields()) {
                $this->log->addMessage(WARNING_LEVEL, "Error 'Required Fields Missing' on function send");
                $this->errorMessage = "Required Fields Missing!";
                return false;
            }
            
            if ($this->mail->send()) {
                $this->log->addMessage(INFO_LEVEL, "Email sent successfully");
                return true;
            }

            return false;
		} catch (Exception $e) {
            $message = $e->getMessage();
            $this->log->addMessage(ERROR_LEVEL, $message);
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
		if (empty($pathToFile) || empty($filename)) {
			return;
		}

		$this->mail->addAttachment($pathToFile, $filename);
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

    private function initLog(): void
	{
		$this->log = new Log(LOG::LOG_EMAIL);
		$this->log->addMessage(INFO_LEVEL, "New e-mail object created now");
	}
}
