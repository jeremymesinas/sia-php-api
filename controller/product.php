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
    error_log("Connection error" . $ex, 0);
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

        // set up response for successful return
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Product deleted");
        $response->send();
        exit;
    }

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

// handle updating product
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') { 
    // update product
   
    try {
        // check request's content type header is JSON 
        if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            // set up response for unsuccessful request 
            $response = new Response(); 
            $response->setHttpStatusCode(400); 
            $response->setSuccess(false);
            $response->addMessage("Content Type header not set to JSON");
            $response->send();
            exit;
        }
        
        // get PATCH request body as the PATCHed data will be JSON format 
        $rawPatchData = file_get_contents('php://input');
        if(!$jsonData = json_decode($rawPatchData)) { 
            // set up response for unsuccessful request 
            $response = new Response(); 
            $response->setHttpStatusCode(400); 
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit;
        }
        
        // set product field updated to false initially
        $productName_updated = false; 
        $description_updated = false; 
        $deliveredDate_updated = false;

        // create blank query fields string to append each field to
        $queryFields = "";

        //check if product name exists in PATCH
        if (isset($jsonData->productName)) {
            $productName_updated = true;
            $queryFields .= "product_name = :productName, ";
        }
        //check if description exists in PATCH
        if (isset($jsonData->description)) {
            $description_updated = true;
            $queryFields .= "description = :description, ";
        }
        //check if delivered date exists in PATCH
        if (isset($jsonData->deliveredDate)) {
            $deliveredDate_updated = true;
            $queryFields .= "delivered_date = STR_TO_DATE(:deliveredDate, '%d/%m/%Y %H:%i'), ";
        }

        //remove the right hand comma and trailing space
        $queryFields = rtrim($queryFields, ", ");

        //check if any product fields supplied in JSON
        if ($productName_updated === false && 
        $description_updated === false &&
        $deliveredDate_updated === false) {
            $response = new Response(); 
            $response->setHttpStatusCode(400); 
            $response->setSuccess(false);
            $response->addMessage("No product fields provided");
            $response->send();
            exit;
        }

        
        // create db query to get product from database to update - use master db
        $query = $writeDB->prepare('SELECT product_id, product_name, description,
        DATE_FORMAT(delivered_date, "%d/%m/%Y %H:%i") as delivered_date from tblproducts where
        product_id = :productid');
        $query->bindParam(':productid', $productid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();
        // make sure that the product exists for a given product id 
        
        if($rowCount === 0) {
            // set up response for unsuccessful return 
            $response = new Response();
            $response->setHttpStatusCode(404); 
            $response->setSuccess(false);
            $response->addMessage("No product found to update"); 
            $response->send();
            exit;
        }

        // for each row returned - should be just one
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new product object
            $product = new Product($row['product_id'], $row['product_name'], $row['description'], $row['delivered_date']);
        }
        
        // create the query string including any query fields
        $queryString = "update tblproducts set ".$queryFields." where product_id = :productid";
       
        // prepare the query
        $query = $writeDB->prepare($queryString);

        // if product name has been provided 
        if($productName_updated === true) {
            // set product object name to given value (checks for valid input) 
            $product->setProductName($jsonData->productName);
            
            // get the value back as the object could be handling the return of the value differently to what was provided
            $up_productName = $product->getProductName();
            
            // bind the parameter of the new value from the object to the query (prevents SQL injection) 
            $query->bindParam(':productName', $up_productName, PDO::PARAM_STR);
        }
       
        // if description has been provided
        if($description_updated === true) {
            // set product object description to given value (checks for valid input) 
            $product->setDescription($jsonData->description);

            // get the value back as the object could be handling the return of the value differently to what was provided
            $up_description = $product->getDescription();
            // bind the parameter of the new value from the object to the query (prevents SQL injection) 
            $query->bindParam(':description', $up_description, PDO::PARAM_STR);
        }

        // if delivered date has been provided
        if ($deliveredDate_updated === true) {
            // set product object delivered date to given value (checks for valid input)
            $product->setDeliveredDate($jsonData->deliveredDate);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_deliveredDate = $product->getDeliveredDate();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':deliveredDate', $up_deliveredDate, PDO::PARAM_STR);
        }

        // bind the product id provided in the query string
        $query->bindParam(':productid', $productid, PDO::PARAM_INT);
        // run the query
        $query->execute();

        // get affected row count
        $rowCount = $query->rowCount();

        // check if row was actually updated, could be that the given values were the same as the stored values
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Product not updated - given values may be the same as the stored values");
            $response->send();
            exit;
        }

        // create db query to return the newly edited product - connect to master database
        $query = $writeDB->prepare('SELECT product_id, product_name, description,
        DATE_FORMAT(delivered_date, "%d/%m/%Y %H:%i") as delivered_date from tblproducts where
        product_id = :productid');

        $query->bindParam(':productid', $productid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // check if product was found
        if($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No product found");
            $response->send();
            exit;
        }

        // create product array to store returned products
        $productArray = array();

        // for each row returned
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new product object for each row returned
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
        $response->addMessage("Product updated");
        $response->setData($returnData);
        $response->send();
        exit;
    }

    catch (ProductException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit;
    }
    // If error with sql query return a json error
    catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex, 0);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to update product - check your data for errors");
        $response->send();
        exit;
    }
}

