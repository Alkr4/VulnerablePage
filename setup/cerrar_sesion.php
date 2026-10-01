<?php
declare(strict_types=1);

include(__DIR__ . "/setup.php");
iniciar_sesion();
exigir_post_csrf();

$id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
destruir_sesion();   // VUL-15: el identificador de sesión anterior deja de ser válido

header('Location: ../index.php' . ($id > 0 ? '?id=' . $id : ''));
exit;
