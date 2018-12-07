<?php
session_start();
//Calula se a ultima ativiade na sessão tem mais de x segundos e atualiza a ultima atividade
$_SESSION['TIMEOUT'] = ini_get("session.gc_maxlifetime");

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > $_SESSION['TIMEOUT']) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

  	<script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	
	<style>
.customplace {color:#999;}

[hidden] {
  display: none !important;
}

</style>

    <title>Upload File</title>
    </head>
    <body>
	
    <div class="container">
	<nav class="navbar navbar-default">
<div class="container-fluid">
<ul class="nav navbar-nav navbar-left">
        <li><a href="./Leaflet-Teste/Leafletmenu.php">Home</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Tabelas Customizadas<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="./uploadfile.php">Enviar Tabela</a></li>
            <li><a href="./usermenu.php">Selecionar Tabela </a></li>
          </ul>
        </li>
      </ul>
</div>
</nav>
	 <div class="panel panel-primary col-md-4">
                <div class="panel-heading">
                     <h3 class="panel-title">Upload de tabela de dados</h3>
                </div>
                <div class="panel-body">

 <form class="form" action="ProcessReceivedFile.php" method="post" enctype="multipart/form-data">
 
 						<div class="form-group">
                            							<label class="control-label">Tipo de Dados:</label>
								<div class="container-fluid">

									<div class="btn-group input-group btn-group-justified" id="radiotarget" data-toggle="buttons">
                                    <label class="btn btn-success active">
                                        <input type="radio" name="options" name="target" value="municipios" id="option1" checked />Municípios</label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="options" name="target" value="estados" id="option2" />Estados</label>
                                    <label class="btn btn-danger">
                                        <input type="radio" name="options" name="target" value="regioes" id="option3" />Regiões</label>
									</div>
								
								</div>
							
                        </div>

<div class="form-group">
		<span class="btn btn-primary btn-file">
    Procurar <input type="file" name="fileToUpload">
</span>
	</div>

<div class="form-group">
							<div class="container-fluid">
								<button type="submit" class="btn btn-primary mb-2">Enviar</button>		
							</div>							
                        </div>
</form>
</div> <!-- /panel body -->
</div> <!-- /panel -->
</div> <!-- /container -->
</body>
	
	<script type="text/javascript">
/*	

function bs_input_file() {
	$(".input-file").before(
		function() {
			if ( ! $(this).prev().hasClass('input-ghost') ) {
				var element = $("<input type='file' class='input-ghost' style='visibility:hidden; height:0'>");
				element.attr("name",$(this).attr("name"));
				element.change(function(){
					element.next(element).find('input').val((element.val()).split('\\').pop());
				});
				$(this).find("button.btn-choose").click(function(){
					element.click();
				});
				$(this).find("button.btn-reset").click(function(){
					element.val(null);
					$(this).parents(".input-file").find('input').val('');
				});
				$(this).find('input').css("cursor","pointer");
				$(this).find('input').mousedown(function() {
					$(this).parents('.input-file').prev().click();
					return false;
				});
				return element;
			}
		}
	);
}
$(function() {
	bs_input_file();
});

 */

	</script>
</html>
