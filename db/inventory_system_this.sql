-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 11:05 PM
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
  `status` enum('Borrowed','Returned','Overdue') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_records`
--

INSERT INTO `borrow_records` (`borrow_id`, `item_id`, `employee_id`, `quantity_borrowed`, `borrow_date`, `status`) VALUES
(1, 1, 1, 1, '2026-04-27', 'Returned'),
(2, 2, 1, 1, '2026-04-27', 'Returned'),
(3, 1, 2, 1, '2026-04-27', 'Returned'),
(4, 2, 2, 1, '2026-04-27', 'Returned'),
(5, 2, 2, 1, '2026-04-26', 'Returned'),
(6, 3, 3, 1, '2026-04-27', 'Returned'),
(7, 4, 3, 1, '2026-04-29', 'Returned'),
(8, 1, 3, 1, '2026-04-27', 'Returned'),
(9, 3, 3, 1, '2026-04-29', 'Borrowed'),
(10, 2, 3, 1, '2026-04-27', 'Borrowed'),
(11, 5, 3, 1, '2026-04-27', 'Borrowed'),
(12, 6, 5, 1, '2026-04-29', 'Borrowed'),
(13, 4, 1, 1, '2026-04-29', 'Returned'),
(14, 1, 1, 2, '2026-05-06', 'Returned'),
(15, 7, 1, 1, '2026-05-08', 'Returned'),
(16, 10, 2, 1, '2026-05-12', 'Borrowed'),
(17, 9, 2, 1, '2026-05-12', 'Borrowed'),
(18, 8, 1, 2, '2026-05-12', 'Borrowed'),
(19, 11, 3, 1, '2026-05-12', 'Borrowed'),
(20, 1, 2, 1, '2026-05-12', 'Borrowed');

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

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_name`, `office_unit`, `position`, `contact_no`) VALUES
(1, 'Julieta Nacario', 'PSA ', 'Office-In-Charge', '09709096518'),
(2, 'Loelyn Tagalogon Ates', 'PSA', 'OJT', '0906609672'),
(3, 'Genevieve Cabug', 'PSA', 'OJT', '09774748774'),
(4, 'Rica May Telecio', 'PSA', 'OJT', '09735463627'),
(5, 'Alyssa Infantado', 'PSA', 'Manager', '09364664631'),
(6, 'Edna', 'PSA', 'FIES', '09999999999');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_assignments`
--

CREATE TABLE `equipment_assignments` (
  `assignment_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_date` date DEFAULT curdate(),
  `status` enum('Active','Returned') DEFAULT 'Active',
  `borrow_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL
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
  `category` enum('Device','Furniture and Fixtures','Office Equipment','Supplies','Vehicles') DEFAULT NULL,
  `equipment_type` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `acquisition_cost` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit` varchar(30) DEFAULT NULL,
  `item_condition` enum('Good','Repair Needed','Unserviceable') DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_inventory`
--

