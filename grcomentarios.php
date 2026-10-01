<?php
declare(strict_types=1);

include("setup/setup.php");
iniciar_sesion();
exigir_post_csrf();

// VUL-09: sólo usuarios autenticados pueden comentar
if (empty($_SESSION['nombre'])) {
    http_response_code(403);
    exit('Debes iniciar sesión para comentar.');
}

$id_restaurante = entero_positivo($_SESSION['id'] ?? 0);
$comentario     = is_string($_POST['comentario'] ?? null) ? trim($_POST['comentario']) : '';

if ($id_restaurante > 0 && $comentario !== '') {
    $comentario = mb_substr($comentario, 0, 1000);
    // El autor es siempre el usuario de la sesión (no se acepta un nombre enviado por el cliente).
    consulta(
        "INSERT INTO comentarios (usuario, comentario, id_restaurante) VALUES (?, ?, ?)",
        "ssi",
        [(string)$_SESSION['nombre'], $comentario, $id_restaurante]
    );
}

header('Location: index.php?id=' . $id_restaurante);
exit;
