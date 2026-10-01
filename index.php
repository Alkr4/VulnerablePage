<?php
declare(strict_types=1);

include("setup/setup.php");
iniciar_sesion();

// VUL-03/06/22: el identificador se valida como entero positivo (0 = sin restaurante)
$key = entero_positivo($_GET['id'] ?? null);
$_SESSION['id'] = $key;

$datos_restorant = ['calle'=>'','numero'=>'','comuna'=>'','region'=>'','nombre'=>'','id'=>0,'fono'=>'','email'=>'','foto'=>''];
if ($key > 0) {
    $result_restorant = consulta(
        "SELECT direcciones.calle, direcciones.numero, direcciones.comuna, direcciones.region, restautantes.nombre, restautantes.id, restautantes.fono, restautantes.email, restautantes.foto
         FROM restautantes INNER JOIN direcciones ON restautantes.direcciones_id = direcciones.id
         WHERE restautantes.id = ? AND restautantes.eliminado IS NULL", "i", [$key]);
    $datos_restorant = $result_restorant->fetch_assoc() ?: $datos_restorant;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo h(csrf_token());?>">
  <title><?php echo hu($datos_restorant['nombre']);?></title>
	<!--<link rel="icon" href="img/Fevicon.png" type="image/png">-->

  <link rel="stylesheet" href="vendors/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="vendors/themify-icons/themify-icons.css">
  <link rel="stylesheet" href="vendors/owl-carousel/owl.theme.default.min.css">
  <link rel="stylesheet" href="vendors/owl-carousel/owl.carousel.min.css">

  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-light bg-light">
   <?php
    if(!isset($_SESSION['nombre']))
    {
    ?>
      <?php if (!empty($_SESSION['flash'])) { echo '<span class="text-danger small ms-2">'.h($_SESSION['flash']).'</span>'; unset($_SESSION['flash']); } ?>
      <form class="d-flex flex-wrap align-items-center gap-2" role="search" action="setup/procesalogin.php" method="post" autocomplete="off">
        <?php echo csrf_campo();?>
        <div class="input-group w-auto">
          <span class="input-group-text" id="basic-addon1">@</span>
          <input type="text" class="form-control" name="frmusuario" placeholder="Usuario" maxlength="255" required>
        </div>
        <div>
          <input type="password" class="form-control" name="frmpassword" placeholder="Contraseña" maxlength="1024" required>
        </div>
        <button type="submit" class="btn btn-outline-primary my-2 my-sm-0">Ingresar</button>
      </form>
    <?php
    }else{
      echo '<form class="d-flex align-items-center gap-2 m-0" action="setup/cerrar_sesion.php" method="post">Bienvenido: '.h($_SESSION['nombre']).' - '.csrf_campo().'<button type="submit" class="btn btn-link p-0">Cerrar Sesión</button></form>';
    }
  ?>
</nav>
  <section>
    <div class="container contendor">
      <div class="row">
        <div class="col-lg-4">
          <div style="text-align: center;">
            <div class="d-flex">
              <img class="logosintituciones" src="img/logo.png" width="190px" alt="">
            </div>
          </div>  
        </div>
        <div class="col-lg-4">
          <div style="text-align: center;">
            <div class="d-flex">
              <?php
              if($datos_restorant['foto']!="")
              {  
                ?>
                  <img class="logosintituciones" src="imagenes/cod<?php echo (int)$key;?>/<?php echo h($datos_restorant['foto']);?>" width="170px" alt="">
              <?php
              }else{
                ?>
                  <img class="logosintituciones" src="img/logo_empresa_comodin.png" width="170px" alt="">
                <?php
              }
              ?>
            </div>
          </div>  
        </div>
        <div class="col-lg-4">
          <div class="carro">
            <div class="d-flex float-end">
              <a class="button_carrito" href="mostrar_carrito.php?keyid=<?php echo (int)$key;?>">
              <svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-cart4" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l.5 2H5V5H3.14zM6 5v2h2V5H6zm3 0v2h2V5H9zm3 0v2h1.36l.5-2H12zm1.11 3H12v2h.61l.5-2zM11 8H9v2h2V8zM8 8H6v2h2V8zM5 8H3.89l.5 2H5V8zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
              </svg>
              Productos Seleccionados
              </a>
            </div>
          </div>  
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
            <div class="text-center">
            <?php
               if($key>0)
               {
                 ?>
               
              <h4><?php echo hu($datos_restorant['nombre']);?></h4>
              <h5>
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-geo-alt" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M12.166 8.94C12.696 7.867 13 6.862 13 6A5 5 0 0 0 3 6c0 .862.305 1.867.834 2.94.524 1.062 1.234 2.12 1.96 3.07A31.481 31.481 0 0 0 8 14.58l.208-.22a31.493 31.493 0 0 0 1.998-2.35c.726-.95 1.436-2.008 1.96-3.07zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                  <path fill-rule="evenodd" d="M8 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                </svg>  
              <?php echo hu($datos_restorant['calle'])." #".h($datos_restorant['numero']).", ".hu($datos_restorant['comuna']);?>
              </h5>
              <?php
               }
               ?>
              <h5>
              <?php 
              if(!$datos_restorant['fono']=="")
              {
                ?>
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-telephone-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M2.267.98a1.636 1.636 0 0 1 2.448.152l1.681 2.162c.309.396.418.913.296 1.4l-.513 2.053a.636.636 0 0 0 .167.604L8.65 9.654a.636.636 0 0 0 .604.167l2.052-.513a1.636 1.636 0 0 1 1.401.296l2.162 1.681c.777.604.849 1.753.153 2.448l-.97.97c-.693.693-1.73.998-2.697.658a17.47 17.47 0 0 1-6.571-4.144A17.47 17.47 0 0 1 .639 4.646c-.34-.967-.035-2.004.658-2.698l.97-.969z"/>
                </svg>  
                <?php echo h($datos_restorant['fono']);?>
              <?php
              }
              if(!$datos_restorant['email']=="")
              {
                ?>
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-envelope-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757zm3.436-.586L16 11.801V4.697l-5.803 3.546z"/>
                </svg>
                <?php echo h($datos_restorant['email']);?>
                <?php
              }
              ?>
            </h5>
            </div>
        </div>
      </div>
    </div>
  </section>
  <!--DESTACADOS-->
  <?php
  $result=consulta("SELECT items.id,items.visible,items.tiempo,items.puntuacion,items.destacado, items.precio, items.descripcion,items.observaciones, items.nombre, items.foto, items.orden, cartas.restautantes_id, categorias.visible FROM categorias INNER JOIN items ON items.categorias_id = categorias.id INNER JOIN cartas ON categorias.cartas_id = cartas.id
  WHERE items.destacado = 1 AND items.eliminado IS NULL AND items.visible=1 AND cartas.restautantes_id = ? AND cartas.eliminada IS NULL AND categorias.visible = 1 AND categorias.eliminado IS NULL","i",[$key]);
  $cont_destacados=$result->num_rows;
  if( $cont_destacados!=0)
  {
  ?>
  <section class="destacados">
    <div class="container contendor">
      <div class="section-intro">
        <h4 class="intro-title">Menús Destacados</h4>
      </div>
      <div class="owl-carousel owl-theme featured-carousel">
      <?php
      while($destacados=$result->fetch_assoc())
      {
      ?>
        <div class="featured-item">
        <?php
              if($destacados['foto']!="")
              {  
                ?>
                  <img class="card-img rounded-0" width="350px" height="235px" src="imagenes/cod<?php echo (int)$key;?>/<?php echo h($destacados['foto']);?>" alt="">
              <?php
              }else{
                ?>
                  <img class="card-img rounded-0" src="img/carta_comodin.png" width="350px" height="235px" alt="">
                <?php
              }
              ?>
          <div class="item-body">
              <h3><?php echo hu($destacados['nombre']);?></h3>
            <p><?php echo hu($destacados['descripcion']);?><br>
            <?php echo hu($destacados['observaciones']);?><br>
            <?php
            if($destacados['tiempo']!="")
            {
              ?>

            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-alarm" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5zM0 3.5c0 .753.333 1.429.86 1.887A8.035 8.035 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1z"/>
                </svg>
                <?php echo " ".h($destacados['tiempo']);?>
            <?php
            }
            ?>
            </p>
            <div class="d-flex justify-content-between">
              <ul class="rating-star">
                <?php
                for($i=1;$i<=(int)$destacados['puntuacion'];$i++)
                {
                  ?>
                    <li><i class="ti-star"></i></li>
                <?php
                }
                ?>
              </ul>
              <h3 class="price-tag"><?php             
              echo moneda_chilena($destacados['precio']);?></h3>
            </div>
            <a class="button" href="#" id="<?php echo h($destacados['id']);?>" style="margin:0%">Seleccionar</a>
          </div>
        </div>
      <?php
      }
      ?>
      </div>
    </div>
  </section>
<?php
  }
?>
<section>
    <div class="container contendor">
       <div class="container">
              <div class="row">
                <div class="col-lg-12">
                  <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                        <?php
                            $resultcartas=consulta("SELECT cartas.id,cartas.restautantes_id,cartas.nombre,cartas.orden,cartas.visible FROM cartas WHERE cartas.restautantes_id = ? and visible=1 AND eliminada IS NULL order by orden asc","i",[$key]);
                            $arraycartas=[];
                            while($cartas=$resultcartas->fetch_assoc())
                            {
                              $idcarta=quitarespacios(latin1_a_utf8($cartas['nombre']));
                              array_push($arraycartas,['nombre'=>$idcarta,'id'=>$cartas['id']]);
                            ?>
                              <a class="nav-item nav-link<?php if(count($arraycartas)==1){echo ' active';}?>" id="nav-<?php echo h($idcarta);?>-tab" data-bs-toggle="tab" href="#<?php echo h($idcarta);?>" role="tab" aria-controls="nav-profile" aria-selected="false"><?php echo hu($cartas['nombre']);?></a>
                            <?php
                            }
                        ?>
                    </div>
                  </nav>
                  <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                    <?php
                     for($i=0;$i<count($arraycartas);$i++)
                     {                             
                    ?>
                    <div class="tab-pane fade <?php if($i==0){?>show active<?php } ?>" id="<?php echo h($arraycartas[$i]["nombre"]);?>" role="tabpanel" aria-labelledby="nav-<?php echo h($arraycartas[$i]["nombre"]);?>-tab">              
                      <?php
                          $result_categorias=consulta("select id,nombre from categorias where visible=1 and cartas_id=? AND eliminado IS NULL order by orden asc","i",[(int)$arraycartas[$i]["id"]]);
                          $cont_categorias=$result_categorias->num_rows;
                          if($cont_categorias==0)
                          {?>
                              <div class="row">
                                <div class="col-lg-12">
                                  <div class="text-center">
                                    <h4>No hay información para Mostrar</h4>
                                  </div>
                                </div>
                              </div>
                          <?php
                          }else{
                              while($datos_categorias=$result_categorias->fetch_assoc())
                              {
                              ?>
                                <div class="section-intro mb-20px">
                                    <h4 class="intro-title"><?php echo hu($datos_categorias['nombre']);?></h4>
                                </div>

                                <div class="row">
                                  <?php
                                      $result_items=consulta("SELECT items.visible,items.id, items.nombre, items.descripcion,items.observaciones,items.tiempo,items.puntuacion, items.precio, items.visible, items.foto, items.orden, items.categorias_id FROM items WHERE items.categorias_id = ? AND items.visible=1 AND eliminado IS NULL order by orden asc","i",[(int)$datos_categorias['id']]);
                                      $count_items=$result_items->num_rows;
                                      if($count_items!=0)
                                      {
                                            while($datos_items=$result_items->fetch_assoc())
                                            {
                                            ?>
                                            <div class="<?php if($count_items>1){ ?>col-lg-6 <?php }else{ ?>col-lg-12<?php } ?>">
                                              <div class="d-flex align-items-center food-card">
                                                <?php
                                                if($datos_items['foto']=="")
                                                {
                                                ?>
                                                <img class="me-3 me-sm-4" src="img/carta_comodin.png" alt="" width="120px">
                                                <?php
                                                }else{
                                                  ?>
                                                <img class="me-3 me-sm-4" src="imagenes/cod<?php echo (int)$key;?>/<?php echo h($datos_items['foto']);?>" alt="" width="120px">
                                                  <?php
                                                }
                                                ?>
                                                <div class="flex-grow-1">
                                                  <div class="d-flex justify-content-between food-card-title">
                                                    <h4><?php echo hu($datos_items['nombre']);?></h4>
                                                    <h3 class="price-tag"><?php echo moneda_chilena($datos_items['precio']);?></h3>
                                                  </div>
                                                  <p><?php echo hu($datos_items['descripcion']);?></br>
                                                  <?php echo hu($datos_items['observaciones']);?></p>
                                                  <?php
                                                  if($datos_items['tiempo']!="")
                                                  {
                                                    ?>
                                                  <p><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-alarm" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                      <path fill-rule="evenodd" d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5zM0 3.5c0 .753.333 1.429.86 1.887A8.035 8.035 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1z"/>
                                                      </svg>
                                                      <?php echo " ".h($datos_items['tiempo']);?></p>
                                                  <?php
                                                  }
                                                  ?>
                                                      <p>
                                                        <ul class="rating-star">
                                                          <?php
                                                          for($k=1;$k<=(int)$datos_items['puntuacion'];$k++)
                                                          {
                                                            ?>
                                                              <li><i class="ti-star"></i></li>
                                                          <?php
                                                          }
                                                          ?>
                                                        </u>
                                                      </p>
                                                  <a class="button float-right" href="#" id="<?php echo h($datos_items['id']);?>">Seleccionar</a>
                                                </div>
                                              </div>
                                            </div>
                                      <?php
                                            }
                                      }
                                      else
                                      {
                                      ?>
                                        <div class="col-lg-12 ">
                                          <div class="text-center">
                                                <h4>No Información para Mostrar</h4>
                                          </div>
                                        </div>
                                      <?php 
                                        }   
                                      ?>
                                </div>
                                <?php
                              }
                            }
                      ?>
                    </div>
                    <?php
                     }
                     ?>
                  </div>     
                </div>
              </div>
        </div>
    </div>
</section>
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-lg-center" id="exampleModalLabel">Selección de Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        El producto seleccionado, ha sido agregado sin problemas!!!
      </div>
    </div>
  </div>
</div>


<?php

if($key>0)
{
?>
<section>
  <div class="container contendor">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-intro">
            <h4 class="intro-title">Comentarios</h4>
             <?php
            if(isset($_SESSION['nombre']))
            {
             ?>
             <form action="grcomentarios.php" method="post">
              <?php echo csrf_campo();?>
              <div class="mb-3">
                <label for="usr">Nombre:</label>
                <input type="text" class="form-control" id="usuario" value="<?php echo h($_SESSION['nombre']);?>" readonly>
              </div>
              <div class="mb-3">
                <label for="comment">Comentario:</label>
                <textarea class="form-control" rows="5" id="comentario" name="comentario" maxlength="1000" required></textarea>
              </div>
              <button type="submit" class="btn btn-success fa-align-right">Comentar</button>
           </form>
           <?php
            }
          ?>
          </div>
          <br>
          <?php

            $resultcomentarios=consulta("select usuario, comentario from comentarios where id_restaurante=?","i",[$key]);
            while($datoscomentarios=$resultcomentarios->fetch_assoc())
            {
          ?>
          <div class="card bg-light">
            <div class="card-body">
              <b><?php echo h($datoscomentarios['usuario']);?></b>
              <br>
              <?php echo nl2br(h($datoscomentarios['comentario']));?>
            </div>
          </div>
          <br>
          <?php
            }
          ?>
          <hr>
        </div>
      </div>
    </div>
  </div>
  </section>
<?php
}
?>

  <footer class="footer-area section-gap"><br>
		<div class="container">
			<div class="footer-bottom row align-items-center text-center">
				<p class="footer-text m-0 col-md-12">
            Copyright 2020, Todos los Derechos reservados PNK, <br>
            Sitio desarrollado para la Explotación de Vulnerabilidades Web, Laboratorio de Programación Segura I<br><b>Área Tecnologías de Información y Ciberseguridad - Inacap Sede La Serena</b></br></br>
        </p>
			</div>
		</div>
	</footer>


  <script src="vendors/jquery/jquery-3.7.1.min.js"></script>
  <script src="vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="vendors/owl-carousel/owl.carousel.min.js"></script>
  <script src="js/main.js"></script>
  <script src="js/controladorajax.js"></script>
</body>
</html>