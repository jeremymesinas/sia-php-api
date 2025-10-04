<?php
require_once('db.php');
require_once('../model/Product.php');
require_once('../model/Response.php');

//set up connections to read & write db connections
try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
}
catch (PDOException $ex) {
    error_log("Connection error" . $ex. 0)
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit();
}

// within this if/elseif statement, it is important to get the correct order (if query string GET param is used in
// multiple routes)
// check if productid is in the url e.g. /products/1

// check if id is in the url e.g. /products/1
if (array_key_exists("productid", $_GET)) {
    // get product id from query string
    $productid = $_GET['productid'];

    //check to see if product id in query string is not empty and is number, if not return json error
    if($productid == "" || !is_numeric($productid)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Product ID cannot be blank or must be numeric");
        $response->send();
        exit;
    }
}

if ($_SERVER)


?>