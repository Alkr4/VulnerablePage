<?php
declare(strict_types=1);

include("setup/setup.php");
iniciar_sesion();
exigir_post_csrf();

if (!isset($_SESSION["carrito"]) || !is_array($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

switch ($_POST['op'] ?? '')
{
    case "1": insertar();
        break;
    case "2": eliminaritems();
        break;
    case "3": eliminartodo();
        break;
    default:
        http_response_code(400);
}

function insertar()
{
    $id = entero_positivo($_POST['iditems'] ?? null);   // VUL-04
    $res = $id > 0
        ? consulta("SELECT id, nombre, precio FROM items WHERE id = ? AND visible = 1 AND eliminado IS NULL", "i", [$id])
        : null;
    $datos = $res ? $res->fetch_assoc() : null;
    if (!$datos) {
        http_response_code(404);
        return;
    }

    $pos = $_SESSION["carrito"] ? max(array_keys($_SESSION["carrito"])) + 1 : 1;
    $_SESSION["carrito"][$pos] = ["posicion" => $pos, "id" => $datos['id'], "nombre" => $datos['nombre'], "precio" => $datos['precio']];
}

function eliminaritems()
{
    $pos = entero_positivo($_POST['pos'] ?? null);
    unset($_SESSION["carrito"][$pos]);
}

function eliminartodo()
{
    // Sólo se vacía el carrito; ya no se destruye la sesión completa (antes cerraba el login).
    $_SESSION["carrito"] = [];
}
