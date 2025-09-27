-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2025 at 04:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `productscjdm`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblproducts`
--

CREATE TABLE `tblproducts` (
  `product_id` bigint(20) NOT NULL COMMENT 'Product ID - Primary Key',
  `product_name` varchar(256) NOT NULL COMMENT 'Product Name',
  `description` mediumtext DEFAULT NULL COMMENT 'Product Description',
  `delivered_date` datetime NOT NULL COMMENT 'Product Delivered Date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblproducts`
--

INSERT INTO `tblproducts` (`product_id`, `product_name`, `description`, `delivered_date`) VALUES
(1, 'Pork Xiaolongbao', 'Soup dumplings with 10 pieces', '2025-09-11 14:26:04'),
(2, 'Crispy Wontons', 'Deep fried shrimp dumplings available in 5 or 10 pieces', '2025-09-12 15:45:42'),
(3, 'Fried rice take out box', 'Our best tasting shrimp and egg fried rice', '2025-09-16 09:16:32'),
(4, 'Bubble Tea', 'Milk Tea 500ml', '2025-09-20 12:15:21'),
(5, 'Coca Cola', 'Canned Coca Cola 300ml', '2025-09-21 09:50:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblproducts`
--
ALTER TABLE `tblproducts`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblproducts`
--
ALTER TABLE `tblproducts`
  MODIFY `product_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Product ID - Primary Key', AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
