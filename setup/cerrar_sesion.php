<?php

include("setup.php");
iniciar_sesion();
destruir_sesion();   // VUL-15: el identificador de sesión anterior deja de ser válido

header('Location:../index.php');

?>