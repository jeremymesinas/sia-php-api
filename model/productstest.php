<?php
require_once('Product.php');

try {
    $product = new Product(100, "Product Here", "Description Here", "17/09/2021 12:00");
    header('Content-type: application/json;charset=UTF-8');
    echo json_encode($product->returnProductAsArray());
}
catch (ProductException $ex) {
    echo "Error: " . $ex->getMessage();
} 

?>