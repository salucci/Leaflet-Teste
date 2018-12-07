<?php
header('Content-Type: application/json; charset=utf-8');
$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');


	$sql = "SELECT Codigo,AttAlias FROM atributos where TableType = '".$_POST["TableType"]."'";
$result = $mysqli->query($sql);		
$rows = [];	
while($row = mysqli_fetch_assoc($result))
{
     $rows[] = $row;
}


if ($_POST["TargetTable"] != "default")
{
	$sql = "select * 
			from 
				(select COLUMN_NAME AttAlias 
				from 
					INFORMATION_SCHEMA.COLUMNS,
					(SELECT CONCAT (`SessionId`,`TableName`) as TargetTable FROM tablecontrol where Codigo = ".$_POST["TargetTable"]." and TableType = '".$_POST["TableType"]."') as a
				where TABLE_NAME = a.TargetTable) 
			as DescribedTable
			where DescribedTable.AttAlias NOT IN ('codigoibge','nome')";
			
			$result = $mysqli->query($sql);
	while($row = mysqli_fetch_assoc($result))
	{
		// if($_POST["TargetTable"] = "default"){
		//}
		$row["Codigo"] = $row["AttAlias"]."&".$_POST["TargetTable"];	
		$rows[] = $row;
		// else{ if ($row["Field"] != "Codigo" && $row["Field"] != "Nome")
		// {$rows[] = array("Codigo" => 0, "AttAlias" => $row["Field"]);}}
	}

}






function encode_all_strings($arr) {
    foreach($arr as $key => $value) {
        $arr[$key] = utf8_encode($value);
   }
   return $arr;
}

echo $json = json_encode(array_map('encode_all_strings', $rows));

//echo json_encode($rows);



?>