// handle getting all products or creating a new one
elseif(empty($_GET)) {
    // if request is a GET e.g. get products
    if($_SERVER['REQUEST_METHOD'] === 'GET') {

        // attempt to query the database
        try {
            // create db query
            $query = $readDB->prepare('SELECT product_id,
            product_name, description,
            DATE_FORMAT(delivered_date, "%d/%m/%Y %H:%i") as
            delivered_date from tblproducts');
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();
            // create product array to store returned products
            $productArray = array();

            // for each row returned
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                // create new product object for each row returned
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
            error_log("Database Query Error: " . $ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to get products");
            $response->send();
            exit;
        }
    }

    // else if request is a POST e.g. create product
    elseif($_SERVER['REQUEST_METHOD'] === 'POST'){

        // create product
        try {
            // check request's content type header is JSON
            if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
                // set up response for unsuccessful request
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Content Type header not set to JSON");
                $response->send();
                exit;
            }

            // get POST request body as the POSTed data will be JSON format
            $rawPostData = file_get_contents('php://input');

            if(!$jsonData = json_decode($rawPostData)) {
                // set up response for unsuccessful request
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Request body is not valid JSON");
                $response->send();
                exit;
            }

            // create new product with data, if non mandatory fields not provided then set to null
            $newProduct = new Product(null, $jsonData->productName, (isset($jsonData->description) ? $jsonData->description : null), $jsonData->deliveredDate);
            // get product name, description, delivered date and store them in variables
            $productName = $newProduct->getProductName();
            $description = $newProduct->getDescription();
            $deliveredDate = $newProduct->getDeliveredDate();

            // create db query
            $query = $writeDB->prepare('insert into tblproducts (product_name, description, delivered_date)
            values (:productName, :description, STR_TO_DATE(:deliveredDate, \'%d/%m/%Y %H:%i\'))');

            $query->bindParam(':productName', $productName, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':deliveredDate', $deliveredDate, PDO::PARAM_STR);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // check if row was actually inserted, PDO exception should have caught it if not.
            if($rowCount === 0) {
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to create product");
                $response->send();
                exit;
            }

            // get last product id so we can return the product in the json
            $lastProductID = $writeDB->lastInsertId();
            // create db query to get newly created product - get from master db not read slave as replication may be too slow for successful read
            $query = $writeDB->prepare('SELECT product_id, product_name, description,
            DATE_FORMAT(delivered_date, "%d/%m/%Y %H:%i") as delivered_date from tblproducts where product_id =
            :productid');

            $query->bindParam(':productid', $lastProductID, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // make sure that the new product was returned
            if($rowCount === 0) {
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to retrieve product after creation");
                $response->send();
                exit;
            }

            // create empty array to store products
            $productArray = array();

            // for each row returned - should be just one
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                // create new product object
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
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("Product created");
            $response->setData($returnData);
            $response->send();
            exit;
        }
        // if product fails to create due to data types, missing fields or invalid data then send error json
        catch(ProductException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
        }
        // if error with sql query return a json error
        catch(PDOException $ex) {
            error_log("Database Query Error: " . $ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to insert product into database - check submitted data for errors");
            $response->send();
            exit;
        }
    }
    
    // If any other request method apart from GET or POST is used then return 405 method not allowed
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}

// return 404 error if endpoint not available
else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
}

?>