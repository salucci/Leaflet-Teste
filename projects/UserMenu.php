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

if((!isset($_SESSION['login'])) || (!isset ($_SESSION['senha'])) || (!isset ($_SESSION['id'])) || (!isset ($_SESSION['na'])))
{
    unset($_SESSION['login']);
    unset($_SESSION['senha']);
	unset ($_SESSION['na']);
	unset ($_SESSION['id']);     
    header('location:Leaflet-Teste/LeafletMenu.php');
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
	
	 <div class="panel panel-primary col-md-4">
	 <a><?php echo 'Bem Vindo:'.$_SESSION['login'].' '.$_SESSION['senha'].' '.$_SESSION['na'].' '.$_SESSION['id']; ?></a>
                <div class="panel-heading">
                     <h3 class="panel-title">Menu do Sistema</h3>
                </div>
                <div class="panel-body">
				
<select id="var1" name="var1" class="form-control varfield" onchange="doSomething();" onfocus="this.selectedIndex = -1;">
										<option value="" disabled selected hidden>Tabelas</option>
										<?php
											$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');
											$sql = "SELECT * from tablecontrol where user = ".$_SESSION['id'];
											$result = $mysqli->query($sql);	
											while($row = mysqli_fetch_assoc($result)){
												echo '<option value="'.$row["Codigo"].'">'.$row["TableName"].'</option>';
											}
										?>
</select>
<a class="btn btn-primary" href="UploadFile.php" role="button">Enviar Tabela</a>
<?php
if ($_SESSION['na'] = 3)
{
echo '<a class="btn btn-primary" href="UploadFile.php" role="button">Gerar Relatório</a>';
}
?>

</div> <!-- /panel body -->
</div> <!-- /panel -->
</div> <!-- /container -->
</body>
	
	<script type="text/javascript">
	
	 

	</script>
</html>
