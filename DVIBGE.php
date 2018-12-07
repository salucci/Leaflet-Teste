<html>
<head>
</head>
<body>
<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');



	
$sql = "SELECT Codigo,cod_mun from municipal2017";


$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
while($row = mysqli_fetch_assoc($result))
{
if (strlen($row["cod_mun"]) == 6)
{
	$digito_verificador = calcula_dv_municipio($row["cod_mun"]);

	echo $row["cod_mun"]+"   ";
	echo $digito_verificador;
	$final = $row["cod_mun"] . $digito_verificador;
	$sql = "update municipal2017 set cod_mun = '".$final."' where Codigo = ".$row["Codigo"];
	$result2 = $mysqli->query($sql) or die(mysqli_error($mysqli));

	//echo "<p>".mysqli_fetch_assoc($result)."</p><br/>";
}	
}//TODO

function calcula_dv_municipio($codigo_municipio){
$peso = "1212120";
$soma = 0;
for($i = 0; $i < 7; $i++){ $valor = substr($codigo_municipio,$i,1) * substr($peso,$i,1); if($valor>9)
$soma = $soma + substr($valor,0,1) + substr($valor,1,1);
else
$soma = $soma + $valor;
}
$dv = (10 - ($soma % 10));
if(($soma % 10)==0)
$dv = 0;
return $dv;
}



//echo json_encode($rows);



?>
</body>
<html>