-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 07:13 AM
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
-- Database: `psa`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `log_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('scan_in','scan_out','adjustment') DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `description`, `category`, `quantity`, `reorder_level`, `qr_code`, `created_at`) VALUES
(1, 'Ballpen', 'Pen', 'pen', 10, 2, NULL, '2026-03-17 06:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_borrows`
--

CREATE TABLE `psa_borrows` (
  `borrow_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date_out` datetime DEFAULT current_timestamp(),
  `date_returned` datetime DEFAULT NULL,
  `status` enum('Borrowed','Returned','Lost') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_inventory_ledger`
--

CREATE TABLE `psa_inventory_ledger` (
  `ledger_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `trans_type` enum('Beginning','Sold','Returned_from_PSA','Adjustment') NOT NULL,
  `qty` int(11) NOT NULL,
  `trans_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_inventory_ledger`
--

INSERT INTO `psa_inventory_ledger` (`ledger_id`, `item_id`, `trans_type`, `qty`, `trans_date`, `reference_id`) VALUES
(1, 1, 'Sold', 1, '2026-03-24 03:21:46', 'wrewrw');

-- --------------------------------------------------------

--
-- Table structure for table `psa_items`
--

CREATE TABLE `psa_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` enum('Form','Device','Asset') NOT NULL,
  `is_borrowable` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_items`
--

INSERT INTO `psa_items` (`item_id`, `item_name`, `category`, `is_borrowable`) VALUES
(1, 'Marriage Certificate', 'Form', 0),
(2, 'Laptop', 'Device', 0);

-- --------------------------------------------------------

--
-- Table structure for table `psa_item_devices`
--

CREATE TABLE `psa_item_devices` (
  `item_id` int(11) NOT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `brand_model` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_item_devices`
--

INSERT INTO `psa_item_devices` (`item_id`, `serial_no`, `brand_model`) VALUES
(2, 'SN-234-4111', 'Lenovo Ideapad');

-- --------------------------------------------------------

--
-- Table structure for table `psa_item_forms`
--

CREATE TABLE `psa_item_forms` (
  `item_id` int(11) NOT NULL,
  `form_type_code` varchar(50) DEFAULT NULL,
  `qty_per_bundle` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_item_forms`
--

INSERT INTO `psa_item_forms` (`item_id`, `form_type_code`, `qty_per_bundle`) VALUES
(1, 'PECH-11', 50);

-- --------------------------------------------------------

--
-- Table structure for table `psa_permissions`
--

CREATE TABLE `psa_permissions` (
  `psa_permission_id` int(11) NOT NULL,
  `psa_permission_name` varchar(255) NOT NULL,
  `psa_permission_slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_roles`
--

CREATE TABLE `psa_roles` (
  `psa_roles_id` int(11) NOT NULL,
  `psa_role_name` varchar(255) NOT NULL,
  `psa_role_log` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_roles`
--

INSERT INTO `psa_roles` (`psa_roles_id`, `psa_role_name`, `psa_role_log`) VALUES
(1, 'staff', 'created by dev'),
(2, 'admin', 'created by dev');

-- --------------------------------------------------------

--
-- Table structure for table `psa_role_permiossions`
--

CREATE TABLE `psa_role_permiossions` (
  `psa_role_permission_id` int(11) NOT NULL,
  `psa_role_permission_roleid` varchar(255) NOT NULL,
  `psa_role_permission_perm_id` varchar(255) NOT NULL,
  `psa_role_permission_createdat` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_stock_in`
--

CREATE TABLE `psa_stock_in` (
  `stock_in_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `dr_number` varchar(50) NOT NULL,
  `qty_received` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `received_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `psa_users`
--

CREATE TABLE `psa_users` (
  `psa_user_id` int(11) NOT NULL,
  `psa_username` varchar(255) NOT NULL,
  `psa_password` varchar(255) NOT NULL,
  `psa_role_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psa_users`
--

INSERT INTO `psa_users` (`psa_user_id`, `psa_username`, `psa_password`, `psa_role_id`) VALUES
(1, 'loelyn', '$2y$10$cFn/z9q2G3jIE9bEZ1eB1eJmDMYG0L1C49y8yyoz9wtoqcrdS8W.u', 1),
(2, 'ates', '$2y$10$zi.JOmOm/A/miLYVHE1eKOqPpdMsoaxttTHEIlm3DuLaSiFWVIzOC', 2);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_items`
--

CREATE TABLE `request_items` (
  `request_item_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity_requested` int(11) DEFAULT NULL,
  `quantity_approved` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Loelyn Ates', 'atesberna845@gmail.com', '$2y$10$q6VoJo7bV6.XIfpawGWjp.WNwDoHSPZQYTl02ukDrjCJP0Mo4F23O', 'admin', '2026-03-17 03:33:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `psa_borrows`
--
ALTER TABLE `psa_borrows`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `staff_id` (`staff_id`);

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
-- Indexes for table `psa_item_devices`
--
ALTER TABLE `psa_item_devices`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `serial_no` (`serial_no`);

--
-- Indexes for table `psa_item_forms`
--
ALTER TABLE `psa_item_forms`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `psa_permissions`
--
ALTER TABLE `psa_permissions`
  ADD PRIMARY KEY (`psa_permission_id`);

--
-- Indexes for table `psa_roles`
--
ALTER TABLE `psa_roles`
  ADD PRIMARY KEY (`psa_roles_id`);

--
-- Indexes for table `psa_role_permiossions`
--
ALTER TABLE `psa_role_permiossions`
  ADD PRIMARY KEY (`psa_role_permission_id`);

--
-- Indexes for table `psa_stock_in`
--
ALTER TABLE `psa_stock_in`
  ADD PRIMARY KEY (`stock_in_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `psa_users`
--
ALTER TABLE `psa_users`
  ADD PRIMARY KEY (`psa_user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `request_items`
--
ALTER TABLE `request_items`
  ADD PRIMARY KEY (`request_item_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_borrows`
--
ALTER TABLE `psa_borrows`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_inventory_ledger`
--
ALTER TABLE `psa_inventory_ledger`
  MODIFY `ledger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `psa_items`
--
ALTER TABLE `psa_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `psa_permissions`
--
ALTER TABLE `psa_permissions`
  MODIFY `psa_permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_roles`
--
ALTER TABLE `psa_roles`
  MODIFY `psa_roles_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `psa_role_permiossions`
--
ALTER TABLE `psa_role_permiossions`
  MODIFY `psa_role_permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_stock_in`
--
ALTER TABLE `psa_stock_in`
  MODIFY `stock_in_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `psa_users`
--
ALTER TABLE `psa_users`
  MODIFY `psa_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_items`
--
ALTER TABLE `request_items`
  MODIFY `request_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `psa_borrows`
--
ALTER TABLE `psa_borrows`
  ADD CONSTRAINT `psa_borrows_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`),
  ADD CONSTRAINT `psa_borrows_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `psa_users` (`psa_user_id`);

--
-- Constraints for table `psa_inventory_ledger`
--
ALTER TABLE `psa_inventory_ledger`
  ADD CONSTRAINT `psa_inventory_ledger_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`);

--
-- Constraints for table `psa_item_devices`
--
ALTER TABLE `psa_item_devices`
  ADD CONSTRAINT `psa_item_devices_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_item_forms`
--
ALTER TABLE `psa_item_forms`
  ADD CONSTRAINT `psa_item_forms_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `psa_stock_in`
--
ALTER TABLE `psa_stock_in`
  ADD CONSTRAINT `psa_stock_in_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `psa_items` (`item_id`),
  ADD CONSTRAINT `psa_stock_in_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `psa_users` (`psa_user_id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `request_items`
--
ALTER TABLE `request_items`
  ADD CONSTRAINT `request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`),
  ADD CONSTRAINT `request_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
