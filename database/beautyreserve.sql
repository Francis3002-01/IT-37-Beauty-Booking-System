-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2026 at 06:21 AM
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
-- Database: `beautyreserve`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointmentID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `serviceID` int(11) NOT NULL,
  `appointmentDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `appointmentType` varchar(20) NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `appointmentStatus` varchar(20) NOT NULL,
  `priceAtBooking` decimal(10,2) NOT NULL,
  `totalCost` decimal(10,2) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `apt_beautician`
--

CREATE TABLE `apt_beautician` (
  `appointmentID` int(11) NOT NULL,
  `beauticianID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `apt_client`
--

CREATE TABLE `apt_client` (
  `appointmentID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `availabilityID` int(11) NOT NULL,
  `beauticianID` int(11) NOT NULL,
  `availableDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `beauticians`
--

CREATE TABLE `beauticians` (
  `beauticianID` int(11) NOT NULL,
  `beauticianType` varchar(20) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contactNo` varchar(11) NOT NULL,
  `employmentStatus` varchar(20) NOT NULL,
  `dateJoined` date NOT NULL,
  `dateSeparated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `beauticians`
--

INSERT INTO `beauticians` (`beauticianID`, `beauticianType`, `lastName`, `firstName`, `address`, `contactNo`, `employmentStatus`, `dateJoined`, `dateSeparated`) VALUES
(3001, 'Makeup Artist', 'Tejero', 'Rene', '22 Quezon St., Amigo Subd., Piapi, Dumaguete City', '09175837780', 'Active', '2026-10-11', NULL),
(3002, 'Hair Stylist', 'Jocson', 'Salvador', '07 Sta. Catalina Street, Dumaguete City, 6200 Negros Oriental', '09626743779', 'On Leave', '2026-05-01', NULL),
(3003, 'Nail Artist', 'Claws', 'Miyukie', 'Barangay Calindagan, Dumaguete City, Philippines, 6200', '09912921972', 'Inactive', '2026-08-24', '2026-10-09');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `clientID` int(11) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contactNo` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`clientID`, `lastName`, `firstName`, `address`, `contactNo`) VALUES
(2001, 'Pham', 'Hanni', 'Melbourne, Victoria, Australia', '09271527532'),
(2002, 'Edwards', 'Martin', 'Songpa-gu, Seoul, South Korea', '09270322008');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` int(11) NOT NULL,
  `appointmentID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paymentMethod` varchar(20) NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `serviceID` int(11) NOT NULL,
  `serviceName` varchar(50) NOT NULL,
  `serviceCategory` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`serviceID`, `serviceName`, `serviceCategory`, `duration`, `price`, `description`) VALUES
(1001, 'Deep Cleansing Facial', 'Face', 60, 2000.00, 'Rejuvenating facial treatment that cleanses pores, exfoliates dead skin cells, and hydrates your skin.'),
(1002, 'Hair Rebond & Treatment', 'Hair', 120, 3500.00, 'Premium hair straightening treatment combined with deep conditioning for smooth, shiny hair.'),
(1003, 'Gel Manicure & Pedicure', 'Nails', 45, 1200.00, 'Long-lasting gel polish application with full nail cleaning and relaxing hand/foot massage.'),
(1004, 'Aromatherapy Full Body Massage', 'Massage', 90, 2500.00, 'Soothing body massage with natural essential oils designed to reduce stress and physical tension.');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` int(11) NOT NULL,
  `staffType` varchar(20) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contactNo` varchar(11) NOT NULL,
  `employmentStatus` varchar(20) NOT NULL,
  `dateJoined` date NOT NULL,
  `dateSeparated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staffID`, `staffType`, `lastName`, `firstName`, `address`, `contactNo`, `employmentStatus`, `dateJoined`, `dateSeparated`) VALUES
(4001, 'Receptionist', 'Dangca', 'Gehlee', 'Las Piñas, Philippines', '09359192007', 'Active', '2026-09-26', NULL),
(4002, 'Receptionist', 'Gaspar', 'John Lawrence', 'Muntinlupa, Philippines', '09354212004', 'On Leave', '2026-10-01', NULL),
(4003, 'Receptionist', 'Laforteza', 'Sophia', 'Manila, Philippines', '09171221202', 'Inactive', '2026-08-29', '2026-10-11');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `staffID` int(11) DEFAULT NULL,
  `userName` varchar(50) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `accountStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointmentID`),
  ADD KEY `fk_appointment_user` (`userID`),
  ADD KEY `fk_appointment_service` (`serviceID`);

--
-- Indexes for table `apt_beautician`
--
ALTER TABLE `apt_beautician`
  ADD PRIMARY KEY (`appointmentID`,`beauticianID`),
  ADD KEY `fk_aptbeautician_beautician` (`beauticianID`);

--
-- Indexes for table `apt_client`
--
ALTER TABLE `apt_client`
  ADD PRIMARY KEY (`appointmentID`,`clientID`),
  ADD KEY `fk_aptclient_client` (`clientID`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`availabilityID`),
  ADD KEY `fk_availability_beauticians` (`beauticianID`);

--
-- Indexes for table `beauticians`
--
ALTER TABLE `beauticians`
  ADD PRIMARY KEY (`beauticianID`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`clientID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `fk_payment_aptclient` (`appointmentID`,`clientID`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`serviceID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `uq_user_username` (`userName`),
  ADD UNIQUE KEY `uq_user_staffID` (`staffID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7001;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `availabilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6001;

--
-- AUTO_INCREMENT for table `beauticians`
--
ALTER TABLE `beauticians`
  MODIFY `beauticianID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3004;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `clientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2003;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `serviceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4004;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5001;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_appointment_service` FOREIGN KEY (`serviceID`) REFERENCES `service` (`serviceID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointment_user` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON UPDATE CASCADE;

--
-- Constraints for table `apt_beautician`
--
ALTER TABLE `apt_beautician`
  ADD CONSTRAINT `fk_aptbeautician_appointment` FOREIGN KEY (`appointmentID`) REFERENCES `appointment` (`appointmentID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aptbeautician_beautician` FOREIGN KEY (`beauticianID`) REFERENCES `beauticians` (`beauticianID`) ON UPDATE CASCADE;

--
-- Constraints for table `apt_client`
--
ALTER TABLE `apt_client`
  ADD CONSTRAINT `fk_aptclient_appointment` FOREIGN KEY (`appointmentID`) REFERENCES `appointment` (`appointmentID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aptclient_client` FOREIGN KEY (`clientID`) REFERENCES `client` (`clientID`) ON UPDATE CASCADE;

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `fk_availability_beauticians` FOREIGN KEY (`beauticianID`) REFERENCES `beauticians` (`beauticianID`) ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_aptclient` FOREIGN KEY (`appointmentID`,`clientID`) REFERENCES `apt_client` (`appointmentID`, `clientID`) ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_staff` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
