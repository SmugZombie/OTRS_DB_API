<?php
// Get data from JSON
if(isset($JSON_IN['ticket_number'])){$ticket_number = $JSON_IN['ticket_number'];} else{ $ticket_number = ""; }
if(isset($JSON_IN['ticket_id'])){ $ticket_id = $JSON_IN['ticket_id']; } else{ $ticket_id = ""; }

// Static values for dev
$limit = 100;
$page = 1;
$start = 0;

// Create empty arrays to be used later
$articles = array();
$article_types = getArticleTypes();

// If no ticket_number or ticket_id presented, error
if(!$ticket_number && !$ticket_id){ return_error("Invalid Ticket_Id provided!"); return; }

// Validate that the Ticket Number Translates Properly to the Ticket Id
if($ticket_number && $ticket_id){ 
	$temp_ticket_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `id` FROM `ticket` WHERE `tn` = $ticket_number "))['id']; 
	if($ticket_id != $temp_ticket_id){ return_error("Ticket ID and Ticket Number Don't Match", 1); return; }
}
// If the ticket id is not provided or found, error
else{ return_error("Invalid Information Provided",1); return; }

// Grab Article Types
function getArticleTypes(){
        global $conn;
        $article_types = array();
        $result = mysqli_query($conn,"SELECT `id`,`name` FROM `article_type`");
        while( $row = mysqli_fetch_assoc($result) ){ $id = $row['id']; $article_types[$id] = $row['name']; }
        return $article_types;
}

// Define changable parts of the sql statement as arrays
$sql_where = array();
$sql_limit = array("LIMIT ".$start.",".$limit);
$sql_sort = array("ORDER BY `change_time`", "DESC");

// Define where clause
$sql_where[count($sql_where)] = "`ticket_id` = '$ticket_id'";

//$sql_where[count($sql_where)] = "`change_time` >= DATE_SUB(CURDATE(), INTERVAL ".$oldest." DAY)";

// Turn the arrays back into usable SQL
$sql_where = implode(" AND ", $sql_where);
$sql_limit = implode(" ", $sql_limit);
$sql_sort = implode(" ", $sql_sort);

// Build SQL Statements
$sql = "SELECT * FROM `article` WHERE " . $sql_where . " " . $sql_sort ." " . $sql_limit;
$count_sql = "SELECT count(*) as `count` FROM `article` WHERE " . $sql_where;

// Run SQL Statements
$result = mysqli_query($conn, $sql);
$total = mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['count'];
while( $row = mysqli_fetch_assoc($result) ){ $article_count = count($articles); $articles[$article_count] = $row; $articles[$article_count]['type'] = $article_types[$row['article_type_id']]; $articles[$article_count]['a_body'] = htmlEntities($row['a_body']); }

// Return JSON Response
$response['code'] = 1;
$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
$response['debug']['json'] = $JSON_IN;
$response['debug']['article_types'] = $article_types;
$response['data']['articles'] = $articles;
$response['search_params']['sql'] = $sql;
$response['search_params']['count_sql'] = $count_sql;
$response['search_params']['page'] = $page;
$response['search_params']['start'] = $start;
$response['search_params']['ticket_id'] = $ticket_id;
$response['search_results']['count'] = count($articles);
$response['search_results']['total'] = $total;
$response['search_results']['pages'] = ceil($total / $limit);

