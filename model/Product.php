<?php
//Product Model Object

//empty ProductException class so we can catch product errors

class ProductException extends Exception {}

class Product {
    //define private variables

private $_productID;
private $_productName;
private $_description;
private $_deliveredDate;

//constructor
public function __construct($productID, $productName, $description, $deliveredDate) {
    $this->setProductID($productID);
    $this->setProductName($productName);
    $this->setDescription($description);
    $this->setDeliveredDate($deliveredDate);
}

//getters
public function getProductID() {
    return $this->_productID;
}

public function getProductName() {
    return $this->_productName;
}

public function getDescription() {
    return $this->_description;
}

public function getDeliveredDate() {
    return $this->_deliveredDate;
}

//private prod ID setter
public function setProductID($productID) {
    if(($productID!==null) && 
    (!is_numeric($productID) || $productID<=0 || 
    $productID>9223372036854775807 || $this->_productID !== null)) {
        throw new ProductException('Product ID error');
    }
    $this->_productID = $productID;
}

//private prod name setter
public function setProductName($productName) {
    if(strlen($productName)<0|| strlen($productName)>255) {
        throw new ProductException('Product name error');
    }
    $this->_productName = $productName;
}

//private prod desc setter
public function setDescription($description) {
    if (($description!==null) && (strlen($description)>16777215)) {
        throw new ProductException('Product description error');
    }
    $this->_description=$description;
}

//private prod date setter
public function setDeliveredDate($deliveredDate) {
    if(($deliveredDate!==null) && 
    !date_create_from_format('d/m/Y H:i', $deliveredDate) ||
    date_format(date_create_from_format('d/m/Y H:i', $deliveredDate), 'd/m/Y H:i') != $deliveredDate) {
        throw new ProductException('Product delivered date error');
    }
    $this->_deliveredDate = $deliveredDate;
}

//func to return products as array
    public function returnProductAsArray() {
        $product = array();
        $product['productID'] = $this->getProductID();
        $product['productName'] = $this->getProductName();
        $product['description'] = $this->getDescription();
        $product['deliveredDate'] = $this->getDeliveredDate();

        return $product;
    }
}
?>