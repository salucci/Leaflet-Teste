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
		
		
		//verificando se o Código recebido começa com 0
		//pega a variavel
		$parts = explode('&', $value);
		if (!isset($parts[1]))
		{
			$sql = "SELECT Atributo,TabelaRef FROM atributos where Codigo = ".$value;
			$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		
			$SQLrow = mysqli_fetch_assoc($result);
			$varRow[] = $SQLrow["Atributo"]; //lista das variaveis escolhidas
			$tableRow[$SQLrow["Atributo"]] = $SQLrow["TabelaRef"]; //Lista das tabelas utilizadas com indice = váriavel que será utilizada
		}
		else
		{
			$varRow[] = $parts[0];
			$sql = "Select SessionId,TableName from tablecontrol where Codigo = ".$parts[1];
			$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
			$SQLrow = mysqli_fetch_assoc($result);
			$tableRow[$parts[0]] = $SQLrow["SessionId"].$SQLrow["TableName"];
		}


		
		

		//pega o fuzzy
		if ( !empty($_POST["val".$detectedIndex]) ) {
		$valRow[end($varRow)] = $_POST["val".$detectedIndex];
		}
		else
		{
			$valRow[end($varRow)] = "default";
		}
		
		//pega o peso
		if ( !empty($_POST["peso".$detectedIndex]) || (isset($_POST["peso".$detectedIndex]) && $_POST["peso".$detectedIndex] === "0")) {
			$pesoRow[end($varRow)] = $_POST["peso".$detectedIndex];
		}
		else
		{
			$pesoRow[end($varRow)] = 1;
		}
	}
	
	
	
}

$totalPeso = array_sum($pesoRow);
foreach ($pesoRow as $key => $value){
	$pesoRow[$key] /= $totalPeso;
}





$normalizedRows = [];


function OrderTables($conn,&$tableArray)
{
	
	$tableGoingToUse = [];
	foreach ($tableArray as $key => $value)
	{
		if (!in_array($value, $tableGoingToUse))//monta a lista de todas as tabelas que irei utilizar na query
		{
			$queryBiggerTable = "select count(*) as count from ".$value;
			$result = $conn->query($queryBiggerTable) or die(mysqli_error($conn));	
			$tableGoingToUse[mysqli_fetch_assoc($result)["count"]] = $value;	
		}
	}
	
	
	ksort($tableGoingToUse);
	
	$SortedTables = [];
	foreach ($tableGoingToUse as $xkey => $xvalue)
	{
		foreach ($tableArray as $ykey => $yvalue)
		{
			if ($xvalue == $yvalue)
			{
				$SortedTables[$ykey] = $yvalue;
			}
		}
	}
	

	return $SortedTables;
}


function GenerateQuery(&$tableArray)
{			
	$sql = "SELECT ". reset($tableArray).".CodigoIBGE";
	
	$tableGoingToUse = [];
	foreach($tableArray as $key => $value)
	{
		$sql = $sql.",".$value.".".$key;  //monta query => tabela.atributo
		if (!in_array($value, $tableGoingToUse))//monta a lista de todas as tabelas que irei utilizar na query
		{
			$tableGoingToUse[] = $value;
		}
	}
	
	if (count($tableGoingToUse) > 0)
	{	
		$sql = $sql." from ".$tableGoingToUse[0];
		$MajorTable = $tableGoingToUse[0];
		unset($tableGoingToUse[0]);
		foreach ($tableGoingToUse as $key => $value)
		{
			$sql = $sql." LEFT JOIN ".$tableGoingToUse[$key]." ON ".$MajorTable.".CodigoIBGE = ".$tableGoingToUse[$key].".CodigoIBGE";
		}
	}
	return $sql;
}

///////////// TODO CodigoIBGE para municipios e COD_UF para estados e xx para regiões
if (count($varRow) > 0)
{
	// foreach($tableRow as $key => $result ) {
    // echo $key." ".  $result."<br/>";
	// }
	
	// foreach($tableRow as $key => $result ) {
    // echo $key." ".  $result."<br/>";
	// }
	
	$OrderedTables = OrderTables($mysqli, $tableRow);
	$sql = GenerateQuery($OrderedTables);
	
}
else
{
	$sql = "SELECT CodigoIBGE FROM municipal2017";
}

//get max and min values para normalizar por amplitude
$maxvar = [];
$minvar = [];
foreach ($varRow as $key => $value)
{
		$sqlmax = "SELECT MAX(".$value.") as FinalValue FROM (".$sql.") customTable";//pegar a custom query grande
		$result = $mysqli->query($sqlmax) or die(mysqli_error($mysqli));
		$maxvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];
		//echo $sqlmax;
		
		$sqlmin = "SELECT MIN(".$value.") as FinalValue FROM (".$sql.") customTable";
		$result = $mysqli->query($sqlmin) or die(mysqli_error($mysqli));
		$minvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];
		
		//$sqlmin = "SELECT MIN(".$value.") as FinalValue FROM ".$tableRow[$value];
		//$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		//$minvar[$value] = mysqli_fetch_assoc($result)["FinalValue"];
}

$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
$rows = [];
while($row = mysqli_fetch_assoc($result))
{
	$row["FinalValue"] = 0;
	$log = $row["CodigoIBGE"]." ";
	foreach($row as $key => $item) 
	{	
		if ($key != "CodigoIBGE" && $key != "FinalValue")
		{
		$Amplitudade = $maxvar[$key]-$minvar[$key];
		if ($Amplitudade == 0) $Amplitudade = 1;
		$normalizedRows[$key] = ( $row[$key] - $minvar[$key])/$Amplitudade;
		$log = $log." Amplitude:(".$maxvar[$key]."-".$minvar[$key].")=".$Amplitudade;
			//if ($row["CodigoIBGE"] == 3304557) {
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["index"];
				//echo Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
			//	} //debugging
			//echo "Valor normalizado do ".$key.": ".$normalizedRows[$key]." Peso: ".$pesoRow[$key]." Fuzzyficação: ".Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"];
			$log = $log." V".$key." :".$normalizedRows[$key]." F".$key." :".Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"]." P".$key." :".$pesoRow[$key];
		$row["FinalValue"] +=  Fuzzification($normalizedRows[$key],$valRow[$key])["retorno"] * $pesoRow[$key];
		}
		
			
	}
	//echo $log. " Final: ".$row["FinalValue"];
	
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

///echo json_encode($rows);



?>