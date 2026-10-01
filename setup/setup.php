<?php
declare(strict_types=1);

/*
 * Configuración y utilidades comunes de seguridad.
 * Se incluye al inicio de cada script PHP.
 */

// ---------------------------------------------------------------------------
// Manejo de errores (VUL-13, ISO 27034 / ASVS V16): nunca mostrar detalles al cliente.
// ---------------------------------------------------------------------------
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
// mysqli lanza excepciones en lugar de warnings; se capturan en el manejador global.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

set_exception_handler(function (Throwable $e): void {
    error_log('[pnkSecurity] ' . get_class($e) . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error</title></head>'
       . '<body><h1>Error interno</h1><p>Ocurrió un problema al procesar la solicitud.</p></body></html>';
    exit;
});

// ---------------------------------------------------------------------------
// Cabeceras de seguridad HTTP (VUL-11, VUL-12)
// ---------------------------------------------------------------------------
header_remove('X-Powered-By');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; "
     . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
     . "font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; "
     . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
if (es_https()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

function es_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

// ---------------------------------------------------------------------------
// Sesión segura (VUL-10, VUL-15)
// ---------------------------------------------------------------------------
function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => es_https(),   // Se activa automáticamente cuando el sitio se sirva por HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function destruir_sesion(): void
{
    iniciar_sesion();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

// ---------------------------------------------------------------------------
// CSRF (VUL-14)
// ---------------------------------------------------------------------------
function csrf_token(): string
{
    iniciar_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

/** Valida el token recibido por POST (campo "csrf") o por cabecera X-CSRF-Token (AJAX). */
function csrf_validar(): bool
{
    iniciar_sesion();
    $enviado = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($enviado) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $enviado);
}

/** Corta la petición si no es POST o si el token CSRF es inválido. */
function exigir_post_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit;
    }
    if (!csrf_validar()) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }
}

// ---------------------------------------------------------------------------
// Base de datos (VUL-19, VUL-21)
// ---------------------------------------------------------------------------
/**
 * Conexión única. Las credenciales se leen del entorno (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS)
 * y no del código fuente. Si no están definidas se usa el valor histórico del laboratorio
 * (root sin contraseña) y se deja constancia en el log, para no romper el despliegue actual.
 */
function conectar(): mysqli
{
    static $con = null;
    if ($con === null) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $name = getenv('DB_NAME') ?: 'pnk_security';
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        if ($user === false || $user === '') {
            error_log('[pnkSecurity] ADVERTENCIA: DB_USER no definido; usando credenciales heredadas. Definir DB_USER/DB_PASS con un usuario de mínimos privilegios.');
            $user = 'root';
            $pass = '';
        }
        $con = mysqli_connect($host, $user, $pass === false ? '' : $pass, $name, (int)(getenv('DB_PORT') ?: 3306));
        // Se fija el juego de caracteres de forma explícita (el código histórico asume latin1 + utf8_encode);
        // así el resultado no depende del valor por defecto de la versión de PHP/MySQL.
        $con->set_charset('latin1');
    }
    return $con;
}

/**
 * Consulta parametrizada. $tipos usa los mismos códigos de bind_param ("i", "s", ...).
 * Devuelve el mysqli_result para consultas SELECT, o true para INSERT/UPDATE/DELETE.
 */
function consulta(string $sql, string $tipos = '', array $params = [])
{
    $stmt = conectar()->prepare($sql);
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    return $res === false ? true : $res;
}

// ---------------------------------------------------------------------------
// Validación y salida segura (VUL-06/07/08, VUL-22)
// ---------------------------------------------------------------------------
/** Entero positivo o 0 si el valor no es un entero válido. */
function entero_positivo($valor): int
{
    $n = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $n === false ? 0 : $n;
}

/** Codificación de salida HTML (contexto: contenido y atributos). */
function h($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Equivale al antiguo utf8_encode() (ISO-8859-1 -> UTF-8), sin la función eliminada en PHP 8.2+. */
function latin1_a_utf8($valor): string
{
    return mb_convert_encoding((string)($valor ?? ''), 'UTF-8', 'ISO-8859-1');
}

/** latin1_a_utf8 + codificación de salida HTML. */
function hu($valor): string
{
    return h(latin1_a_utf8($valor));
}

function quitarespacios($titulo)
{
    $titulo =str_replace(" ", "", (string)$titulo);
    $cadena =str_replace("ñ", "", $titulo);
    $cadena =str_replace("Ñ", "", $cadena);
    return $cadena;
}

function moneda_chilena($numero){
    $numero = (string)$numero;
    $tmp = "";
    $pos = 1;
    for($i=strlen($numero)-1; $i>=0; $i--){
        $tmp = $tmp.substr($numero, $i, 1);
        if($pos%3==0 && $pos!=strlen($numero))
            $tmp = $tmp.".";
        $pos = $pos + 1;
    }
    return "$ ".strrev($tmp);
}
