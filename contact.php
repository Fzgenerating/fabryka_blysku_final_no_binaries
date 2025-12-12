<?php
// contact.php - obsługa formularza kontaktowego przez PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

function respond($success, $message)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Nieprawidłowa metoda żądania.');
}

$getValue = static function ($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
};

$name = $getValue('name');
$email = $getValue('email');
$phone = $getValue('phone');
$subject = $getValue('subject');
$message = $getValue('message');

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    respond(false, 'Proszę wypełnić wszystkie wymagane pola oznaczone gwiazdką.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Podany adres e-mail jest nieprawidłowy.');
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kontakt.jakubtoronczak@gmail.com';
    $mail->Password = 'hskb xdvq tetb koiq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('kontakt.jakubtoronczak@gmail.com', 'Fabryka Błysku - formularz');
    $mail->addAddress('kontakt.jakubtoronczak@gmail.com', 'Jakub Torończak');

    if (!empty($email)) {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(false);

    $mail->Subject = 'Nowa wiadomość z formularza Fabryka Błysku: ' . $subject;

    $bodyLines = [];
    $bodyLines[] = 'Imię i nazwisko: ' . $name;
    $bodyLines[] = 'E-mail: ' . $email;
    if ($phone !== '') {
        $bodyLines[] = 'Telefon: ' . $phone;
    }
    $bodyLines[] = '';
    $bodyLines[] = 'Wiadomość:';
    $bodyLines[] = $message;

    $mail->Body = implode("\n", $bodyLines);

    $mail->send();

    respond(true, 'Dziękujemy za wiadomość. Skontaktujemy się z Tobą tak szybko jak to możliwe.');
} catch (Exception $e) {
    respond(false, 'Nie udało się wysłać wiadomości. Spróbuj ponownie później lub skontaktuj się telefonicznie.');
}
