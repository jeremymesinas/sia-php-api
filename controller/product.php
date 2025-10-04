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

if ($_SERVER['REQUEST_METHOD']==='GET') {
    //query db

    try {
        $query = $readDB->prepare('SELECT product_id, product_name, description, DATE_FORMAT(delivered_date, "%d/%m/%Y %H:%i") as delivered_date from tblproducts where product_id = :productid');
        $query->bindParam(':productid', $productid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();
        // create product array to store returned product
        $productArray = array();

        if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Product not found");
        $response->send();
        exit;
        }

        // for each row returned
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new product object for each row
            $product = new Product($row['product_id'], $row['product_name'], $row['description'], $row['delivered_date']);

            // create product and store in array for return in json data
            $productArray[] = $product->returnProductAsArray();
        }

        // bundle products and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['products'] = $productArray;

        // set up response for successful return
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
    }

    // if error with sql query return a json error
    catch(ProductException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit;
    }
    catch(PDOException $ex) {
        error_log("Database Query Error: ".$ex, 0);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to get product");
        $response->send();
        exit;
    }
}

// else if request if a DELETE e.g. delete product
elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {
        // create db query
        $query = $writeDB->prepare('delete from tblproducts where product_id = :productid');
        $query->bindParam(':productid', $productid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Product not found");
            $response->send();
            exit;
        }
    }

    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->addMessage("Product deleted");
    $response->send();
    exit;
    

    // if error with sql query return a json error
    catch(PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to delete product");
        $response->send();
        exit;
    }
}

?>
