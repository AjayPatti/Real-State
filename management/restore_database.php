<?php 

if(isset($_POST['import'])){
	$query = '';
	$sqlScript = explode(".",$_FILES["kc_db"]["name"]);
	$extention = end($sqlScript);
	// $except_tables = array("kc_login");
	$mime = mime_content_type($_FILES["kc_db"]["tmp_name"]);
	if($extention == 'sql' && $mime == 'text/x-Algol68'){
		$conn = new mysqli('127.0.0.1', 'root', '' , 'wcc_real_estate',3308);
		$file_data = file($_FILES['kc_db']['tmp_name']);
		mysqli_query($conn,'DROP TABLE `kc_login`');
		foreach ($file_data as $line){
			// if(!in_array($line,$except_tables)){
				$startWith = substr(trim($line), 0 ,2);
				$endWith = substr(trim($line), -1 ,1);
				if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
					continue;
				}
				$query = $query . $line;
				if ($endWith == ';') {
					mysqli_query($conn,$query) or die('<div class="error-response sql-import-response">Problem in executing the SQL query <b>' . $query. '</b></div>');
					$query= '';	
				}
			// }
		} 
		echo '<div class="success-response sql-import-response">SQL file imported successfully</div>';
	 	echo "Tables imported successfully";
	 	header("Location:/wcc_real_estate/management/dashboard.php"); 
		exit;
	}else{
		echo "Invalid file";
		header("Location:/wcc_real_estate/management/dashboard.php");
		exit();
	}
}


