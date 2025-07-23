<?php
header('Content-Type: application/json');

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function limpiar($valor) {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

// Validar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar reCAPTCHA
$token = $_POST['g-recaptcha-response'] ?? '';
$recaptchaSecret = $_ENV['RECAPTCHA_SECRET'];
$verificacion = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$token");
$respuesta = json_decode($verificacion, true);

if (!$respuesta['success'] || $respuesta['score'] < 0.5) {
    echo json_encode(['success' => false, 'message' => 'No se pudo verificar que eres humano.']);
    exit;
}

// Validar datos del formulario
$nombre = limpiar($_POST['nombre'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$mensaje = limpiar($_POST['mensaje'] ?? '');

if (!$nombre || !$email || !$mensaje) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Enviar con PHPMailer
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->Port = $_ENV['SMTP_PORT'];
    if ($mail->Port == 465){
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME'] . ' | ' . $_ENV['WEB_NAME']);
    $mail->addAddress($_ENV['SMTP_FROM']);       // Destinatario, el mismo que envía, en este caso
    $mail->addReplyTo($email, $nombre);          // Para responderle
    $mail->addCC($email);                        // Copia al remitente

    $mail->Subject = "Nuevo mensaje desde el formulario";
    $mail->isHTML(true);
    $mail->Body = "<h1>Nueva consulta web</h1>
        <strong>Nombre:</strong> " . nl2br($nombre) . "<br>
        <strong>Email:</strong> " . nl2br($email) . "<br>
        <strong>Mensaje:</strong><br>" . nl2br($mensaje);

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Mensaje enviado correctamente. Se ha enviado una copia a tu correo.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje: ' . $mail->ErrorInfo]);
}
