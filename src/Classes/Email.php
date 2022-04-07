<?php

namespace App\Mail\Classes;

use PDO;
use PDOException;
use App\Mail\Classes\Log;
use App\Mail\Database\Connection;
use App\Mail\Classes\LogInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

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

    /**
     * Persist email 
     *
     * @return bool
     */
    public function persist(): bool
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
		$this->log->addMessage(NOTICE_LEVEL, "Added subject $subjetc and content to e-mail");
	}

	/**
	 * Send Email
	 */
	public static function send($id)
	{
        $data = self::getEmailFromDB($id);
        if (!$data) {
            return false;
        }

        try {
            $email = new Email();
            $email->to($data['send_to'], $data['from_name']);
            $email->content($data['subject'], base64_decode($data['message']));
    
    
            if ($email->mail->send()) {
                $email->log->addMessage(INFO_LEVEL, "Email sent successfully");
                $email->updateStatus($data['id']);
                return true;
            }
        } catch (Exception $e) {
            (new Log(Log::LOG_EMAIL))->addMessage(CRITICAL_LEVEL, $e->getMessage());
        }
	}

    /**
     * Connect to DB and get Email data using the id passed by ConsumeQueue function
     *
     * @param int $id
     */
    private static function getEmailFromDB(int $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        try {
            $conn = Connection::getConnection();
            $stmt = $conn->prepare("SELECT * FROM email WHERE id = :id");
            $stmt->execute(array(':id' => $id));
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            (new Log(Log::LOG_EMAIL))->addMessage(CRITICAL_LEVEL, $e->getMessage());
        }
    }

    /**
     * Update DB status column
     *
     * @param int $id
     */
    private function updateStatus(int $id): void
    {
        try {
            $conn = Connection::getConnection();
            $conn->exec("UPDATE email SET status = 200 WHERE id = '$id'");
        } catch (PDOException $e) {
            (new Log(Log::LOG_EMAIL))->addMessage(CRITICAL_LEVEL, $e->getMessage());
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
