<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__, 2) . '/apiService/core/env.php';
require_once dirname(__DIR__, 2) . '/libs/PHPMailer-master/src/Exception.php';
require_once dirname(__DIR__, 2) . '/libs/PHPMailer-master/src/PHPMailer.php';
require_once dirname(__DIR__, 2) . '/libs/PHPMailer-master/src/SMTP.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Leer y validar campos
$nombre  = trim($_POST['nombre']  ?? '');
$email   = trim($_POST['email']   ?? '');
$telefono = trim($_POST['tel']    ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if (empty($nombre) || empty($email) || empty($mensaje)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Nombre, email y mensaje son obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El email no es válido.']);
    exit;
}

// Escapar datos para el HTML del correo
$nombreSafe  = htmlspecialchars($nombre,   ENT_QUOTES, 'UTF-8');
$emailSafe   = htmlspecialchars($email,    ENT_QUOTES, 'UTF-8');
$telefonoSafe = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
$mensajeSafe = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

$body = "
<html>
<head><meta charset='UTF-8'></head>
<body style='font-family:sans-serif; font-size:15px; color:#333;'>
    <h2 style='color:#5a3e28;'>Nuevo mensaje de contacto — BookArt</h2>
    <p><strong>Nombre:</strong> {$nombreSafe}</p>
    <p><strong>Email:</strong> {$emailSafe}</p>
    <p><strong>Teléfono:</strong> {$telefonoSafe}</p>
    <p><strong>Mensaje:</strong><br>{$mensajeSafe}</p>
</body>
</html>
";

// Enviar correo
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = (int) $_ENV['MAIL_PORT'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($_ENV['MAIL_TO']);
    $mail->addReplyTo($email, $nombre);

    $mail->isHTML(true);
    $mail->Subject = 'Nuevo mensaje de contacto — BookArt';
    $mail->Body    = $body;
    $mail->AltBody = "Nombre: {$nombre}\nEmail: {$email}\nTeléfono: {$telefono}\nMensaje: {$mensaje}";

    $mail->send();

    echo json_encode(['success' => true, 'message' => '¡Mensaje enviado! Te contactaremos pronto.']);
} catch (Exception $e) {
    error_log('BookArt contact error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No pudimos enviar tu mensaje. Intenta de nuevo más tarde.']);
}
