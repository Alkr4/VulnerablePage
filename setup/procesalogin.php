<?php
declare(strict_types=1);

include(__DIR__ . "/setup.php");
iniciar_sesion();
exigir_post_csrf();

const MAX_INTENTOS = 5;      // intentos fallidos permitidos
const VENTANA_SEG  = 900;    // 15 min de bloqueo / ventana de conteo

/** Archivo de contador por IP + usuario (sin tocar la base de datos). */
function archivo_intentos(string $email): string
{
    $clave = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . strtolower($email));
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pnk_login_' . $clave;
}

function intentos_recientes(string $archivo): int
{
    if (!is_file($archivo) || (time() - filemtime($archivo)) > VENTANA_SEG) {
        return 0;
    }
    return (int)file_get_contents($archivo);
}

$email    = is_string($_POST['frmusuario'] ?? null) ? trim($_POST['frmusuario']) : '';
$password = is_string($_POST['frmpassword'] ?? null) ? $_POST['frmpassword'] : '';
$archivo  = archivo_intentos($email);

// VUL-16: límite de intentos
if (intentos_recientes($archivo) >= MAX_INTENTOS) {
    $_SESSION['flash'] = 'Demasiados intentos fallidos. Inténtalo nuevamente en unos minutos.';
    header('Location: ../index.php' . (isset($_SESSION['id']) ? '?id=' . (int)$_SESSION['id'] : ''));
    exit;
}

// VUL-02: consulta parametrizada
$fila = null;
if ($email !== '' && strlen($email) <= 255 && strlen($password) <= 1024) {
    $res  = consulta("SELECT nombre, password FROM usuarios WHERE email = ? LIMIT 1", "s", [$email]);
    $fila = $res->fetch_assoc();
}

/*
 * VUL-17: se acepta un hash moderno (password_hash / bcrypt / argon2) y, de forma transitoria,
 * las contraseñas heredadas en texto plano (la tabla aún no ha sido migrada). Cuando se migre la
 * columna a hashes, este código ya es compatible sin cambios.
 */
$ok = false;
if ($fila !== null) {
    $almacenada = (string)$fila['password'];
    $ok = password_get_info($almacenada)['algo'] !== null
        ? password_verify($password, $almacenada)
        : hash_equals($almacenada, $password);
} else {
    // Trabajo equivalente para no filtrar por tiempo si el usuario existe
    password_verify($password, '$2y$10$usesomesillystringforsaltdummyhashvalueforconstanttimexx');
}

if ($ok) {
    @unlink($archivo);
    session_regenerate_id(true);              // VUL-15: nuevo ID al cambiar de privilegio
    $_SESSION['nombre'] = $fila['nombre'];
    unset($_SESSION['csrf']);                 // token nuevo para la sesión autenticada
} else {
    file_put_contents($archivo, (string)(intentos_recientes($archivo) + 1), LOCK_EX);
    $_SESSION['flash'] = 'Usuario o contraseña incorrectos.';
}

header('Location: ../index.php' . (isset($_SESSION['id']) ? '?id=' . (int)$_SESSION['id'] : ''));
exit;
