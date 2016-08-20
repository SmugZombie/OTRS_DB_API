<?php
// This script is expecting to be hosted on the OTRS server, however the database is not required to be on the same box.

// Define Database Credentials
$db_user = "otrs";
$db_pwd = "";
$db_name = "otrs";
$db_host = "";
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $db_name);

// SOAP needs to be enabled on OTRS for this to function
// Define OTRS RPC Credentials
$url = 'http://127.0.0.1/otrs/rpc.pl';
$username = "";  // SOAP username set in sysconfig
$password = "";  // SOAP password set in sysconfig

