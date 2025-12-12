<?php
/**
 * Minimal SMTP client used by bundled PHPMailer class.
 */

namespace PHPMailer\PHPMailer;

class SMTP
{
    public $Timeout = 30;
    public $streamOptions = [];

    /** @var resource|null */
    private $connection;
    private $lastReply = '';

    public function connect($host, $port = 25, $timeout = 30, array $options = [])
    {
        $context = stream_context_create($options);
        $this->connection = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->connection) {
            throw new Exception('Nie udało się połączyć z serwerem SMTP: ' . $errstr);
        }

        stream_set_timeout($this->connection, $timeout);
        $this->readReply();
    }

    public function sendCommand($command, array $expectedCodes)
    {
        if ($this->connection === null) {
            throw new Exception('Brak aktywnego połączenia SMTP.');
        }

        if ($command !== '') {
            fwrite($this->connection, $command . "\r\n");
        }

        $code = $this->readReply();
        if (!in_array($code, $expectedCodes, true)) {
            throw new Exception('Nieprawidłowa odpowiedź SMTP: ' . trim($this->lastReply));
        }
    }

    public function startTLS()
    {
        $this->sendCommand('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception('Nie udało się włączyć szyfrowania TLS.');
        }
    }

    public function authenticate($username, $password)
    {
        $this->sendCommand('AUTH LOGIN', [334]);
        $this->sendCommand(base64_encode($username), [334]);
        $this->sendCommand(base64_encode($password), [235]);
    }

    public function mail($from, array $recipients, $data)
    {
        $this->sendCommand('MAIL FROM: <' . $from . '>', [250]);
        foreach ($recipients as $recipient) {
            $this->sendCommand('RCPT TO: <' . $recipient['address'] . '>', [250, 251]);
        }

        $this->sendCommand('DATA', [354]);
        fwrite($this->connection, $data . "\r\n.\r\n");
        $this->sendCommand('', [250]);
    }

    public function quit()
    {
        if ($this->connection) {
            fwrite($this->connection, "QUIT\r\n");
            fclose($this->connection);
            $this->connection = null;
        }
    }

    private function readReply()
    {
        $reply = '';
        while ($line = fgets($this->connection, 515)) {
            $reply .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $this->lastReply = $reply;
        return (int)substr($reply, 0, 3);
    }
}
