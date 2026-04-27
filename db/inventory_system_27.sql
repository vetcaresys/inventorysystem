-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2026 at 03:00 AM
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
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrow_records`
--

CREATE TABLE `borrow_records` (
  `borrow_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `quantity_borrowed` int(11) DEFAULT NULL,
  `borrow_date` date DEFAULT NULL,
  `expected_return` date DEFAULT NULL,
  `status` enum('Borrowed','Returned','Overdue') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `office_unit` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_inventory`
--

CREATE TABLE `equipment_inventory` (
  `item_id` int(11) NOT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `inventory_tag_no` varchar(100) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `category` enum('Device','Furniture and Fixtures','Office Equipment','Supplies') DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `acquisition_cost` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit` varchar(30) DEFAULT NULL,
  `item_condition` enum('Good','Repair Needed','Unserviceable') DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `accountable_officer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `form_id` int(11) NOT NULL,
  `form_code` varchar(50) DEFAULT NULL,
  `form_name` varchar(100) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `beginning_inventory` int(11) DEFAULT 0,
  `current_stock` int(11) DEFAULT 0,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`form_id`, `form_code`, `form_name`, `unit_price`, `beginning_inventory`, `current_stock`, `status`) VALUES
(1, '0001-0050-2026', 'Birth Certificate', 305.00, 100, 198, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `form_restock`
--

CREATE TABLE `form_restock` (
  `restock_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `delivery_receipt_no` varchar(50) DEFAULT NULL,
  `quantity_received` int(11) DEFAULT NULL,
  `date_received` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_restock`
--

INSERT INTO `form_restock` (`restock_id`, `form_id`, `delivery_receipt_no`, `quantity_received`, `date_received`) VALUES
(1, 1, '0000-0001-26', 100, '2026-04-25');

-- --------------------------------------------------------

--
-- Table structure for table `form_sales`
--

CREATE TABLE `form_sales` (
  `sale_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `date_sold` date DEFAULT NULL,
  `sold_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_sales`
--

INSERT INTO `form_sales` (`sale_id`, `form_id`, `quantity_sold`, `total_amount`, `date_sold`, `sold_by`) VALUES
(1, 1, 2, 610.00, '2026-04-25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `returned_forms`
--

CREATE TABLE `returned_forms` (
  `return_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `quantity_returned` int(11) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_records`
--

CREATE TABLE `return_records` (
  `return_id` int(11) NOT NULL,
  `borrow_id` int(11) DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `returned_condition` enum('Good','Damaged') DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('forms_admin','inventory_admin') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Forms Administrator', 'formsadmin', '123456', 'forms_admin', '2026-04-25 07:46:19'),
(2, 'Inventory Administrator', 'inventoryadmin', '123456', 'inventory_admin', '2026-04-25 07:46:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrow_records`
--
ALTER TABLE `borrow_records`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `equipment_inventory`
--
ALTER TABLE `equipment_inventory`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`form_id`);

--
-- Indexes for table `form_restock`
--
ALTER TABLE `form_restock`
  ADD PRIMARY KEY (`restock_id`),
  ADD KEY `form_id` (`form_id`);

--
-- Indexes for table `form_sales`
--
ALTER TABLE `form_sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `sold_by` (`sold_by`);

--
-- Indexes for table `returned_forms`
--
ALTER TABLE `returned_forms`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `form_id` (`form_id`);

--
-- Indexes for table `return_records`
--
ALTER TABLE `return_records`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `borrow_id` (`borrow_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrow_records`
--
ALTER TABLE `borrow_records`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_inventory`
--
ALTER TABLE `equipment_inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `form_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `form_restock`
--
ALTER TABLE `form_restock`
  MODIFY `restock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `form_sales`
--
ALTER TABLE `form_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `returned_forms`
--
ALTER TABLE `returned_forms`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_records`
--
ALTER TABLE `return_records`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_records`
--
ALTER TABLE `borrow_records`
  ADD CONSTRAINT `borrow_records_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `equipment_inventory` (`item_id`),
  ADD CONSTRAINT `borrow_records_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `form_restock`
--
ALTER TABLE `form_restock`
  ADD CONSTRAINT `form_restock_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`);

--
-- Constraints for table `form_sales`
--
ALTER TABLE `form_sales`
  ADD CONSTRAINT `form_sales_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`),
  ADD CONSTRAINT `form_sales_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `returned_forms`
--
ALTER TABLE `returned_forms`
  ADD CONSTRAINT `returned_forms_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`);

--
-- Constraints for table `return_records`
--
ALTER TABLE `return_records`
  ADD CONSTRAINT `return_records_ibfk_1` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_records` (`borrow_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
