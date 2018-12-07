<?php
header('Content-Type: application/json; charset=utf-8');
$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');
	//if ($mysqli->connect_errno) {
		//echo "Sorry, this website is experiencing problems.";
		//echo "Error: Failed to make a MySQL connection, here is why: \n";
		//echo "Errno: " . $mysqli->connect_errno . "\n";
		//echo "Error: " . $mysqli->connect_error . "\n";
		//exit;
	//}
	

//setup fuzzy

$Fuzzy = [];
$Fuzzy[]=array("li" => 0, "t" => 0,"ls" => 0.25,"k" => 0);
$Fuzzy[]=array("li" => 0, "t" => 0.25,"ls" => 0.5,"k" => 0);
$Fuzzy[]=array("li" => 0.25, "t" => 0.5,"ls" => 0.75,"k" => 0);
$Fuzzy[]=array("li" => 0.5, "t" => 0.75,"ls" => 1,"k" => 0);
$Fuzzy[]=array("li" => 0.75, "t" => 1,"ls" => 1,"k" => 0);
$Fuzzy[]=array("li" => 0, "t" => 1,"ls" => 1,"k" => 0);


//$Peq[] = array();
//$Peq["li"] = 0;$Peq["t"] = 0;$Peq["ls"] = 0.25;
//$MPeq[] = array();
//$MPeq["li"] = 0;$MPeq["t"] = 0.25;$MPeq["ls"] = 0.5;
//$Med[] = array();
//$Med["li"] = 0.25;$Med["t"] = 0.5;$Med["ls"] = 0.75;
//$MGrande[] = array();
//$MGrande["li"] = 0.5;$MGrande["t"] = 0.75;$MGrande["ls"] = 1;
//$Grande[] = array();
//$Grande["li"] = 0.75;$Grande["t"] = 1;$Grande["ls"] = 1;
//$DefaultFuz[] = array();
//$DefaultFuz["li"] = 0;$DefaultFuz["t"] = 1;$DefaultFuz["ls"] = 1;

$varRow = array();
$valRow = array();
$pesoRow = array();
	
foreach ($_POST as $key => $value)
{
    if (strpos($key, 'var') !== false)
    { 
		$detectedIndex = substr($key, -1);
		$varRow[] = $value; //set var value
		
		if ( !empty($_POST["val".$detectedIndex]) ) {
		$valRow[$value] = $_POST["val".$detectedIndex];
		}
		else
		{
			$valRow[$value] = "default";
		}
		
		if ( !empty($_POST["peso".$detectedIndex]) || (isset($_POST["peso".$detectedIndex]) && $_POST["peso".$detectedIndex] === "0")) {
			$pesoRow[$value] = $_POST["peso".$detectedIndex];
		}
		else
		{
			$pesoRow[$value] = 1;
		}
	}
}

$totalPeso = array_sum($pesoRow);
foreach ($pesoRow as $key => $value){
	$pesoRow[$key] /= $totalPeso;
}



//get max and min values to normalize amp
$maxvar = [];
$minvar = [];
foreach ($varRow as $key => $value)
{
	
		$sql = "SELECT MAX(".$value.") as FinalValue FROM municipios left join relatoriomunicipal2017 on relatoriomunicipal2017.nome_municipio = municipios.municipio";
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		$maxvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];

		
		$sql = "SELECT MIN(".$value.") as FinalValue FROM municipios left join relatoriomunicipal2017 on relatoriomunicipal2017.nome_municipio = municipios.municipio";
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		$minvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];
		
}


$normalizedRows = [];

if (count($varRow) > 0)
{
	//$sql = "SELECT municipios.Codigo as CodigoIBGE,".implode(',',$varRow)." FROM municipios left join relatoriomunicipal2017 on relatoriomunicipal2017.nome_municipio = municipios.municipio";
	$sql = "SELECT municipios.Codigo as CodigoIBGE,".implode(',',$varRow)." FROM municipios left join relatoriomunicipal2017 on relatoriomunicipal2017.nome_municipio = municipios.municipio";
}
else
{
	$sql = "SELECT municipios.Codigo as CodigoIBGE FROM municipios left join relatoriomunicipal2017 on relatoriomunicipal2017.nome_municipio = municipios.municipio";
}

	$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
$rows = [];
while($row = mysqli_fetch_assoc($result))
{
	$row["FinalValue"] = 0;

	foreach($row as $key => $item) {
		
		if ($key != "CodigoIBGE" && $key != "FinalValue")
		{
		$normalizedRows[$key] = ( $row[$key] - $minvar[$key])/($maxvar[$key]-$minvar[$key]);		
			if ($row["CodigoIBGE"] == 3304557) {
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["index"];
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
				} //debugging
		$row["FinalValue"] +=  Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"] * $pesoRow[$key];
		}
	}
	
	    $rows[$row["CodigoIBGE"]] = $row;
}//TODO

function Fuzzification($value,$target)
{
$targetindex;
	switch ($target) {
    case "Baixo":
        $targetindex=0;
        break;
    case "Médio Baixo":
        $targetindex=1;
        break;
    case "Médio":
        $targetindex=2;
        break;
    case "Médio Alto":
        $targetindex=3;
        break;
    case "Alto":
        $targetindex=4;
        break;
	case "default":
		$targetindex=5;
     break;
		
	}



	
global $Fuzzy;

	if($Fuzzy[$targetindex]["li"] > $value || $Fuzzy[$targetindex]["ls"] < $value)
		$retorno = 0;
	else if($Fuzzy[$targetindex]["li"] == $Fuzzy[$targetindex]["t"]) //1o. conjunto
		$retorno =  ($Fuzzy[$targetindex]["ls"] - $value)/($Fuzzy[$targetindex]["ls"]-$Fuzzy[$targetindex]["li"]);
	else if($Fuzzy[$targetindex]["ls"] == $Fuzzy[$targetindex]["t"]) // ultimo conjunto
		$retorno =  ($value - $Fuzzy[$targetindex]["li"])/($Fuzzy[$targetindex]["ls"]-$Fuzzy[$targetindex]["li"]);
	else if($Fuzzy[$targetindex]["li"] <= $value && $value <= $Fuzzy[$targetindex]["t"])
	{
		$retorno =  ($value - $Fuzzy[$targetindex]["li"])/($Fuzzy[$targetindex]["t"]-$Fuzzy[$targetindex]["li"]);
	}
	else if ( $Fuzzy[$targetindex]["t"] <= $value && $value <= $Fuzzy[$targetindex]["ls"])
		{
			$retorno =  ($Fuzzy[$targetindex]["ls"] - $value )/($Fuzzy[$targetindex]["ls"]-$Fuzzy[$targetindex]["t"]);
		}

return array("retorno" => $retorno, "valor" => $value,"index" => $targetindex,"t"=> $Fuzzy[$targetindex]["t"],"li" => $Fuzzy[$targetindex]["li"], "ls" => $Fuzzy[$targetindex]["ls"]);

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