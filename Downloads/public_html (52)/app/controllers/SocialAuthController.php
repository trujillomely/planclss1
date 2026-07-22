<?php

/**
 * SocialAuthController
 *
 * Verifica la identidad con Google, luego busca el email en la base de datos.
 * Si NO existe, rechaza el acceso. NO crea usuarios nuevos.
 */

require_once ROOT_PATH . '/app/models/User.php';

class SocialAuthController {

    public function callback() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?url=login');
            exit;
        }

        $provider = trim($_POST['provider'] ?? '');
        $token    = trim($_POST['token']    ?? '');

        if ($provider !== 'google' || !$token) {
            error_log('[SOCIAL-AUTH] Invalid request: provider=' . $provider . ' token_empty=' . empty($token));
            $_SESSION['error'] = 'Inicio de sesión social inválido.';
            header('Location: ?url=login');
            exit;
        }

        // ── 1. VERIFICAR TOKEN CON GOOGLE ────────────────────────────────────
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlErr) {
            error_log('[SOCIAL-AUTH] cURL error: ' . $curlErr);
            $_SESSION['error'] = 'Error de conexión con Google: ' . htmlspecialchars($curlErr);
            header('Location: ?url=login');
            exit;
        }

        if ($httpCode !== 200) {
            error_log('[SOCIAL-AUTH] Google API returned HTTP ' . $httpCode . ': ' . substr($response, 0, 500));
            $_SESSION['error'] = 'No se pudo verificar tu identidad con Google (HTTP ' . $httpCode . '). Intenta de nuevo.';
            header('Location: ?url=login');
            exit;
        }

        $data = json_decode($response, true);
        if (empty($data['email'])) {
            error_log('[SOCIAL-AUTH] Google response missing email: ' . substr($response, 0, 500));
            $_SESSION['error'] = 'Google no proporcionó un correo válido.';
            header('Location: ?url=login');
            exit;
        }

        $socialEmail = strtolower(trim($data['email']));

        // ── 2. BUSCAR EL USUARIO — NO SE CREA SI NO EXISTE ──────────────────
        $userModel = new User();
        $user      = $userModel->findByEmail($socialEmail);

        if (!$user) {
            error_log('[SOCIAL-AUTH] User not found for email: ' . $socialEmail);
            $_SESSION['error'] = 'Tu cuenta de Google (' . htmlspecialchars($socialEmail) . ') no está registrada en el sistema. Contacta al administrador.';
            header('Location: ?url=login');
            exit;
        }

        // ── 3. CREAR SESIÓN ──────────────────────────────────────────────────
        // Regenerate session ID (prevent session fixation)
        session_regenerate_id(true);

        $_SESSION['id_user']  = $user['id'];
        $_SESSION['name']     = $user['username'];
        $_SESSION['lastname'] = $user['lastname'] ?? '';
        $_SESSION['email']    = $user['email'];
        $_SESSION['role']     = $user['role_name'];
        $_SESSION['role_id']  = $user['role_id'];
        $_SESSION['avatar']   = $user['avatar_url'] ?? '';
        $_SESSION['_created'] = time();
        $_SESSION['_last_activity'] = time();

        // Load permissions
        Auth::loadPermissions($user['role_id']);

        error_log('[SOCIAL-AUTH] Successful login: ' . $socialEmail . ' role=' . $user['role_name']);

        // ── 4. REDIRIGIR SEGÚN ROL ───────────────────────────────────────────
        switch ((int)$user['role_id']) {
            case 1:
                header('Location: ?url=admin/dashboard');
            break;
            case 2:
                header('Location: ?url=gerente/dashboard');
            break;
            default:
                header('Location: ?url=cliente/dashboard');
            break;
        }
        exit;
    }
}
