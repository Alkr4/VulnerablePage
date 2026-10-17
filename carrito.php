<?php

include("setup/setup.php");
iniciar_sesion();
exigir_post_csrf();

switch($_POST['op'])
{
    case "1": insertar();
        break;
    case "2": eliminaritems();
        break;
    case "3": eliminartodo();
        break;
}

function insertar()
{
    $_SESSION["carrito"];
    $id = entero_positivo($_POST['iditems'] ?? null);
    $result = consulta("SELECT id, nombre, precio FROM items WHERE id = ? AND visible = 1 AND eliminado IS NULL", "i", [$id]);
    $datos = $result->fetch_assoc();
    if (!$datos) {
        http_response_code(404);
        return;
    }

    $pos=count($_SESSION["carrito"])+1;
    $productos = array("posicion"=>$pos,"id" => $datos['id'], "nombre" =>$datos['nombre'],"precio"=>$datos['precio']);
    $_SESSION["carrito"][$pos] = $productos;
}

function eliminaritems()
{
    unset($_SESSION["carrito"][entero_positivo($_POST['pos'] ?? null)]);
}

function eliminartodo()
{
    $_SESSION["carrito"] = [];   // sólo se vacía el carrito; antes se destruía la sesión completa (cerraba el login)
}
?>