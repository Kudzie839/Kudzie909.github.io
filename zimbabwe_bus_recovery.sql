-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 22, 2025 at 07:35 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zimbabwe_bus_recovery`
--

-- --------------------------------------------------------

--
-- Table structure for table `bus_companies`
--

DROP TABLE IF EXISTS `bus_companies`;
CREATE TABLE IF NOT EXISTS `bus_companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bus_companies`
--

INSERT INTO `bus_companies` (`id`, `name`, `created_at`) VALUES
(1, 'Intercape', '2025-05-12 12:30:21'),
(2, 'Greyhound', '2025-05-12 12:30:21'),
(3, 'ZUPCO', '2025-05-12 12:30:21'),
(4, 'Munenzva Bus Services', '2025-05-12 12:30:21'),
(5, 'Mzansi Express', '2025-05-12 12:30:21'),
(6, 'Eagle Liner', '2025-05-12 12:30:21'),
(7, 'CAG Travellers Coaches', '2025-05-12 12:30:21'),
(8, 'Tombs Motorways', '2025-05-12 12:30:21'),
(9, 'Chihwa Bus Services', '2025-05-12 12:30:21'),
(10, 'Wangu Investments', '2025-05-12 12:30:21'),
(11, 'Inter Africa', '2025-05-12 12:30:21'),
(12, 'Kukura Kurerwa', '2025-05-12 12:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_read`) VALUES
(1, 'James Banda', 'james.banda@gmail.com', 'Lost Luggage Inquiry', 'I lost my luggage on a ZUPCO bus from Harare to Bulawayo last week. How can I check if it has been found?', '2025-05-12 12:30:47', 0),
(2, 'Mary Shumba', 'mary.shumba@yahoo.com', 'Claiming Process', 'I saw my bag in your found items database. What documents do I need to bring to claim it?', '2025-05-12 12:30:47', 1),
(3, 'Thomas Gumbo', 'thomas.gumbo@gmail.com', 'Job Application', 'I am interested in applying for a position at your company. Do you have any vacancies available?', '2025-05-12 12:30:47', 0),
(4, 'Patricia Nyoni', 'patricia.nyoni@outlook.com', 'Feedback on Service', 'I recently recovered my lost luggage through your service. The process was smooth and efficient. Thank you!', '2025-05-12 12:30:47', 1),
(5, 'Samuel Dube', 'samuel.dube@gmail.com', 'Bus Company Partnership', 'I represent a new bus company and would like to discuss partnership opportunities with your organization.', '2025-05-12 12:30:47', 0),
(6, 'Kudzaiishe Chirapu', 'kudzaiishechirapu@gmail.com', 'Luggage', 'My report is taking too long to be assessed.', '2025-05-12 12:38:19', 0);

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--

