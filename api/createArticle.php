<?php
// Get Input from JSON
$ticket_id = validate_data($JSON_IN['ticket_id'],'ticket_id','int',10,2,true);
$from = validate_data($JSON_IN['from'],'from','email',64,0,true);
$to = validate_data($JSON_IN['to'],'to','email',64,0,true);
$body = mysqli_real_escape_string($conn, htmlEntities($JSON_IN['body']));

// Doublecheck Body Has Contents
if(!$body){ return_error("Invalid Body Provided", 1); return; }

// Get the ticket number and title from the database
$ticket_number =
$title = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `title` FROM `ticket` WHERE `id` = $ticket_id LIMIT 1"))['title'];

// Place the HTML characters back in the boxy
$body = html_entity_decode($body);

// Start new SOAP client
$client = new SoapClient(null,array( 'location'  => $url,'uri'       => "Core",'trace'     => 1,'login'     => $username,'password'  => $password,'style'     => SOAP_RPC,'use'       => SOAP_ENCODED));

// Create the article, return the Article ID
$ArticleID = $client->__soapCall("Dispatch",
        array($username, $password,
                "TicketObject",   "ArticleCreate",
                "TicketID",       $ticket_id,
                "ArticleType",    "webrequest",
                "SenderType",     "customer",
                "HistoryType",    "AddNote",
                "HistoryComment", "API Response",
                "From",           $from,
                "Subject",        "[Ticket#".$ticket_number."] ".$title,
                "ContentType",    "text/html; charset=UTF-8",
                "Body",           $body,
                "UserID",         1,
                "Loop",           0,
                "AutoResponseType", 'auto reply',
                "OrigHeader", array(
                        'From' => $from,
                        'To' => $customer_email,
                        'Subject' => $title,
                        'Body' => $body
                ),
        )
);

// If the ArticleID comes back, we know the article created, now update the ticket to open in database
if($ArticleID){ mysqli_query($conn, "UPDATE `ticket` SET `ticket_state_id` = 4 WHERE `id` = $ticket_id; "); }

// Return JSON Response
$response['code'] = 1;
$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
$response['debug']['json'] = $ArticleID;

