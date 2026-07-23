<?php

class HomeController {

    public function index(){

        require_once ROOT_PATH .
        '/app/views/home/index.php';

    }

    public function acercaDe(){

        require_once ROOT_PATH .
        '/app/views/home/acerca_de.php';

    }

    public function aseguradoras(){

        require_once ROOT_PATH .
        '/app/views/home/aseguradoras.php';

    }

    public function servicios(){

        require_once ROOT_PATH .
        '/app/views/home/servicios.php';

    }

    public function contacto(){

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarFormularioContacto();
            return;
        }

        require_once ROOT_PATH .
        '/app/views/home/contacto.php';

    }

    private function procesarFormularioContacto(){

        require_once ROOT_PATH . '/app/helpers/Validator.php';

        Csrf::validate();

        $origen  = trim($_POST['origen'] ?? 'contacto');
        $volverA = ($origen === 'servicios') ? 'servicios' : 'contacto';

        $nombre          = trim($_POST['nombre'] ?? '');
        $correo          = trim($_POST['correo'] ?? '');
        $telefono        = trim($_POST['telefono'] ?? '');
        $asunto          = trim($_POST['asunto'] ?? '');
        $mensaje         = trim($_POST['mensaje'] ?? '');
        $servicioInteres = trim($_POST['servicio_interes'] ?? '');

        if ($nombre === '' || !Validator::email($correo) || empty($_POST['politica'])) {
            $_SESSION['contacto_error'] = 'Por favor completa los campos obligatorios con datos válidos.';
            header('Location: ?url=' . $volverA);
            exit;
        }

        $destino = Environment::get('SMTP_FROM', '');
        $tituloAsunto = $servicioInteres !== '' ? ('Cotización: ' . $servicioInteres) : ($asunto !== '' ? $asunto : 'Nuevo mensaje de contacto');
        $subject = 'Arco Seguros — ' . $tituloAsunto;

        $bodyHtml = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;background:#fff;padding:32px;border:1px solid #e2d9d3;">';
        $bodyHtml .= '<div style="text-align:center;margin-bottom:24px;">';
        $bodyHtml .= '<h1 style="font-size:1.5rem;color:#1C1C1E;margin:0;">Arco <span style="color:#8C7B6E;font-weight:300;">Seguros</span></h1>';
        $bodyHtml .= '<p style="color:#a1a1aa;font-size:.8rem;margin-top:4px;">Origen: ' . ($origen === 'servicios' ? 'Formulario de Servicios (cotización)' : 'Formulario de Contacto') . '</p>';
        $bodyHtml .= '</div>';
        $bodyHtml .= '<table style="width:100%;border-collapse:collapse;font-size:.92rem;">';
        $bodyHtml .= '<tr><td style="padding:6px 0;color:#78716c;">Nombre</td><td style="padding:6px 0;color:#1C1C1E;"><strong>' . htmlspecialchars($nombre) . '</strong></td></tr>';
        $bodyHtml .= '<tr><td style="padding:6px 0;color:#78716c;">Correo</td><td style="padding:6px 0;color:#1C1C1E;">' . htmlspecialchars($correo) . '</td></tr>';
        if ($telefono !== '') {
            $bodyHtml .= '<tr><td style="padding:6px 0;color:#78716c;">Teléfono</td><td style="padding:6px 0;color:#1C1C1E;">' . htmlspecialchars($telefono) . '</td></tr>';
        }
        if ($servicioInteres !== '') {
            $bodyHtml .= '<tr><td style="padding:6px 0;color:#78716c;">Seguro de interés</td><td style="padding:6px 0;color:#1C1C1E;">' . htmlspecialchars($servicioInteres) . '</td></tr>';
        } elseif ($asunto !== '') {
            $bodyHtml .= '<tr><td style="padding:6px 0;color:#78716c;">Asunto</td><td style="padding:6px 0;color:#1C1C1E;">' . htmlspecialchars($asunto) . '</td></tr>';
        }
        $bodyHtml .= '</table>';
        if ($mensaje !== '') {
            $bodyHtml .= '<div style="margin-top:20px;"><p style="color:#78716c;font-size:.8rem;margin-bottom:4px;">Mensaje</p><p style="color:#1C1C1E;line-height:1.6;background:#F8F6F3;padding:14px 16px;border-radius:8px;">' . nl2br(htmlspecialchars($mensaje)) . '</p></div>';
        }
        $bodyHtml .= '<hr style="border:none;border-top:1px solid #e2d9d3;margin:24px 0;">';
        $bodyHtml .= '<p style="color:#a1a1aa;font-size:.75rem;text-align:center;">Mensaje enviado automáticamente desde arcoseguros.com.gt</p>';
        $bodyHtml .= '</div>';

        $plainText = "Arco Seguros — {$tituloAsunto}\n\n";
        $plainText .= "Nombre: {$nombre}\nCorreo: {$correo}\n";
        if ($telefono !== '') $plainText .= "Teléfono: {$telefono}\n";
        if ($servicioInteres !== '') $plainText .= "Seguro: {$servicioInteres}\n";
        if ($mensaje !== '') $plainText .= "\nMensaje:\n{$mensaje}\n";

        $sent = false;

        if (!$sent) {
            $sent = $this->enviarConMailFunction($destino, $subject, $bodyHtml, $plainText, $correo, $nombre);
        }

        if (!$sent) {
            $sent = $this->enviarConPhpMailer($destino, $subject, $bodyHtml, $correo, $nombre);
        }

        if ($sent) {
            $_SESSION['contacto_success'] = '¡Gracias! Tu mensaje fue enviado correctamente. Te contactaremos pronto.';
        } else {
            $_SESSION['contacto_error'] = 'No se pudo enviar tu mensaje. Por favor, intenta de nuevo más tarde o contáctanos directamente al +502 4173-3482.';
        }

        header('Location: ?url=' . $volverA);
        exit;
    }

    private function enviarConMailFunction(string $destino, string $subject, string $htmlBody, string $plainText, string $replyEmail, string $replyName): bool
    {
        $boundary = md5(uniqid(time()));

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "From: Arco Seguros <{$destino}>\r\n";
        $headers .= "Reply-To: {$replyName} <{$replyEmail}>\r\n";
        $headers .= "X-Mailer: ArcoSeguros/1.0\r\n";

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $plainText . "\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        $body .= "--{$boundary}--";

        $result = @mail($destino, $subject, $body, $headers);
        if (!$result) {
            error_log('[CONTACT-FORM] mail() failed for subject: ' . $subject);
            return false;
        }
        error_log('[CONTACT-FORM] mail() returned true for: ' . $replyEmail);
        return true;
    }

    private function enviarConPhpMailer(string $destino, string $subject, string $htmlBody, string $replyEmail, string $replyName): bool
    {
        $exceptionFile = ROOT_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';
        $phpmailerFile = ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        $smtpFile      = ROOT_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';

        if (!file_exists($exceptionFile) || !file_exists($phpmailerFile) || !file_exists($smtpFile)) {
            error_log('[CONTACT-FORM] PHPMailer files not found, skipping SMTP');
            return false;
        }

        require_once $exceptionFile;
        require_once $phpmailerFile;
        require_once $smtpFile;

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(false);

            $mail->isSMTP();
            $mail->Host       = Environment::get('SMTP_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = Environment::get('SMTP_USER', '');
            $mail->Password   = Environment::get('SMTP_PASS', '');
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int) Environment::get('SMTP_PORT', 465);
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10;

            $mail->setFrom(
                Environment::get('SMTP_FROM', ''),
                Environment::get('SMTP_FROM_NAME', 'Arco Seguros') . ' - Sitio Web'
            );
            $mail->addAddress($destino);
            if (Validator::email($replyEmail)) {
                $mail->addReplyTo($replyEmail, $replyName !== '' ? $replyName : $replyEmail);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            error_log('[CONTACT-FORM] PHPMailer SMTP sent OK');
            return true;

        } catch (Exception $e) {
            error_log('[CONTACT-FORM] PHPMailer Error: ' . $e->getMessage());
            return false;
        }
    }
}
