<?php

/*
 * Configuración y utilidades comunes de seguridad.
 * Se incluye al inicio de cada script PHP.
 */

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
// Base de datos (VUL-19, VUL-21)
// ---------------------------------------------------------------------------
function conectar()
{
    $con=mysqli_connect("localhost","root","","pnk_security");
    return $con;
}

/**
 * Consulta parametrizada. $tipos usa los mismos códigos de bind_param ("i", "s", ...).
 * Devuelve el mysqli_result para consultas SELECT, o true para INSERT/UPDATE/DELETE.
 */
function consulta(string $sql, string $tipos = '', array $params = [])
{
    $con  = conectar();   // la conexión debe seguir referenciada mientras se ejecuta la sentencia
    $stmt = $con->prepare($sql);
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
    $titulo =str_replace(" ", "", $titulo);
    $cadena =str_replace("ñ", "", $titulo);
    $cadena =str_replace("Ñ", "", $cadena);
    return $cadena;
}

function moneda_chilena($numero){
    $numero = (string)$numero;
    $puntos = floor((strlen($numero)-1)/3);
    $tmp = "";
    $pos = 1;
    for($i=strlen($numero)-1; $i>=0; $i--){
    $tmp = $tmp.substr($numero, $i, 1);
    if($pos%3==0 && $pos!=strlen($numero))
    $tmp = $tmp.".";
    $pos = $pos + 1;
    }
    $formateado = "$ ".strrev($tmp);
    return $formateado;
    }

?>