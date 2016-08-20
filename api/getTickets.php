<?php
// Get JSON Input
if(isset($JSON_IN['queue'])){$queue = $JSON_IN['queue'];} else{ $queue = ""; }
if(isset($JSON_IN['ticket_number'])){$ticket_number = $JSON_IN['ticket_number'];} else{ $ticket_number = ""; }
if(isset($JSON_IN['ticket_id'])){ $ticket_id = $JSON_IN['ticket_id']; } else{ $ticket_id = ""; }
if(isset($JSON_IN['oldest'])){ $oldest = $JSON_IN['oldest']; }else{ $oldest = 90; }
if(isset($JSON_IN['customer_user_id'])){ $customer_user_id = $JSON_IN['customer_user_id']; }else{ $customer_user_id = 0; }

// Static vars for dev
$limit = 100;
$page = 1;
$start = 0;

// Build empty array to be used later
$tickets = array();

// Get Ticket States and add to array
$ticket_states = getTicketStates();

function getTicketStates(){
	global $conn;
	$ticket_states = array();
	$result = mysqli_query($conn,"SELECT `id`,`name` FROM `ticket_state`");
	while( $row = mysqli_fetch_assoc($result) ){ $id = $row['id']; $ticket_states[$id] = $row['name']; }
	return $ticket_states;
}

// Define changable parts of the sql statement as arrays
$sql_where = array();
$sql_limit = array("LIMIT ".$start.",".$limit);
$sql_sort = array("ORDER BY `id`", "DESC");

// If queue is defined, add it to the where clause, also fetch the queue information
if($queue){ 
	$sql_where[count($sql_where)] = "`queue_id` = '".$queue."'";
	$queue_details = mysqli_fetch_assoc( mysqli_query($conn, "SELECT `id`,`name`,`group_id`,`comments`,`valid_id` FROM `queue` WHERE `id` = $queue"));
	$response['data']['queue'] = $queue_details;
}

// If customer_user_id define add it to the where clause as well
if($customer_user_id){ $sql_where[count($sql_where)] = "`customer_user_id` = '$customer_user_id'"; }

// If ticket number id defined, add it to the where clause
if($ticket_number){ $sql_where[count($sql_where)] = "`tn` = $ticket_number"; }
// Else if ticket_id is defined instead, add it to the where clause instead
elseif($ticket_id){ $sql_where[count($sql_where)] = "`id` = $ticket_id"; }
// Else disregard both and search by timestamp
else{ $sql_where[count($sql_where)] = "`change_time` >= DATE_SUB(CURDATE(), INTERVAL ".$oldest." DAY)"; }

// Turn the arrays back into usable SQL
$sql_where = implode(" AND ", $sql_where);
$sql_limit = implode(" ", $sql_limit);
$sql_sort = implode(" ", $sql_sort);

// Build SQL Statements
$sql = "SELECT * FROM `ticket` WHERE " . $sql_where . " " . $sql_sort ." " . $sql_limit;
$count_sql = "SELECT count(*) as `count` FROM `ticket` WHERE " . $sql_where;

// Run SQL Statements
$result = mysqli_query($conn, $sql);
$total = mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['count'];
while( $row = mysqli_fetch_assoc($result) ){ $ticket_count = count($tickets); $tickets[$ticket_count] = $row; $tickets[$ticket_count]['status'] = $ticket_states[$row['ticket_state_id']]; }

// Return JSON Response
$response['code'] = 1;
$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
$response['debug']['json'] = $JSON_IN;
$response['debug']['ticket_states'] = $ticket_states;
$response['data']['tickets'] = $tickets;
$response['search_params']['sql'] = $sql;
$response['search_params']['count_sql'] = $count_sql;
$response['search_params']['page'] = $page;
$response['search_params']['start'] = $start;
$response['search_results']['count'] = count($tickets);
$response['search_results']['total'] = $total;
$response['search_results']['pages'] = ceil($total / $limit);
