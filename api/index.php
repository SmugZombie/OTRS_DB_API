<?php
// This is part of an OTRS database API created by Ron Egli (github.com/smugzombie)
$time = -microtime(true);
require('config.php');
header("Access-Control-Allow-Origin: *");
$JSON_IN = json_decode(file_get_contents('php://input'), true);

// Get rid of index errors
$name = ""; $street1 = ""; $street2 = ""; $city = ""; $state = ""; $zip = ""; $type = ""; $user_id = ""; $parent_entity = ""; $device_id = "";

// --- Step 1: Initialize variables and functions

/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/
function deliver_response($format, $api_response){
        global $time;
        $time += microtime(true); $time = round($time, 2);
        $api_response['data']['runtime'] = $time." Seconds";
        // Define HTTP responses
        $http_response_code = array(
                200 => 'OK',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found'
        );

        // Set HTTP Response
        header('HTTP/1.1 '.$api_response['status'].' '.$http_response_code[ $api_response['status'] ]);

        // Process different content types
        if( strcasecmp($format,'json') == 0 ){

                // Set HTTP Response Content Type
                header('Content-Type: application/json; charset=utf-8');

                // Format data into a JSON response
                $json_response = json_encode($api_response);

                // Deliver formatted data
                echo $json_response;

        }elseif( strcasecmp($format,'xml') == 0 ){

                // Set HTTP Response Content Type
                header('Content-Type: application/xml; charset=utf-8');

                // Format data into an XML response (This is only good at handling string data, not arrays)
                $xml_response = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                        '<response>'."\n".
                        "\t".'<code>'.$api_response['code'].'</code>'."\n".
                        "\t".'<data>'.$api_response['data'].'</data>'."\n".
                        '</response>';

                // Deliver formatted data
                echo $xml_response;

        }else{

                // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
                header('Content-Type: text/html; charset=utf-8');

                // Deliver formatted data
                echo $api_response['data'];

        }

        // End script process
        exit;

}

// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;

// Define whether user authentication is required
$authentication_required = FALSE;

// Define API response codes and their related HTTP response
$api_response_code = array(
        0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
        1 => array('HTTP Response' => 200, 'Message' => 'Success'),
        2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
        3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
        4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
        5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
        6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format'),
        7 => array('HTTP Response' => 500, 'Message' => 'Server Error')
);

// Set default HTTP response of 'ok'
$response['code'] = 0;
$response['status'] = 404;
$response['data'] = NULL;

// --- Step 2: Authorization

// Optionally require connections to be made via HTTPS
if( $HTTPS_required && $_SERVER['HTTPS'] != 'on' ){
        $response['code'] = 2;
        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        $response['data'] = $api_response_code[ $response['code'] ]['Message'];

        // Return Response to browser. This will exit the script.
        deliver_response($_GET['format'], $response);
}

// Optionally require user authentication
if( $authentication_required ){

        if( empty($_POST['username']) || empty($_POST['password']) ){
                $response['code'] = 3;
                $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
                $response['data'] = $api_response_code[ $response['code'] ]['Message'];

                // Return Response to browser
                deliver_response($_GET['format'], $response);

        }

        // Return an error response if user fails authentication. This is a very simplistic example
        // that should be modified for security in a production environment
        elseif( $_POST['username'] != 'foo' && $_POST['password'] != 'bar' ){
                $response['code'] = 4;
                $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
                $response['data'] = $api_response_code[ $response['code'] ]['Message'];

                // Return Response to browser
                deliver_response($_GET['format'], $response);
        }
}

// --- Step 3: Process Request

if( strcasecmp($_GET['method'],'test') == 0){ include('test.php'); }
elseif( strcasecmp($_GET['method'],'getQueues') == 0){ include('getQueues.php'); }
elseif( strcasecmp($_GET['method'],'getTickets') == 0){ include('getTickets.php'); }
elseif( strcasecmp($_GET['method'],'getArticles') == 0){ include('getArticles.php'); }
elseif( strcasecmp($_GET['method'],'createArticle') == 0){ include('createArticle.php'); }
elseif( strcasecmp($_GET['method'],'listTables') == 0){ include('listTables.php'); }
else{ $response['data'] = "The API call requested (".$_GET['method'].") is unknown."; }

// --- Step 4: Deliver Response

// Return Response to browser
deliver_response($_GET['format'], $response);

function return_error($message, $code){
        global $api_response_code;
        if($code == ""){ $code = 6; }
        $response['code'] = $code;
        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        $response['data']['message'] = "$message";
        $response['message'] = "$message";
        deliver_response($_GET['format'], $response);
}
function get_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    $output = substr($string, $ini, $len);
    if(!$output){$output = "null";}
    return $output;
}

function validate_data($in, $name, $type = 'var', $max = 64, $min = 0, $required = false){
	global $response; $error = 0;

	if($required && $in == ""){ return_error("Input Required for [$name]. {type:$type,min:$min,max:$max}",6); exit; }

	if($type == "" || $in == ""){ $error ++; }
	if($type == "int"){
		if($max == ""){$max = 8;}if($min == ""){$min = 0;}
		if(strlen($in) < $min || strlen($in) > $max){ $error ++; }
		if(is_int($in) || $in == "0"){ return $in; }
		else{
			if(is_int((int)$in)){ return $in; }
			else{ $error ++; }
		}
	}
	if($type == "var"){
		if($min == ""){$min = 0;} // Set Min Length if None
		if(strlen($in) <= ($min -1) || strlen($in) > $max){ $error ++; } // Check if beyond max or min char counts
		else{ return htmlentities($in); } // Output htmlentity encoded once complete
	}
	if($type == "zip"){
		if($max == ""){$max = 5;} if($min == ""){$min = 5;}
		return validate_data($in, 'int', 5, 5);
	}
	if($type == "email"){
		if($min == ""){$min = 8;} // Set Min Length if None
		$pos_at = strpos($in, "@");
		$pos_dot = strpos($in, ".");
		if($pos_dot === false || $pos_at === false){ $error ++; }
		if(strlen($in) < $min || strlen($in) > $max){ $error ++; } // Check if beyond max or min char counts
		else{ return htmlentities($in); } // Output htmlentity encoded once complete
	}
	if($type == "password"){

	}

	if($error){
		return_error("Invalid data provided for [$name]. {type:$type,min:$min,max:$max}",6); exit;
	}
}


?>

