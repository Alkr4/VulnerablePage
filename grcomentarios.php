<?php

include("setup/setup.php");
session_start();

$id_restaurante = entero_positivo($_SESSION['id'] ?? 0);

consulta(
    "INSERT INTO comentarios (usuario, comentario, id_restaurante) VALUES (?, ?, ?)",
    "ssi",
    [(string)($_POST['usuario'] ?? ''), (string)($_POST['comentario'] ?? ''), $id_restaurante]
);

header('Location: index.php?id=' . $id_restaurante);
exit;
