-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 10:22 AM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `psa_assets_logs`
--

CREATE TABLE `psa_assets_logs` (
  `log_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `log_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_device_borrow`
--

CREATE TABLE `psa_device_borrow` (
  `borrow_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `borrower_name` varchar(255) DEFAULT NULL,
  `date_borrowed` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_device_borrow`
--

INSERT INTO `psa_device_borrow` (`borrow_id`, `device_id`, `borrower_name`, `date_borrowed`) VALUES
(1, 1, 'Genevieve Cabug', '2026-04-16'),
(2, 1, 'Genevieve Cabug', '2026-04-16');

-- --------------------------------------------------------

--
-- Table structure for table `psa_device_return`
--

CREATE TABLE `psa_device_return` (
  `return_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `borrower_name` varchar(255) DEFAULT NULL,
  `date_returned` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_device_return`
--

INSERT INTO `psa_device_return` (`return_id`, `device_id`, `borrower_name`, `date_returned`) VALUES
(1, 1, 'Genevieve Cabug', '2026-04-16'),
(2, 1, 'Genevieve Cabug', '2026-04-16');

-- --------------------------------------------------------

--
-- Table structure for table `psa_inventory_ledger`
--

CREATE TABLE `psa_inventory_ledger` (
  `ledger_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `trans_type` enum('Beginning','Received','Sold','Returned_from_PSA') DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `ref_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_inventory_ledger`
--

INSERT INTO `psa_inventory_ledger` (`ledger_id`, `item_id`, `trans_type`, `qty`, `trans_date`, `ref_no`) VALUES
(1, 8, 'Received', 100, '2026-04-16', NULL),
(2, 1, 'Received', 100, '2026-04-16', NULL),
(3, 2, 'Received', 100, '2026-04-16', NULL),
(4, 3, 'Received', 100, '2026-04-16', NULL),
(5, 1, 'Sold', 2, '2026-04-16', NULL),
(6, 1, 'Received', 100, '2026-04-16', NULL),
(7, 1, 'Returned_from_PSA', 2, '2026-04-16', NULL),
(8, 11, 'Received', 10, '2026-04-16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `psa_items`
--

CREATE TABLE `psa_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` enum('Form','Device','Asset') NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_items`
--

INSERT INTO `psa_items` (`item_id`, `item_name`, `category`, `description`, `price`, `quantity`, `status`, `created_at`) VALUES
(1, 'Birth Certificate', 'Form', NULL, 155.00, 0, 'Available', '2026-04-16 01:38:48'),
(2, 'Marriage Certificate', 'Form', NULL, 155.00, 0, 'Available', '2026-04-16 01:38:48'),
(3, 'Death Certificate', 'Form', NULL, 155.00, 0, 'Available', '2026-04-16 01:38:48'),
(4, 'Laptop', 'Device', NULL, 35000.00, 0, 'Available', '2026-04-16 01:38:48'),
(5, 'Tablet', 'Device', NULL, 15000.00, 0, 'Available', '2026-04-16 01:38:48'),
(6, 'Office Chair', 'Asset', NULL, 1200.00, 10, 'Available', '2026-04-16 01:38:48'),
(7, 'Table', 'Asset', NULL, 2500.00, 5, 'Available', '2026-04-16 01:38:48'),
(8, 'Cenumar', 'Form', 'Cenumar ', 155.00, 100, 'Available', '2026-04-16 02:50:07'),
(9, 'TV ', 'Asset', 'Devant', 65000.00, 2, 'Available', '2026-04-16 02:51:10'),
(10, 'Laptop ', 'Device', 'HP Probook, COREi5', 0.00, 0, 'Available', '2026-04-16 03:18:00'),
(11, 'Birth Certificated', 'Form', '', 155.00, 10, 'Available', '2026-04-16 05:53:16'),
(12, 'Laptop ', 'Device', 'Acer', 0.00, 0, 'Available', '2026-04-16 06:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `psa_item_assets`
--

CREATE TABLE `psa_item_assets` (
  `asset_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `acquisition_cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_item_devices`
--

CREATE TABLE `psa_item_devices` (
  `device_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(100) DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `accountable_officer` varchar(255) DEFAULT NULL,
  `brand_model` varchar(255) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `acquisition_cost` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('Available','Borrowed','Not Available') DEFAULT 'Available',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_item_devices`
--

INSERT INTO `psa_item_devices` (`device_id`, `item_id`, `inventory_tag`, `property_no`, `accountable_officer`, `brand_model`, `serial_no`, `date_acquired`, `acquisition_cost`, `location`, `status`, `remark`) VALUES
(1, 10, '1', 'CS-DIT-0000-03-2026', 'Loelyn T. Ates', 'HP laptop ', 'VAGGWRJSX20100W', '2026-02-04', 33000.00, 'Locker 9', 'Available', NULL),
(2, 12, '1', 'CS-DIT-0000-03-2026', 'Loelyn T. Ates', 'HP laptop ', 'VAGGWRJSX20100W', '2026-02-04', 35000.00, 'Locker 9', 'Available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `psa_item_forms`
--

CREATE TABLE `psa_item_forms` (
  `form_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `bundle_size` int(11) NOT NULL,
  `price_per_bundle` decimal(10,2) NOT NULL,
  `price_per_piece` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_reports`
--

CREATE TABLE `psa_reports` (
  `report_id` int(11) NOT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `generated_by` varchar(255) DEFAULT NULL,
  `date_generated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_returns`
--

CREATE TABLE `psa_returns` (
  `return_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `qty_returned` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `ref_no` varchar(100) DEFAULT NULL,
  `date_returned` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_returns`
--

INSERT INTO `psa_returns` (`return_id`, `item_id`, `qty_returned`, `reason`, `ref_no`, `date_returned`) VALUES
(1, 1, 2, NULL, NULL, '2026-04-16');

-- --------------------------------------------------------

--
-- Table structure for table `psa_sales`
--

CREATE TABLE `psa_sales` (
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `buyer_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `qty_sold` int(11) DEFAULT NULL,
  `ref_no` varchar(100) DEFAULT NULL,
  `date_sold` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_sales`
--

INSERT INTO `psa_sales` (`sale_id`, `item_id`, `buyer_name`, `address`, `qty_sold`, `ref_no`, `date_sold`) VALUES
(1, 1, 'Rica Telecio', 'Medina Hospital, Ozamiz City', 2, NULL, '2026-04-16');

-- --------------------------------------------------------

--
-- Table structure for table `psa_stock_in`
--

CREATE TABLE `psa_stock_in` (
  `stock_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `dr_number` varchar(100) DEFAULT NULL,
  `qty_received` int(11) DEFAULT NULL,
  `date_received` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `psa_assets_logs`
--
ALTER TABLE `psa_assets_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_device_borrow`
--
ALTER TABLE `psa_device_borrow`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `psa_device_return`
--
ALTER TABLE `psa_device_return`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `psa_inventory_ledger`
--
ALTER TABLE `psa_inventory_ledger`
  ADD PRIMARY KEY (`ledger_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_items`
--
ALTER TABLE `psa_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `psa_item_assets`
--
ALTER TABLE `psa_item_assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_item_devices`
--
ALTER TABLE `psa_item_devices`
  ADD PRIMARY KEY (`device_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_item_forms`
--
ALTER TABLE `psa_item_forms`
  ADD PRIMARY KEY (`form_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_reports`
--
ALTER TABLE `psa_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `psa_returns`
--
ALTER TABLE `psa_returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_sales`
--
ALTER TABLE `psa_sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_stock_in`
--
ALTER TABLE `psa_stock_in`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `psa_assets_logs`
--
ALTER TABLE `psa_assets_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_device_borrow`
--
ALTER TABLE `psa_device_borrow`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `psa_device_return`
--
ALTER TABLE `psa_device_return`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `psa_inventory_ledger`
--
ALTER TABLE `psa_inventory_ledger`
  MODIFY `ledger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `psa_items`
--
ALTER TABLE `psa_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `psa_item_assets`
--
ALTER TABLE `psa_item_assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_item_devices`
--
ALTER TABLE `psa_item_devices`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `psa_item_forms`
--
ALTER TABLE `psa_item_forms`
  MODIFY `form_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_reports`
--
ALTER TABLE `psa_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_returns`
--
ALTER TABLE `psa_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `psa_sales`
--
ALTER TABLE `psa_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `psa_stock_in`
--
ALTER TABLE `psa_stock_in`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `psa_assets_logs`
--
ALTER TABLE `psa_assets_logs`
  ADD CONSTRAINT `psa_assets_logs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_device_borrow`
--
ALTER TABLE `psa_device_borrow`
  ADD CONSTRAINT `psa_device_borrow_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `psa_item_devices` (`device_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_device_return`
--
ALTER TABLE `psa_device_return`
  ADD CONSTRAINT `psa_device_return_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `psa_item_devices` (`device_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_inventory_ledger`
--
ALTER TABLE `psa_inventory_ledger`
  ADD CONSTRAINT `psa_inventory_ledger_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_item_assets`
--
ALTER TABLE `psa_item_assets`
  ADD CONSTRAINT `psa_item_assets_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_item_devices`
--
ALTER TABLE `psa_item_devices`
  ADD CONSTRAINT `psa_item_devices_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_item_forms`
--
ALTER TABLE `psa_item_forms`
  ADD CONSTRAINT `psa_item_forms_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`);

--
-- Constraints for table `psa_returns`
--
ALTER TABLE `psa_returns`
  ADD CONSTRAINT `psa_returns_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_sales`
--
ALTER TABLE `psa_sales`
  ADD CONSTRAINT `psa_sales_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_stock_in`
--
ALTER TABLE `psa_stock_in`
  ADD CONSTRAINT `psa_stock_in_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