INSERT INTO `equipment_inventory` (`item_id`, `property_no`, `inventory_tag_no`, `description`, `category`, `equipment_type`, `serial_no`, `date_acquired`, `acquisition_cost`, `quantity`, `unit`, `item_condition`, `location`, `batch_id`) VALUES
(1, 'CS-DIT-0000-03-2026', '1', 'Corei5 ASUS Laptop', 'Device', NULL, 'JNISJ8WKOAJ', '2026-04-27', 55000.00, 0, '1', 'Repair Needed', 'Locker 1', NULL),
(2, 'LAPTOP-DIVHP-2026', '2', 'COREi7 Acer Pro', 'Device', NULL, 'NSHJAUW228338', '2026-04-27', 55.00, 0, '1', 'Good', 'Locker 10', NULL),
(3, 'M00001-00191', '3', 'Mouse Lenovo for Laptop', 'Device', NULL, 'HSHSDWYHHA', '2026-04-20', 55.00, 0, '1', 'Good', 'Locker 10', NULL),
(4, 'CUB-0000-000001', '4', 'Table', 'Furniture and Fixtures', NULL, 'HG6AGDGS6SJ', '2026-04-17', 56000.00, 1, '1', 'Good', 'PSA Office', NULL),
(5, 'M00001-00190', '5', 'Mouse Lenovo for Laptop', 'Device', NULL, 'DDJDIKKSMWI', '2026-04-14', 55.00, 0, '1', 'Good', 'Locker 1', NULL),
(6, 'CS-DIT-0000-01-2026', '11', 'Corei5 ASUS Laptop', 'Device', NULL, 'NNUIW28J2A29', '2026-04-29', 45000.00, 0, '1', 'Good', 'Locker 9', NULL),
(7, '1000-0000-0011', '123', 'Dell Computer ', 'Device', 'Computer (Desktop/Laptop)', 'HWSWYDBWJW2', '2026-05-06', 55.00, 1, '1', '', 'Office', NULL),
(8, '069969', '1234', 'Round Table', 'Furniture and Fixtures', 'Workstation Desk', 'IJDIJD', '2026-05-08', 34000.00, -1, '1', 'Good', 'Office', 1),
(9, '0949581', '15255', 'Dell', 'Device', 'Computer (Desktop/Laptop)', 'EHJDUED', '2026-05-08', 56000.00, 0, '1', 'Good', 'Office', 1),
(10, '0009898', '887', 'Round Table', 'Furniture and Fixtures', 'Executive Table', 'GIHKJH', '2026-05-09', 45000.00, 0, 'pc', 'Good', 'Office', 2),
(11, 'M-040041', '1234', 'Chair', 'Furniture and Fixtures', 'Office Chair', 'JFUUEUEUE', '2026-05-10', 450.00, 0, 'pc', 'Good', 'Office', 3);

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
  `status` varchar(30) DEFAULT NULL,
  `min_stock` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`form_id`, `form_code`, `form_name`, `unit_price`, `beginning_inventory`, `current_stock`, `status`, `min_stock`) VALUES
(4, 'MF - 97 Marriage Cert.', 'Marriage Certificate', 155.00, 100, 99, 'Available', 50),
(6, 'MF - 90 Application', 'Application', 100.00, 150, 200, 'Available', 50),
(7, 'MF - 102 Birth Certificate', 'Birth Certificate', 155.00, 20, 100, 'Available', 50),
(8, 'Form 102 - Cert. of Divorce', 'Cert. of Divorce', 180.00, 50, 48, 'Available', 50),
(9, 'Form 104 - Conv. to Islam', 'Conv. to Islam', 150.00, 120, 120, 'Available', 50),
(10, 'MF - 103  Death Certificate', 'Death Certificate', 180.00, 155, 145, 'Available', 50),
(11, 'Form 101 - Founding', 'Founding', 150.00, 100, 100, 'Available', 50),
(12, 'IP FORM - 1', 'IP FORM - 1', 100.00, 50, 50, 'Available', 50),
(13, 'IP FORM - 2', 'IP FORM - 2', 100.00, 120, 118, 'Available', 50),
(14, 'IP FORM - 3', 'IP FORM - 3', 120.00, 150, 149, 'Available', 50),
(15, 'IP FORM - 4', 'IP FORM - 4', 150.00, 10, 9, 'Available', 50),
(16, 'IP FORM - 5', 'IP FORM - 5', 100.00, 100, 100, 'Available', 50),
(17, 'Muslim Attachment 103', 'Muslim Attachment 103', 110.00, 10, 9, 'Available', 50),
(18, 'Muslim Attachment 97', 'Muslim Attachment 97', 180.00, 20, 20, 'Available', 50),
(19, 'Muslim Attachment 102', 'Muslim Attachment 102', 100.00, 20, 18, 'Available', 50),
(20, 'Form 103 - Rev. of Divorce', 'Rev. of Divorce', 190.00, 35, 34, 'Available', 50);

-- --------------------------------------------------------

--
-- Table structure for table `form_restock`
--

