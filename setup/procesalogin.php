<?php

include("setup.php");
iniciar_sesion();
exigir_post_csrf();

$email    = is_string($_POST['frmusuario'] ?? null) ? trim($_POST['frmusuario']) : '';
$password = is_string($_POST['frmpassword'] ?? null) ? $_POST['frmpassword'] : '';

$fila = null;
if ($email !== '' && strlen($email) <= 255 && strlen($password) <= 1024) {
    $res  = consulta("SELECT nombre, password FROM usuarios WHERE email = ? LIMIT 1", "s", [$email]);
    $fila = $res->fetch_assoc();
}

if ($fila !== null && $fila['password'] === $password)
{
    session_regenerate_id(true);              // VUL-15: nuevo ID al cambiar de privilegio
    $_SESSION['nombre'] = $fila['nombre'];
    unset($_SESSION['csrf']);                 // token nuevo para la sesión autenticada
}

header('Location:../index.php');

?>