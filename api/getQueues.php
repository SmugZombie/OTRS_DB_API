<?php

if(isset($JSON_IN['queue'])){$queue = $JSON_IN['queue'];} else{ $queue = ""; }

if(!$queue){
	$queueCount = 0;
//	$queues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `queue`"));
	$result = mysqli_query($conn, "SELECT `id`,`name`,`group_id`,`comments`,`valid_id` FROM `queue`");
	$queues = array();

	while( $row = mysqli_fetch_assoc($result) ){
		$queues[$queueCount] = $row;
		$queueCount ++;
	}	
}


$response['code'] = 1;
$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
$response['debug']['json'] = $JSON_IN;
$response['data']['queues'] = $queues;

