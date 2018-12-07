<?php
session_start();
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > ini_get("session.gc_maxlifetime")) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}



$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv'); //accepted formats
$target_dir = "uploads/".session_id()."/";
if(!is_dir($target_dir)) mkdir($target_dir);
$fileName = basename(str_replace(" ", "_",$_FILES["fileToUpload"]["name"]));
$target_file = $target_dir . $fileName;
$uploadOk = 1;
//$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
   // $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
   //str_replace(" ", "_", $fileName);
    if(in_array($_FILES['fileToUpload']['type'],$mimes)) {
        echo "File is csv - " ;//. $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an csv.";
        $uploadOk = 1;
    }
}

echo '</br>'. $target_file;


if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.  Code:#". $_FILES["fileToUpload"]["error"];
    }
}

// Here file if already transfered

$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');
$table = strtok($_FILES["fileToUpload"]["name"], ".");

// get structure from csv and insert db
ini_set('auto_detect_line_endings',TRUE);
$handle = fopen($target_file,'r');
// first row, structure
if ( ($data = fgetcsv($handle,0,";") ) === FALSE ) {
    echo "Cannot read from csv $target_file";die();
}
$fields = array();
$field_count = 0;
$havecode = false;
for($i=0;$i<count($data); $i++) {
    $f = strtolower(trim($data[$i]));
    if ($f) {
        // normalize the field name, strip to 20 chars if too long
        $f = substr(preg_replace ('/[^0-9a-z]/', '_', $f), 0, 30);
        $field_count++;
		if ($f != 'nome')
		{
				$fields[] = $f.' Int(20)';
				echo $f.'  ';
				if ($f == 'codigoibge')
				{
					$havecode = true;
				}
		}
		else
		{
			$fields[] = $f.' Varchar(40)';
		}
    }
}

if($havecode == false)
{
	echo 'Não foi encontrado Código indexador na tabela tabela enviada, para mais informações leia o guia de referência';
	exit;
}
else echo 'Codigo Indexador encontrado'.'</br>';

echo $table;
if ($result = $mysqli->query("SHOW TABLES LIKE '".session_id().$table."'")) 
{
	if($result->num_rows != 0) 
	{
		
		$result = $mysqli->query("DROP TABLE ".session_id().$table);
		$result = $mysqli->query("delete from tablecontrol where SessionId = '".session_id()."' and TableName = '".$table."'");	
	}
			$sql = "CREATE TABLE ".session_id().$table ." (" . implode(', ', $fields) . ")";
			echo $sql . "<br /><br />";
			// $db->query($sql);
			$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
			while ( ($data = fgetcsv($handle,0,";") ) !== FALSE ) 
			{
				$fields = array();
				for($i=0;$i<$field_count; $i++) {
					$fields[] = '\''.addslashes($data[$i]).'\'';
				}
				$sql = "Insert into ".session_id()."$table values(" . implode(', ', $fields) . ')';
				// $db->query($sql);
				$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
			}
			
			$sql = "insert into tablecontrol(SessionId,TableName,CreateTime,TableType) values('".session_id()."','".$table."','".date('Y-m-d H:i:s',$_SESSION['CREATED'])."','".$_POST["options"]."')";
			echo $sql; 
			$tcresult = $mysqli->query($sql) or die(mysqli_error($mysqli));
	

}

	

fclose($handle);
ini_set('auto_detect_line_endings',FALSE);




ClearExpiredTables();


function ClearExpiredTables()
{
	$mysqli = new mysqli('127.0.0.1', 'root', '', 'geoinfo');
	$sql = "select * from tablecontrol where CreateTime <= '".date('Y-m-d H:i:s',time()-ini_get("session.gc_maxlifetime"))."'";
		echo $sql."<br/>"; 
	$tablesToBeDeleted = $mysqli->query($sql) or die(mysqli_error($mysqli));

	foreach($tablesToBeDeleted as $key => $value)
	{
		$sql = "DROP TABLE ".$value["SessionId"].$value["TableName"];
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
		$sql = "delete from tablecontrol where Codigo = ".$value["Codigo"];
		echo $sql."<br/>"; 
		$result = $mysqli->query($sql) or die(mysqli_error($mysqli));
	}
}


header('location:UserMenu.php');


// UPLOAD_ERR_INI_SIZE = Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.

// UPLOAD_ERR_FORM_SIZE = Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.

// UPLOAD_ERR_PARTIAL = Value: 3; The uploaded file was only partially uploaded.

// UPLOAD_ERR_NO_FILE = Value: 4; No file was uploaded.

// UPLOAD_ERR_NO_TMP_DIR = Value: 6; Missing a temporary folder. Introduced in PHP 5.0.3.

// UPLOAD_ERR_CANT_WRITE = Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.

// UPLOAD_ERR_EXTENSION = Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.
?>