-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 09:55 PM
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
-- Database: `tours`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `ActivityID` int(11) NOT NULL,
  `ActivityName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity`
--

INSERT INTO `activity` (`ActivityID`, `ActivityName`, `Description`) VALUES
(1, 'Whale Watching', 'Boat tour to see whales'),
(2, 'Sigiriya Hike', 'Climb the Lion Rock');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `PackagesID` int(11) NOT NULL,
  `BookingDate` date NOT NULL,
  `STATUS` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `CustomerID`, `PackagesID`, `BookingDate`, `STATUS`) VALUES
(1, 1, 1, '2025-01-04', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomersID` int(11) NOT NULL,
  `CustomerName` varchar(150) NOT NULL,
  `Nationality` varchar(100) DEFAULT NULL,
  `ContactNo` varchar(25) DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`CustomersID`, `CustomerName`, `Nationality`, `ContactNo`, `Email`, `gender`) VALUES
(1, 'cus1', 'sinhala', '12344567', 'testmail@test.com', 'M');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `PackageID` int(11) NOT NULL,
  `Rating` tinyint(4) NOT NULL,
  `Comments` text DEFAULT NULL
) ;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FeedbackID`, `CustomerID`, `PackageID`, `Rating`, `Comments`) VALUES
(1, 1, 1, 5, 'Superb');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `BookingID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentDate` date NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `BookingID`, `Amount`, `PaymentDate`, `PaymentMethod`) VALUES
(1, 1, 1300.00, '2025-01-04', 'Credit Card');

-- --------------------------------------------------------

--
-- Table structure for table `tourpackage`
--

CREATE TABLE `tourpackage` (
  `PackagesID` int(11) NOT NULL,
  `TourName` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Duration` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tourpackage`
--

INSERT INTO `tourpackage` (`PackagesID`, `TourName`, `Description`, `Price`, `Duration`) VALUES
(1, 'Classic Sri Lanka 7D', 'Colombo, Kandy, Sigiriya, Galle', 750.00, 7),
(2, 'Cultural Triangle 4D', 'Anuradhapura, Polonnaruwa, Sigiriya', 420.00, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`ActivityID`),
  ADD UNIQUE KEY `uq_activity_name` (`ActivityName`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `idx_booking_customer` (`CustomerID`),
  ADD KEY `idx_booking_package` (`PackagesID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomersID`),
  ADD KEY `idx_customers_email` (`Email`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `idx_feedback_customer` (`CustomerID`),
  ADD KEY `idx_feedback_package` (`PackageID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `idx_payment_booking` (`BookingID`);

--
-- Indexes for table `tourpackage`
--
ALTER TABLE `tourpackage`
  ADD PRIMARY KEY (`PackagesID`),
  ADD UNIQUE KEY `uq_tour_name` (`TourName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
  MODIFY `ActivityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomersID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tourpackage`
--
ALTER TABLE `tourpackage`
  MODIFY `PackagesID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_booking_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomersID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_package` FOREIGN KEY (`PackagesID`) REFERENCES `tourpackage` (`PackagesID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomersID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_package` FOREIGN KEY (`PackageID`) REFERENCES `tourpackage` (`PackagesID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