CREATE TABLE `form_restock` (
  `restock_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `delivery_receipt_no` varchar(50) NOT NULL,
  `quantity_received` int(11) DEFAULT NULL,
  `date_received` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_restock`
--

INSERT INTO `form_restock` (`restock_id`, `form_id`, `delivery_receipt_no`, `quantity_received`, `date_received`) VALUES
(3, 6, '0000-0001-26', 50, '2026-05-05'),
(4, 7, '0000-0001-27', 80, '2026-05-05'),
(5, 6, '0000-0001-27', 1, '2026-05-06');

-- --------------------------------------------------------

--
-- Table structure for table `form_sales`
--

CREATE TABLE `form_sales` (
  `sale_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `buyer_name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `date_sold` date DEFAULT NULL,
  `sold_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_sales`
--

INSERT INTO `form_sales` (`sale_id`, `form_id`, `buyer_name`, `department`, `address`, `quantity_sold`, `total_amount`, `date_sold`, `sold_by`) VALUES
(4, 8, 'Alyssa', 'Medina Hospital', 'Maningcol, Ozamiz City, Misamis Occidental', 1, 180.00, '2026-05-05', 1),
(5, 8, 'Rica', 'Mhars Gen', 'Maningcol, Ozamiz City, Misamis Occidental', 1, 180.00, '2026-05-05', 1),
(6, 10, 'Rica May Telecio', 'Medina Hospital', 'Gango, Ozamiz City, Misamis Occidental', 10, 1800.00, '2026-05-05', 1),
(7, 13, 'Rica', 'Medina Hospital', 'Medina Hospital, Ozamiz City', 2, 200.00, '2026-05-05', 1),
(8, 4, 'Rica', 'Mhars Gen', 'Maningcol, Ozamiz City, Misamis Occidental', 1, 155.00, '2026-05-05', 1),
(9, 19, 'Loelyn', 'PAW', 'Oroquieta City', 2, 200.00, '2026-05-05', 1),
(10, 14, 'Genevieve Cabug', 'Medina Hospital', 'Medina Hospital, Ozamiz City', 1, 120.00, '2026-05-05', 1),
(11, 17, 'Loelyn', 'Medina Hospital', 'Medina Hospital, Ozamiz City', 1, 110.00, '2026-05-06', 1),
(12, 6, 'Rica', 'Mhars Gen', 'Maningcol, Ozamiz City, Misamis Occidental', 1, 100.00, '2026-05-05', 1),
(13, 20, 'Alyssas', 'Medina Hospital', 'Maningcol, Ozamiz City, Misamis Occidental', 1, 190.00, '2026-05-05', 1),
(14, 15, 'Rica', 'PAW', 'Oroquieta City', 1, 150.00, '2026-05-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `receivers`
--

CREATE TABLE `receivers` (
  `receiver_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `office_unit` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receivers`
--

INSERT INTO `receivers` (`receiver_id`, `name`, `office_unit`, `position`, `contact_no`, `created_at`) VALUES
(1, 'Loelyn T. Ates', 'PSA', 'Admin', '09088987782', '2026-05-06 09:52:04'),
(2, 'Rica', 'PSA', 'Admin', '09848484444', '2026-05-08 13:44:48'),
(3, 'Alyssa Infantado', 'PSA Office', 'Custodian', '0985768954', '2026-05-10 10:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `receiving_batches`
--

CREATE TABLE `receiving_batches` (
  `batch_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `received_date` date NOT NULL,
  `attachment1` varchar(255) DEFAULT NULL,
  `attachment2` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receiving_batches`
--

INSERT INTO `receiving_batches` (`batch_id`, `receiver_id`, `received_date`, `attachment1`, `attachment2`, `remarks`, `created_at`) VALUES
(1, 1, '2026-05-08', '1778248953_INVENTORY_SYSTEM_WIREFRAME.pdf', '1778248953_INVENTORY_SYSTEM_WIREFRAME.pdf', 'Good Condition', '2026-05-08 13:59:46'),
(2, 1, '2026-05-09', '1778249728_User Guide and Template Link.pdf', '', 'Good Condition', '2026-05-08 14:15:28'),
(3, 3, '2026-05-10', '1778407743_YAHONG ELEMENTARY SCHOOL lesson plan.docx', '', 'For the admin office batch', '2026-05-10 10:09:03');

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

--
-- Dumping data for table `return_records`
--

INSERT INTO `return_records` (`return_id`, `borrow_id`, `actual_return_date`, `returned_condition`, `remarks`) VALUES
(1, 1, '2026-04-27', 'Damaged', 'Early Return kay damaged'),
(2, 2, '2026-04-27', 'Good', ''),
(3, 3, '2026-04-27', 'Good', ''),
(4, 4, '2026-04-27', 'Good', ''),
(5, 5, '2026-04-28', 'Good', ''),
(6, 6, '2026-04-28', 'Good', ''),
(7, 7, '2026-04-28', 'Good', 'Good'),
(8, 8, '2026-04-29', 'Good', ''),
(9, 14, '2026-05-12', 'Good', ''),
(10, 13, '2026-05-12', 'Good', ''),
(11, 15, '2026-05-12', 'Good', '');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `office_unit` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `role`, `created_at`, `email`, `contact_no`, `profile_picture`, `position`, `office_unit`, `last_login`, `status`, `updated_at`) VALUES
(1, 'Daphne Villa', 'formsadmin', '123456', 'forms_admin', '2026-04-25 07:46:19', 'atesberna845@gmail.com', '0909099999', 'user_1_1778034932.png', NULL, NULL, '2026-05-07 14:38:28', 'Active', '2026-05-07 06:38:28'),
(2, 'Inventory Administrator', 'inventoryadmin', '123456', 'inventory_admin', '2026-04-25 07:46:19', 'loelynates@gmail.com', '09709096518', '1777514711_453437074_1918131022030963_6722034240843927653_n.jpg', 'Custodian', 'PSA', '2026-05-12 08:11:44', 'Active', '2026-05-12 00:11:44');

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
-- Indexes for table `equipment_assignments`
--
ALTER TABLE `equipment_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `borrow_id` (`borrow_id`);

--
-- Indexes for table `equipment_inventory`
--
ALTER TABLE `equipment_inventory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_batch` (`batch_id`);

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
-- Indexes for table `receivers`
--
ALTER TABLE `receivers`
  ADD PRIMARY KEY (`receiver_id`);

--
-- Indexes for table `receiving_batches`
--
ALTER TABLE `receiving_batches`
  ADD PRIMARY KEY (`batch_id`),
  ADD KEY `receiver_id` (`receiver_id`);

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
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipment_assignments`
--
ALTER TABLE `equipment_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_inventory`
--
ALTER TABLE `equipment_inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `form_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `form_restock`
--
ALTER TABLE `form_restock`
  MODIFY `restock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `form_sales`
--
ALTER TABLE `form_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `receivers`
--
ALTER TABLE `receivers`
  MODIFY `receiver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `receiving_batches`
--
ALTER TABLE `receiving_batches`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `returned_forms`
--
ALTER TABLE `returned_forms`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_records`
--
ALTER TABLE `return_records`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  ADD CONSTRAINT `borrow_records_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `fk_borrow_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_item` FOREIGN KEY (`item_id`) REFERENCES `equipment_inventory` (`item_id`) ON UPDATE CASCADE;

--
-- Constraints for table `equipment_assignments`
--
ALTER TABLE `equipment_assignments`
  ADD CONSTRAINT `equipment_assignments_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `equipment_inventory` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_assignments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_assignments_ibfk_3` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_records` (`borrow_id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_inventory`
--
ALTER TABLE `equipment_inventory`
  ADD CONSTRAINT `fk_batch` FOREIGN KEY (`batch_id`) REFERENCES `receiving_batches` (`batch_id`),
  ADD CONSTRAINT `fk_item_batch` FOREIGN KEY (`batch_id`) REFERENCES `receiving_batches` (`batch_id`) ON UPDATE CASCADE;

--
-- Constraints for table `form_restock`
--
ALTER TABLE `form_restock`
  ADD CONSTRAINT `fk_restock_form` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `form_restock_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`);

--
-- Constraints for table `form_sales`
--
ALTER TABLE `form_sales`
  ADD CONSTRAINT `fk_sale_form` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_user` FOREIGN KEY (`sold_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `form_sales_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`),
  ADD CONSTRAINT `form_sales_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `receiving_batches`
--
ALTER TABLE `receiving_batches`
  ADD CONSTRAINT `fk_batch_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `receivers` (`receiver_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `receiving_batches_ibfk_1` FOREIGN KEY (`receiver_id`) REFERENCES `receivers` (`receiver_id`);

--
-- Constraints for table `returned_forms`
--
ALTER TABLE `returned_forms`
  ADD CONSTRAINT `fk_returned_form` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `returned_forms_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`form_id`);

--
-- Constraints for table `return_records`
--
ALTER TABLE `return_records`
  ADD CONSTRAINT `fk_return_borrow` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_records` (`borrow_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `return_records_ibfk_1` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_records` (`borrow_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
