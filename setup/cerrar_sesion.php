<?php

include("setup.php");
iniciar_sesion();
session_destroy();

header('Location:../index.php');

?>