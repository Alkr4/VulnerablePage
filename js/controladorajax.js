// JavaScript Document
// Las peticiones que cambian estado envían el token CSRF en la cabecera X-CSRF-Token (VUL-14).

$(document).ready(function(){
   $.ajaxSetup({
     headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
   });

   $('.button').on('click', function(e){
     e.preventDefault();
     agregaritems($(this).attr('id'));
   });

   $('.elim').on('click', function(e){
      e.preventDefault();
      eliminaritems($(this).attr('id'));
    });
    $('.limpiar').on('click', function(e){
      e.preventDefault();
      eliminartodo();
    });
 });

function agregaritems(id)
{
	$.ajax({
            type: "POST",
            url: 'carrito.php',
            data: { op: 1, iditems: id },
            success: function()
            {
				bootstrap.Modal.getOrCreateInstance(document.getElementById('myModal')).show();
            }
       });
}

function eliminaritems(pos)
{
	$.ajax({
            type: "POST",
            url: 'carrito.php',
            data: { op: 2, pos: pos },
            success: function()
            {
				location.reload();
            }
       });
}

function eliminartodo()
{
	$.ajax({
            type: "POST",
            url: 'carrito.php',
            data: { op: 3 },
            success: function()
            {
				location.reload();
            }
       });
}
