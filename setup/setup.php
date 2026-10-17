<?php

/*
 * Configuración y utilidades comunes de seguridad.
 * Se incluye al inicio de cada script PHP.
 */

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