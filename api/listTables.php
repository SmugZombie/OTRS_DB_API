<?php
// This file is simply for looking at the database without requiring database software.

// Define basic SQL Statement
$sql = "SHOW TABLES";

// Get JSON Input
$table = $JSON_IN['table'];
$sample = $JSON_IN['sample'];

// If a table is not defined, list all tables
if(!$table){
	$tables = array();
	$results = mysqli_query($conn, $sql);
	while($row = mysqli_fetch_assoc($results)){
		$table = $row['Tables_in_otrs'];
        	$tables[count($tables)] = $table;
	}
$data = $tables;
}

// If a table is defined, but a sample is not requested, describe it
elseif(!$sample){
	$sql = "SHOW COLUMNS FROM $table";
	$results = mysqli_query($conn, $sql);
	while($row = mysqli_fetch_assoc($results)){ $tables[count($tables)] = $row; }
$data = $tables;
}
// If a table is defined and a sample is requested, give first 20 rows of it
else{
	$result = array();
	$sql = "SELECT * FROM `$table` LIMIT 20";
	$results = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($results)){ $result[count($result)] = $row; }
	$data = $result;
}

// Return JSON Response
$response['code'] = 1;
$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
$response['data'] = $data;
$response['debug']['json'] = $JSON_IN;

