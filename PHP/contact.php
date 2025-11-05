<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye los archivos necesarios de PHPMailer
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$tel = $_POST['tel'];
$coment = $_POST['coment'];

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP para Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contactoservicioweb.webcraft@gmail.com'; // Tu dirección de correo Gmail
    $mail->Password = 'ubar bbgg culg dyhi'; // Tu contraseña de Gmail o la contraseña de aplicaciones
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Configuración del correo electrónico
    $mail->setFrom('contactoservicioweb.webcraft@gmail.com', 'Paulina Fuentes');
    $mail->addAddress('bookartencuadernaciones@gmail.com'); // Dirección de destino

    // Configura el correo para enviar HTML
    $mail->isHTML(true);
    $mail->Subject = 'Contacto de servicio web';
    $mail->Body = "
    <html>
    <head>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap');
        </style>
    </head>
    <body style='font-family: Roboto, sans-serif; font-size: 15px; width: 40%; heigt:auto; margin: 0 auto; display: flex; justify-content: center; align-items: center; background-color: white; color:black;'>
        <div style='text-align: center; background-color: white; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);'>
            <div class='banner'>
                <img src='https://bookartencuadernaciones.com/img/Banner.png' alt='Banner' style='width: 100%;'>
            </div>
            <p style='font-weight:bold;'>¡Hola Daniel! Alguien te ha enviado este correo de parte <br> de la web BookArt Encuadernaciones.</p>
            <p>Nombre: $nombre</p>
            <p>Email: $email</p>
            <p>Teléfono: $tel</p>
            <p>Comentario: $coment</p>
            <div class='footer'>
                <img src='https://bookartencuadernaciones.com/img/Footer.png' alt='Footer' style='width: 100%;'>
            </div>
        </div>
    </body>
    </html>
    ";

    // Enviar el correo
    $mail->send();
    $mensaje=urlencode("El mensaje se ha enviado, te responderán en breves");
    // Redirigir con mensaje de éxito
    header("Location: ../Contacto.php?mensaje=$mensaje&modal=true");
    exit();
} catch (Exception $e) {
    echo "El mensaje no se pudo enviar. Error de Mailer: {$mail->ErrorInfo}";
    exit();
}