DROP TABLE IF EXISTS `found_items`;
CREATE TABLE IF NOT EXISTS `found_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_found` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text NOT NULL,
  `bus_company_id` int NOT NULL,
  `route` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('Found','Claimed') DEFAULT 'Found',
  `claimed_by` varchar(100) DEFAULT NULL,
  `claimer_contact` varchar(50) DEFAULT NULL,
  `claimer_email` varchar(100) DEFAULT NULL,
  `claimer_address` text,
  `claimer_photo` varchar(255) DEFAULT NULL,
  `claimed_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bus_company_id` (`bus_company_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `found_items`
--

INSERT INTO `found_items` (`id`, `date_found`, `description`, `bus_company_id`, `route`, `location`, `status`, `claimed_by`, `claimer_contact`, `claimer_email`, `claimer_address`, `claimer_photo`, `claimed_date`) VALUES
(1, '2025-05-12 12:22:02', 'Black suitcase with red tag', 1, 'Harare to Bulawayo', 'Bulawayo Terminal', 'Claimed', 'Kudzaiishe Chirapu', '0789357809', 'kudzaiishechirapu@gmail.com', '568 Kotwa,Mudzi', 'uploads/claims/claim_photo_6822f5ca1c710.jpg', '2025-05-13 07:33:30'),
(2, '2025-05-12 12:22:02', 'Blue backpack with laptop inside', 3, 'Mutare to Harare', 'Harare Main Terminal', 'Found', NULL, NULL, NULL, NULL, NULL, NULL),
(3, '2025-05-12 12:22:02', 'Brown leather duffel bag', 4, 'Harare to Victoria Falls', 'Victoria Falls Office', 'Claimed', 'Robert Dube', NULL, NULL, NULL, NULL, '2025-05-03 12:30:00'),
(4, '2025-05-12 12:22:02', 'Yellow suitcase with black stripes', 1, 'Bulawayo to Harare', 'Harare Main Terminal', 'Found', NULL, NULL, NULL, NULL, NULL, NULL),
(5, '2025-05-12 12:22:02', 'Red and black sports bag', 5, 'Harare to Masvingo', 'Masvingo Office', 'Found', NULL, NULL, NULL, NULL, NULL, NULL),
(6, '2025-05-12 12:22:02', 'Black laptop bag with company logo', 3, 'Harare to Mutare', 'Mutare Terminal', 'Found', NULL, NULL, NULL, NULL, NULL, NULL),
(7, '2025-05-12 12:22:02', 'Red handbag with gold chain', 8, 'Gweru to Harare', 'Harare Main Terminal', 'Found', NULL, NULL, NULL, NULL, NULL, NULL),
(8, '2025-05-12 12:22:02', 'Green backpack with school books', 6, 'Bulawayo to Gweru', 'Gweru Terminal', 'Claimed', 'Tatenda Moyo', NULL, NULL, NULL, NULL, '2025-05-07 08:15:00'),
(9, '2025-05-12 12:22:02', 'Small brown suitcase', 2, 'Harare to Bulawayo', 'Bulawayo Terminal', 'Found', NULL, NULL, NULL, NULL, NULL, NULL),
(10, '2025-05-12 12:22:02', 'Black camera bag with Canon logo', 9, 'Victoria Falls to Harare', 'Harare Main Terminal', 'Found', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lost_items`
--

DROP TABLE IF EXISTS `lost_items`;
CREATE TABLE IF NOT EXISTS `lost_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `report_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `bus_company_id` int NOT NULL,
  `route_traveled` varchar(255) NOT NULL,
  `travel_date` date NOT NULL,
  `item_description` text NOT NULL,
  `identifying_features` text NOT NULL,
  `ticket_photo` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Found','Not Found','Claimed') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `bus_company_id` (`bus_company_id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lost_items`
--

INSERT INTO `lost_items` (`id`, `report_date`, `full_name`, `email`, `phone`, `bus_company_id`, `route_traveled`, `travel_date`, `item_description`, `identifying_features`, `ticket_photo`, `status`) VALUES
(1, '2025-05-12 12:22:41', 'John Moyo', 'john.moyo@gmail.com', '+263 77 123 4567', 1, 'Harare to Bulawayo', '2025-04-28', 'Black suitcase with red tag', 'Red luggage tag with name, combination lock on zipper', NULL, 'Pending'),
(2, '2025-05-12 12:22:41', 'Sarah Ncube', 'sarah.ncube@yahoo.com', '+263 77 234 5678', 3, 'Mutare to Harare', '2025-04-30', 'Blue backpack with laptop inside', 'Dell laptop, university textbooks, blue Adidas backpack', NULL, 'Found'),
(3, '2025-05-12 12:22:41', 'Robert Dube', 'robert.dube@outlook.com', '+263 77 345 6789', 4, 'Harare to Victoria Falls', '2025-05-01', 'Brown leather duffel bag', 'Leather tag with initials RD, contains formal clothes', NULL, 'Claimed'),
(4, '2025-05-12 12:22:41', 'Grace Mpofu', 'grace.mpofu@gmail.com', '+263 77 456 7890', 7, 'Bulawayo to Gweru', '2025-05-02', 'Green travel bag with wheels', 'Name tag inside, contains women\'s clothing and toiletries', NULL, 'Not Found'),
(5, '2025-05-12 12:22:41', 'Michael Sibanda', 'michael.sibanda@yahoo.com', '+263 77 567 8901', 5, 'Harare to Masvingo', '2025-05-03', 'Red and black sports bag', 'Nike logo, contains sports equipment and shoes', NULL, 'Pending'),
(6, '2025-05-12 12:22:41', 'Tendai Moyo', 'tendai.moyo@gmail.com', '+263 77 678 9012', 1, 'Bulawayo to Harare', '2025-05-04', 'Yellow suitcase with black stripes', 'Combination lock, luggage tag with contact details', NULL, 'Found'),
(7, '2025-05-12 12:22:41', 'David Mutasa', 'david.mutasa@outlook.com', '+263 77 789 0123', 3, 'Harare to Mutare', '2025-05-05', 'Black laptop bag with company logo', 'HP laptop, business documents, company logo on front', NULL, 'Pending'),
(8, '2025-05-12 12:22:41', 'Faith Chigwida', 'faith.chigwida@gmail.com', '+263 77 890 1234', 6, 'Masvingo to Bulawayo', '2025-05-06', 'Purple medium-sized suitcase', 'Broken wheel, name tag with contact details', NULL, 'Not Found'),
(9, '2025-05-12 12:22:41', 'Peter Ndlovu', 'peter.ndlovu@yahoo.com', '+263 77 901 2345', 2, 'Harare to Beitbridge', '2025-05-07', 'Grey backpack with camera equipment', 'Canon camera, lenses, memory cards, tripod', NULL, 'Found'),
(10, '2025-05-12 12:22:41', 'Chipo Mugabe', 'chipo.mugabe@gmail.com', '+263 77 012 3456', 8, 'Gweru to Harare', '2025-05-08', 'Red handbag with gold chain', 'Contains wallet, makeup, and personal documents', NULL, 'Found');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','passenger') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`, `last_login`) VALUES
(1, 'Kudzaiishe Chirapu', 'kudzaiishechirapu@gmail.com', '$2y$10$lX5Lr7/BPjMPsAF2UOHO4ukYZmO43WcusBslP8A3V4MVgw7LzTFT2', 'passenger', '2025-05-13 09:47:46', NULL),
(2, 'Kuzivakwashe Chirapu', 'kuzivakwashechirapu@gmail.com', '$2y$10$JJJM4wPupRMFs223Ce4ESuUqD6V4L5YYvSyo3Ml5ZKaAZ1OWu4M4i', 'admin', '2025-05-13 09:52:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deleted_reports`
--

DROP TABLE IF EXISTS `deleted_reports`;
CREATE TABLE IF NOT EXISTS `deleted_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `original_id` int NOT NULL,
  `report_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `bus_company_id` int NOT NULL,
  `route_traveled` varchar(255) NOT NULL,
  `travel_date` date NOT NULL,
  `item_description` text NOT NULL,
  `identifying_features` text NOT NULL,
  `ticket_photo` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Found','Not Found','Claimed') DEFAULT 'Pending',
  `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bus_company_id` (`bus_company_id`),
  KEY `deleted_by` (`deleted_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
