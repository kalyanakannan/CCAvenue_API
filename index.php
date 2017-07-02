<?php
include("api.php");
$ccavenue_api = new CCAvenue_API();

//Get pendingorders list
$pendingorders = array('page_number' => 1);

$response=$ccavenue_api->getPendingOrders($pendingorders);

print_r($response);

?>
