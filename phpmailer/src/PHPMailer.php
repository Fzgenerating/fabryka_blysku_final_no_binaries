<?php
/**
 * Minimal PHPMailer implementation with SMTP support.
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    public const ENCRYPTION_STARTTLS = 'tls';
    public const ENCRYPTION_SMTPS = 'ssl';

    public $Mailer = 'mail';
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $CharSet = 'UTF-8';
    public $Subject = '';
    public $Body = '';
    public $Timeout = 30;

    protected $From = '';
    protected $FromName = '';
    protected $isHTML = false;

    /** @var array<int, array{address: string, name: string}> */
    protected $to = [];

    /** @var array<int, array{address: string, name: string}> */
    protected $replyTo = [];

    public function __construct($exceptions = false)
    {
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = [
            'address' => $address,
            'name' => $name,
        ];
    }

    public function addReplyTo($address, $name = '')
    {
        $this->replyTo[] = [
            'address' => $address,
            'name' => $name,
        ];
    }

    public function isHTML($isHtml = true)
    {
        $this->isHTML = (bool)$isHtml;
    }

    public function send()
    {
        if ($this->Mailer === 'smtp') {
            return $this->smtpSend();
        }

        return $this->mailSend();
    }

    protected function mailSend()
    {
        $to = array_map(function ($recipient) {
            return $this->formatAddress($recipient);
        }, $this->to);

        $headers = $this->buildHeaders();
        $message = $this->Body;

        return @mail(implode(', ', $to), $this->Subject, $message, $headers);
    }

    protected function smtpSend()
    {
        if (empty($this->Host)) {
            throw new Exception('Nie zdefiniowano hosta SMTP.');
        }

        $smtpHost = $this->Host;
        $smtpPort = $this->Port ?: 25;

        if ($this->SMTPSecure === self::ENCRYPTION_SMTPS && strpos($smtpHost, 'ssl://') !== 0) {
            $smtpHost = 'ssl://' . $smtpHost;
        }

        $smtp = new SMTP();
        $smtp->Timeout = $this->Timeout;

        $smtp->connect($smtpHost, $smtpPort, $this->Timeout);
        $smtp->sendCommand('EHLO ' . gethostname(), [250]);

        if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS) {
            $smtp->startTLS();
            $smtp->sendCommand('EHLO ' . gethostname(), [250]);
        }

        if ($this->SMTPAuth) {
            $smtp->authenticate($this->Username, $this->Password);
        }

        $message = $this->createMessage();
        $smtp->mail($this->From, $this->to, $message);
        $smtp->quit();

        return true;
    }

    protected function formatAddress(array $address)
    {
        if (empty($address['name'])) {
            return $address['address'];
        }

        return sprintf('"%s" <%s>', addslashes($address['name']), $address['address']);
    }

    protected function buildHeaders()
    {
        $headers = [];
        $headers[] = 'From: ' . $this->formatAddress(['address' => $this->From, 'name' => $this->FromName]);

        if (!empty($this->replyTo)) {
            $reply = $this->formatAddress($this->replyTo[0]);
            $headers[] = 'Reply-To: ' . $reply;
        }

        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . ($this->isHTML ? 'text/html' : 'text/plain') . '; charset=' . $this->CharSet;

        return implode("\r\n", $headers);
    }

    protected function createMessage()
    {
        $lines = [];
        $lines[] = 'Date: ' . date('r');
        $lines[] = 'From: ' . $this->formatAddress(['address' => $this->From, 'name' => $this->FromName]);

        if (!empty($this->replyTo)) {
            $lines[] = 'Reply-To: ' . $this->formatAddress($this->replyTo[0]);
        }

        $toHeader = array_map(function ($recipient) {
            return $this->formatAddress($recipient);
        }, $this->to);

        $lines[] = 'To: ' . implode(', ', $toHeader);
        $lines[] = 'Subject: ' . $this->Subject;
        $lines[] = 'MIME-Version: 1.0';
        $lines[] = 'Content-Type: ' . ($this->isHTML ? 'text/html' : 'text/plain') . '; charset=' . $this->CharSet;
        $lines[] = '';
        $lines[] = $this->normalizeBreaks($this->Body);

        return implode("\r\n", $lines);
    }

    protected function normalizeBreaks($text)
    {
        return preg_replace("/(\r\n|\r|\n)/", "\r\n", $text);
    }
}
