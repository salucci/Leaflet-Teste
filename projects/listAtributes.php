<?php
header('Content-Type: application/json; charset=utf-8');
$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');


if ($_POST["TargetTable"] == "default")
{
	$sql = "SELECT * FROM atributos";
}
else
{
	$sql = "DESCRIBE ".$_POST["TargetTable"];
}




$result = $mysqli->query($sql);		
$rows = [];	
while($row = mysqli_fetch_assoc($result))
{
	if($_POST["TargetTable"] = "default"){
    $rows[] = $row;}
	else{ if ($row["Field"] != "Codigo" && $row["Field"] != "Nome")
	{$rows[] = array("Codigo" => 0, "AttAlias" => $row["Field"]);}}
}

function encode_all_strings($arr) {
    foreach($arr as $key => $value) {
        $arr[$key] = utf8_encode($value);
   }
   return $arr;
}

echo $json = json_encode(array_map('encode_all_strings', $rows))

//echo json_encode($rows);



?>