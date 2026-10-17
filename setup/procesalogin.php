<?php

include("setup.php");

$email    = is_string($_POST['frmusuario'] ?? null) ? trim($_POST['frmusuario']) : '';
$password = is_string($_POST['frmpassword'] ?? null) ? $_POST['frmpassword'] : '';

$fila = null;
if ($email !== '' && strlen($email) <= 255 && strlen($password) <= 1024) {
    $res  = consulta("SELECT nombre, password FROM usuarios WHERE email = ? LIMIT 1", "s", [$email]);
    $fila = $res->fetch_assoc();
}

if ($fila !== null && $fila['password'] === $password)
{
    iniciar_sesion();
    $_SESSION['nombre'] = $fila['nombre'];
}

header('Location:../index.php');

?>