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



$varRow = array();
$valRow = array();
$pesoRow = array();
$tableRow = array();


//Passando valores da requsição post para um array
foreach ($_POST as $key => $value)
{
    if (strpos($key, 'var') !== false)
    { 
		$detectedIndex = substr($key, -1);
		$sql = "SELECT Atributo,TabelaRef FROM atributos where Codigo = ".$value;;
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		
		$SQLrow = mysqli_fetch_assoc($result);
		$varRow[] = $SQLrow["Atributo"]; //set var value

		$tableRow[$SQLrow["Atributo"]] = $SQLrow["TabelaRef"];
		

		
		if ( !empty($_POST["val".$detectedIndex]) ) {
		$valRow[$SQLrow["Atributo"]] = $_POST["val".$detectedIndex];
		}
		else
		{
			$valRow[$SQLrow["Atributo"]] = "default";
		}
		
		if ( !empty($_POST["peso".$detectedIndex]) || (isset($_POST["peso".$detectedIndex]) && $_POST["peso".$detectedIndex] === "0")) {
			$pesoRow[$SQLrow["Atributo"]] = $_POST["peso".$detectedIndex];
		}
		else
		{
			$pesoRow[$SQLrow["Atributo"]] = 1;
		}
	}
}

$totalPeso = array_sum($pesoRow);
foreach ($pesoRow as $key => $value){
	$pesoRow[$key] /= $totalPeso;
}



//get max and min values para normalizar por amplitude
$maxvar = [];
$minvar = [];


foreach ($varRow as $key => $value)
{
		$sql = "SELECT MAX(".$value.") as FinalValue FROM ".$tableRow[$value];
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		$maxvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];

		
		$sql = "SELECT MIN(".$value.") as FinalValue FROM ".$tableRow[$value];
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		$minvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];
		
}


$normalizedRows = [];


///////////// TODO CodigoIBGE para municipios e COD_UF para estados e xx para regiões
if (count($varRow) > 0)
{
	$sql = "SELECT CodigoIBGE,".implode(',',$varRow)." FROM ".$tableRow[$varRow[0]];
	$alreadyUsedTables[] = $tableRow[$varRow[0]];
	foreach($tableRow as $key => $value)
	{
		if (!in_array($value, $alreadyUsedTables))
		{
			$sql += " join ".$value." on ".$tableRow[$varRow[0]].".CodigoIBGE = ".$value.".CodigoIBGE";
			$alreadyUsedTables[] = $value;
		}
	}
}
else
{
	$sql = "SELECT CodigoIBGE FROM municipal2017";
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
			//if ($row["CodigoIBGE"] == 3304557) {
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["index"];
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
			//	} //debugging
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