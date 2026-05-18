-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2026 at 06:33 AM
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
-- Database: `tourmanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `travel_date` date NOT NULL,
  `guests` int(11) NOT NULL DEFAULT 1,
  `total_price` varchar(50) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `destination_id`, `booking_date`, `travel_date`, `guests`, `total_price`, `status`, `payment_status`, `notes`, `created_at`) VALUES
(1, 1, 1, '2026-05-06 09:38:12', '2026-05-07', 1, 'Rs 15,000', 'Confirmed', 'Paid', '', '2026-05-06 04:38:12'),
(2, 1, 6, '2026-05-12 08:14:24', '2026-05-13', 5, 'Rs 30,000', 'Pending', 'Unpaid', '', '2026-05-12 03:14:24'),
(3, 1, 6, '2026-05-12 08:14:26', '2026-05-13', 5, 'Rs 30,000', 'Pending', 'Unpaid', '', '2026-05-12 03:14:26'),
(4, 1, 3, '2026-05-12 08:21:07', '2026-05-14', 1, 'Rs 18,000', 'Pending', 'Paid', '', '2026-05-12 03:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `intensity` enum('Low','Medium','High') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `environment` text DEFAULT NULL,
  `accommodation` text DEFAULT NULL,
  `activities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activities`)),
  `is_deleted` tinyint(1) DEFAULT 0,
  `category` enum('Adventure','Relaxation','Educational') DEFAULT 'Adventure',
  `gravity_level` varchar(50) DEFAULT '1.0g',
  `itinerary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`itinerary`)),
  `route_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`route_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `type`, `hero_image`, `duration`, `price`, `intensity`, `description`, `environment`, `accommodation`, `activities`, `is_deleted`, `category`, `gravity_level`, `itinerary`, `route_data`, `created_at`) VALUES
(1, 'Murree Hills', 'Hill Station', 'uploads/destinations/1774527701_muree.jpg', '3 Days', 'Rs 15,000', 'Low', 'The Queen of Hills. Experience the cool breeze, lush green pine forests, and the iconic Mall Road. Perfect for a quick family getaway.', 'Cool temperate climate, mountain air, winter snowfall.', 'Luxury resorts and cozy pine cottages.', '[\"Mall Road Trekking\", \"Chair Lift Ride\", \"Hiking in Mushkpuri\"]', 0, 'Relaxation', '1.0g (Earth Std)', '[{\"day\":1,\"morning\":\"Arrival & Check-in at Mall Road\",\"afternoon\":\"Visit to Pindi Point & Chair Lift\",\"night\":\"Dinner at local street food market\"},{\"day\":2,\"morning\":\"Trek to Mushkpuri Top\",\"afternoon\":\"Visit to Pipeline Track & Dunga Gali\",\"night\":\"Bonfire at Hotel\"}]', '{\"path\":[[33.6,73],[33.7,73.2],[33.9,73.3]],\"pois\":[{\"name\":\"Islamabad Base\",\"lat\":33.6,\"lng\":73,\"desc\":\"Departure Point\"},{\"name\":\"Pindi Point\",\"lat\":33.9,\"lng\":73.3,\"desc\":\"Highest point in Murree\"},{\"name\":\"Mall Road\",\"lat\":33.91,\"lng\":73.39,\"desc\":\"Central hub\"}]}', '2026-05-10 18:00:49'),
(2, 'Islamabad City', 'Capital City', 'uploads/destinations/1774527872_islamabad.jpg', '2 Days', 'Rs 20,000', 'Low', 'One of the most beautiful capitals in the world. Modern infrastructure meets natural beauty with the Margalla Hills as a backdrop.', 'Placid city life, proximity to nature, clean and green.', 'High-end hotels and modern guest houses.', '[\"Faisal Mosque Visit\", \"Daman-e-Koh Sightseeing\", \"Monal Dinner\"]', 0, 'Relaxation', '1.0g (Earth Std)', '[{\"day\":1,\"morning\":\"Breakfast at Monal\",\"afternoon\":\"Faisal Mosque & Daman-e-Koh\",\"night\":\"Shopping at Centaurus Mall\"},{\"day\":2,\"morning\":\"Heritage Museum Visit\",\"afternoon\":\"Rawal Lake Boating\",\"night\":\"Dinner at Saidpur Village\"}]', '{\"path\":[[33.6,73],[33.7,73.1],[33.72,73.03]],\"pois\":[{\"name\":\"Faisal Mosque\",\"lat\":33.72,\"lng\":73.03,\"desc\":\"Iconic Architecture\"},{\"name\":\"Centaurus\",\"lat\":33.7,\"lng\":73.05,\"desc\":\"Premium Shopping\"}]}', '2026-05-10 18:00:49'),
(3, 'Lahore Culture', 'Historical City', 'uploads/destinations/1774527969_lahore.jpg', '4 Days', 'Rs 18,000', 'Medium', 'The heart of Pakistan. Famous for its rich history, vibrant food culture, and the majestic Walled City.', 'Bustling urban environment, heavy historical influence.', 'Heritage hotels and urban luxury suites.', '[\"Lahore Fort Exploration\", \"Badshahi Mosque Visit\", \"Food Street Dining\"]', 0, 'Educational', '1.2g (Heavy)', '[{\"day\":1,\"morning\":\"Badshahi Mosque & Lahore Fort\",\"afternoon\":\"Wazir Khan Mosque & Anarkali\",\"night\":\"Food Street Gawalmandi\"},{\"day\":2,\"morning\":\"Wagah Border Parade\",\"afternoon\":\"Shalimar Gardens\",\"night\":\"Liberty Market Shopping\"}]', '{\"path\":[[31.5,74.3],[31.58,74.31],[31.59,74.31]],\"pois\":[{\"name\":\"Lahore Fort\",\"lat\":31.58,\"lng\":74.31,\"desc\":\"Mughal Heritage\"},{\"name\":\"Badshahi Mosque\",\"lat\":31.59,\"lng\":74.31,\"desc\":\"Historical landmark\"}]}', '2026-05-10 18:00:49'),
(4, 'Swat Valley', 'Natural Paradise', 'uploads/destinations/1774528026_swat.jpg', '5 Days', 'Rs 35,000', 'High', 'The Switzerland of the East. Crystal clear waters of the Swat River and snow-capped peaks of the Hindu Kush.', 'Alpine valley, rushing rivers, serene meadows.', 'Riverside camps and mountain view hotels.', '[\"Fizagat Park Boating\", \"Malam Jabba Skiing\", \"Kalam Valley Tour\"]', 0, 'Adventure', '0.9g (Light)', '[{\"day\":1,\"morning\":\"Drive to Mingora\",\"afternoon\":\"Visit to White Palace Marghazar\",\"night\":\"Relaxation at Riverside\"},{\"day\":2,\"morning\":\"Trek to Malam Jabba\",\"afternoon\":\"Skiing & Chairlift\",\"night\":\"Local Trout Dinner\"}]', '{\"path\":[[33.6,73],[34.2,72],[34.7,72.3]],\"pois\":[{\"name\":\"Mingora\",\"lat\":34.77,\"lng\":72.36,\"desc\":\"Main city of Swat\"},{\"name\":\"Marghazar\",\"lat\":34.69,\"lng\":72.34,\"desc\":\"White Palace\"}]}', '2026-05-10 18:00:49'),
(5, 'Naran & Kaghan', 'Mountain Retreat', 'uploads/destinations/1774528079_naran.jpg', '6 Days', 'Rs 45,000', 'High', 'Gateway to the giants. Home to the legendary Saif-ul-Malook Lake and the Babusar Pass.', 'High-altitude mountain valley, glacier views.', 'Lakeside camps and alpine resorts.', '[\"Jeep Trek to Saif-ul-Malook\", \"Lulusar Lake Sightseeing\", \"Rafting in Kunhar River\"]', 0, 'Adventure', '1.0g (Earth Std)', '[{\"day\":1,\"morning\":\"Drive through Balakot to Naran\",\"afternoon\":\"Visit to Saif-ul-Malook Lake\",\"night\":\"Dinner in Naran Bazaar\"},{\"day\":2,\"morning\":\"Visit to Lulusar Lake\",\"afternoon\":\"Babusar Top Adventure\",\"night\":\"Lakeside BBQ in Naran\"}]', '{\"path\":[[33.6,73],[34.3,73.4],[34.9,73.6]],\"pois\":[{\"name\":\"Saif-ul-Malook\",\"lat\":34.87,\"lng\":73.69,\"desc\":\"Legendary Lake\"},{\"name\":\"Babusar Top\",\"lat\":35.14,\"lng\":74.04,\"desc\":\"High altitude pass\"}]}', '2026-05-10 18:00:49'),
(6, 'Malam Jabba', 'Ski Resort', 'uploads/destinations/1774528769_malam.jpg', '3 Days', 'Rs 30,000', 'Medium', 'The premier ski destination of Pakistan. Enjoy winter sports and stunning panoramic views of the Swat valley.', 'Sub-zero winter temperatures, pristine snow slopes.', 'Ski-in/ski-out resorts and luxury lodges.', '[\"Skiing & Snowboarding\", \"Zip Lining\", \"Cable Car Ride\"]', 0, 'Adventure', '0.7g (Alpine)', '[{\"day\":1,\"morning\":\"Arrival & Ski Resort Check-in\",\"afternoon\":\"Chairlift Ride & Sightseeing\",\"night\":\"Cozy dinner at resort\"},{\"day\":2,\"morning\":\"Skiing & Snowboarding\",\"afternoon\":\"Zipline Adventure\",\"night\":\"Swat Valley views at sunset\"}]', '{\"path\":[[33.6,73],[34.7,72.3],[34.8,72.5]],\"pois\":[{\"name\":\"Ski Resort\",\"lat\":34.8,\"lng\":72.57,\"desc\":\"Adventure hub\"},{\"name\":\"Chair Lift\",\"lat\":34.79,\"lng\":72.58,\"desc\":\"Scenic views\"}]}', '2026-05-10 18:00:49');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `template_key` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `variables` text DEFAULT NULL COMMENT 'Comma-separated list of available variables',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_key`, `subject`, `body`, `variables`, `updated_at`) VALUES
(1, 'registration_approval', 'Welcome to the Nebula! Your Account is Approved', '<h1>Hi {{user_name}},</h1><p>Your registration for our Tour Management platform has been approved. You can now start booking your interstellar adventures!</p><p>Best regards,<br>Mission Control</p>', 'user_name', '2026-05-10 18:41:24'),
(2, 'booking_confirmation', 'Mission Confirmed: {{tour_name}}', '<h1>Hi {{user_name}},</h1><p>Your booking for <strong>{{tour_name}}</strong> on {{travel_date}} is confirmed!</p><p>Guests: {{guests}}<br>Total Paid: {{price}}</p><p>Get ready for your journey!</p>', 'user_name,tour_name,travel_date,guests,price', '2026-05-10 18:41:24'),
(3, 'trip_reminder', 'Reminder: Your Journey to {{tour_name}} Starts Soon!', '<h1>Hi {{user_name}},</h1><p>This is a reminder that your mission to {{tour_name}} starts in 48 hours on {{travel_date}}.</p><p>Please ensure all your gear is ready.</p>', 'user_name,tour_name,travel_date', '2026-05-10 18:41:24');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `flag_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_rtl` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `code`, `flag_url`, `is_active`, `is_rtl`, `created_at`) VALUES
(1, 'English', 'en', 'https://flagcdn.com/w80/us.png', 1, 0, '2026-05-10 19:12:07'),
(2, 'French', 'fr', 'https://flagcdn.com/w80/fr.png', 1, 0, '2026-05-10 19:12:07'),
(3, 'Spanish', 'es', 'https://flagcdn.com/w80/es.png', 1, 0, '2026-05-10 19:12:07'),
(4, 'Chinese', 'zh-CN', 'https://flagcdn.com/w80/cn.png', 1, 0, '2026-05-10 19:12:07'),
(5, 'German', 'de', 'https://flagcdn.com/w80/de.png', 1, 0, '2026-05-10 19:12:07'),
(6, 'Urdu', 'ur', 'https://flagcdn.com/w80/pk.png', 1, 1, '2026-05-10 19:12:07'),
(7, 'Punjabi', 'pa', 'https://flagcdn.com/w80/pk.png', 1, 1, '2026-05-10 19:12:07'),
(8, 'Arabic', 'ar', 'https://flagcdn.com/w80/sa.png', 1, 1, '2026-05-10 19:12:07'),
(9, 'Russian', 'ru', 'https://flagcdn.com/w80/ru.png', 1, 0, '2026-05-10 19:12:07'),
(10, 'Japanese', 'ja', 'https://flagcdn.com/w80/jp.png', 1, 0, '2026-05-10 19:12:07');

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `template_key` varchar(50) DEFAULT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `status` enum('Sent','Failed','Pending') DEFAULT 'Sent',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_logs`
--

INSERT INTO `notification_logs` (`id`, `user_id`, `template_key`, `recipient_email`, `subject`, `body`, `status`, `error_message`, `sent_at`) VALUES
(1, NULL, 'booking_confirmation', 'mhussnain7565@gmail.com', 'Mission Confirmed: Example Mission', NULL, 'Sent', NULL, '2026-05-10 18:44:40'),
(2, NULL, 'trip_reminder', 'mhussnain7565@gmail.com', 'Reminder: Your Journey to Example Mission Starts Soon!', NULL, 'Failed', 'PHP mail() function failed. Check server configuration.', '2026-05-10 18:55:33'),
(3, NULL, 'booking_confirmation', 'malikahsanab617@gmail.com', 'Mission Confirmed: Example Mission', NULL, 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-10 18:56:51'),
(4, NULL, 'booking_confirmation', 'mhussnain7565@gmail.com', 'Mission Confirmed: Example Mission', '<h1>Hi Admin Test,</h1><p>Your booking for <strong>Example Mission</strong> on 2026-05-10 is confirmed!</p><p>Guests: 1<br>Total Paid: Rs 0</p><p>Get ready for your journey!</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-10 19:00:00'),
(5, NULL, 'booking_confirmation', 'mhussnain7565@gmail.com', 'Mission Confirmed: Example Mission', '<h1>Hi Admin Test,</h1><p>Your booking for <strong>Example Mission</strong> on 2026-05-10 is confirmed!</p><p>Guests: 1<br>Total Paid: Rs 0</p><p>Get ready for your journey!</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-10 19:01:36'),
(6, 1, 'booking_confirmation', 'admin@sys.com', 'Mission Confirmed: Malam Jabba', '<h1>Hi Root Admin,</h1><p>Your booking for <strong>Malam Jabba</strong> on 2026-05-13 is confirmed!</p><p>Guests: 5<br>Total Paid: Rs 30,000</p><p>Get ready for your journey!</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-12 03:14:26'),
(7, 1, 'booking_confirmation', 'admin@sys.com', 'Mission Confirmed: Malam Jabba', '<h1>Hi Root Admin,</h1><p>Your booking for <strong>Malam Jabba</strong> on 2026-05-13 is confirmed!</p><p>Guests: 5<br>Total Paid: Rs 30,000</p><p>Get ready for your journey!</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-12 03:14:28'),
(8, 1, 'booking_confirmation', 'admin@sys.com', 'Mission Confirmed: Lahore Culture', '<h1>Hi Root Admin,</h1><p>Your booking for <strong>Lahore Culture</strong> on 2026-05-14 is confirmed!</p><p>Guests: 1<br>Total Paid: Rs 18,000</p><p>Get ready for your journey!</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-12 03:21:09'),
(9, NULL, 'booking_confirmation', 'mhussnain7565@gmail.com', 'Mission Confirmed: Example Mission', '<h1>Hi Admin Test,</h1><p>Your booking for <strong>Example Mission</strong> on 2026-05-12 is confirmed!</p><p>Guests: 1<br>Total Paid: Rs 0</p><p>Get ready for your journey!</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-12 03:24:29'),
(10, NULL, 'registration_approval', 'mhussnain7565@gmail.com', 'Welcome to the Nebula! Your Account is Approved', '<h1>Hi Admin Test,</h1><p>Your registration for our Tour Management platform has been approved. You can now start booking your interstellar adventures!</p><p>Best regards,<br>Mission Control</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-12 03:25:26'),
(11, NULL, 'trip_reminder', 'mhussnain7565@gmail.com', 'Reminder: Your Journey to Example Mission Starts Soon!', '<h1>Hi Admin Test,</h1><p>This is a reminder that your mission to Example Mission starts in 48 hours on 2026-05-12.</p><p>Please ensure all your gear is ready.</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-12 03:25:41'),
(12, NULL, 'registration_approval', 'test@example.com', 'Welcome to the Nebula! Your Account is Approved', '<h1>Hi Test Admin,</h1><p>Your registration for our Tour Management platform has been approved. You can now start booking your interstellar adventures!</p><p>Best regards,<br>Mission Control</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-13 03:51:15'),
(13, NULL, 'registration_approval', 'test4@example.com', 'Welcome to the Nebula! Your Account is Approved', '<h1>Hi Test Admin,</h1><p>Your registration for our Tour Management platform has been approved. You can now start booking your interstellar adventures!</p><p>Best regards,<br>Mission Control</p>', 'Failed', 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)', '2026-05-13 03:57:01');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_method_type` enum('Bank Transfer','Stripe','PayPal','Other') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `amount`, `payment_method_id`, `payment_method_type`, `transaction_id`, `proof_file`, `status`, `notes`, `created_at`) VALUES
(1, 1, 1, 'Rs 15,000', NULL, 'Stripe', NULL, '', 'Verified', NULL, '2026-05-06 04:54:45'),
(2, 4, 1, 'Rs 18,000', NULL, 'Stripe', NULL, '', 'Verified', NULL, '2026-05-12 03:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `iban` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `destination_id`, `rating`, `comment`, `photo_path`, `created_at`) VALUES
(1, 1, 6, 5, 'excellent', NULL, '2026-05-12 03:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `role_access`
--

CREATE TABLE `role_access` (
  `role_key` varchar(50) NOT NULL,
  `page_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_access`
--

INSERT INTO `role_access` (`role_key`, `page_id`) VALUES
('admin', 6),
('admin', 7),
('admin', 8),
('admin', 15),
('admin', 16),
('admin', 18),
('admin', 22),
('admin', 23),
('admin', 24),
('admin', 25),
('admin', 26),
('admin', 28),
('admin', 29),
('admin', 30),
('admin', 31),
('admin', 33),
('admin', 34),
('admin', 35),
('admin', 36),
('admin', 37),
('admin', 38),
('admin', 39),
('employee', 39),
('librarian', 6),
('librarian', 7),
('librarian', 8),
('librarian', 15),
('librarian', 16),
('librarian', 18),
('librarian', 22),
('librarian', 23),
('librarian', 24),
('librarian', 25),
('librarian', 26),
('librarian', 29),
('librarian', 31),
('librarian', 34),
('librarian', 35),
('librarian', 37),
('librarian', 39),
('salesperson', 6),
('salesperson', 7),
('salesperson', 8),
('salesperson', 15),
('salesperson', 16),
('salesperson', 18),
('salesperson', 22),
('salesperson', 23),
('salesperson', 24),
('salesperson', 25),
('salesperson', 26),
('salesperson', 29),
('salesperson', 31),
('salesperson', 34),
('salesperson', 35),
('salesperson', 37),
('salesperson', 39),
('student', 6),
('student', 7),
('student', 8),
('student', 15),
('student', 16),
('student', 18),
('student', 22),
('student', 23),
('student', 24),
('student', 25),
('student', 26),
('student', 29),
('student', 31),
('student', 34),
('student', 35),
('student', 37),
('student', 39),
('super_admin', 1),
('super_admin', 2),
('super_admin', 3),
('super_admin', 4),
('super_admin', 5),
('super_admin', 6),
('super_admin', 7),
('super_admin', 8),
('super_admin', 15),
('super_admin', 16),
('super_admin', 18),
('super_admin', 22),
('super_admin', 23),
('super_admin', 24),
('super_admin', 25),
('super_admin', 26),
('super_admin', 28),
('super_admin', 29),
('super_admin', 30),
('super_admin', 31),
('super_admin', 33),
('super_admin', 34),
('super_admin', 35),
('super_admin', 36),
('super_admin', 37),
('super_admin', 38),
('super_admin', 39),
('suspended', 6),
('suspended', 7),
('suspended', 8),
('suspended', 15),
('suspended', 16),
('suspended', 18),
('suspended', 22),
('suspended', 23),
('suspended', 24),
('suspended', 25),
('suspended', 26),
('suspended', 31),
('suspended', 34),
('suspended', 35),
('suspended', 37),
('suspended', 39),
('user', 39);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('footer_text', '© 2026 Universal Systems. All rights reserved.'),
('system_logo', 'https://cdn-icons-png.flaticon.com/512/906/906343.png'),
('system_name', 'Tour Management');

-- --------------------------------------------------------

--
-- Table structure for table `sys_pages`
--

CREATE TABLE `sys_pages` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `page_name` varchar(100) NOT NULL,
  `page_url` varchar(255) DEFAULT '#',
  `icon_class` varchar(50) DEFAULT 'bi bi-circle',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sys_pages`
--

INSERT INTO `sys_pages` (`id`, `parent_id`, `page_name`, `page_url`, `icon_class`, `sort_order`) VALUES
(1, 0, 'Dashboard', 'index.php', 'bi bi-grid-1x2-fill', 1),
(2, 0, 'System Management', '#', 'bi bi-shield-lock-fill', 2),
(3, 2, 'Manage Users', 'dashboards/super_admin/manage_users.php', 'bi bi-people-fill', 1),
(4, 2, 'Manage Roles', 'dashboards/super_admin/manage_roles.php', 'bi bi-person-badge-fill', 2),
(5, 2, 'Manage Pages', 'dashboards/super_admin/manage_pages.php', 'bi bi-files', 3),
(6, 0, 'Explore', '#', 'bi bi-compass-fill', 3),
(7, 6, 'Destinations', 'explore/destinations.php', 'bi bi-geo-alt-fill', 1),
(8, 6, 'Manage Destinations', 'dashboards/super_admin/manage_destinations.php', 'bi bi-gear-wide-connected', 7),
(16, 6, 'Comparison Tool', 'explore/comparison.php', 'bi bi-layers-fill', 5),
(18, 6, 'Interactive Map', 'explore/interactive_map.php', 'bi bi-map-fill', 6),
(19, 6, 'Adventure Tours', 'explore/destinations.php?category=Adventure', 'bi bi-fire', 2),
(20, 6, 'Relaxation Tours', 'explore/destinations.php?category=Relaxation', 'bi bi-water', 3),
(21, 6, 'Educational Tours', 'explore/destinations.php?category=Educational', 'bi bi-mortarboard-fill', 4),
(22, 6, 'Adventure Tours', 'explore/destinations.php?category=Adventure', 'bi bi-fire', 2),
(23, 6, 'Relaxation Tours', 'explore/destinations.php?category=Relaxation', 'bi bi-water', 3),
(24, 6, 'Educational Tours', 'explore/destinations.php?category=Educational', 'bi bi-mortarboard-fill', 4),
(25, 6, 'Details of Trip', 'explore/destinations.php', 'bi bi-journal-text', 8),
(26, 0, 'Booking & Payment', '#', 'bi bi-calendar-check-fill', 0),
(28, 26, 'Manage Bookings', 'dashboards/super_admin/manage_bookings.php', 'bi bi-clipboard-check-fill', 1),
(29, 26, 'My Bookings', 'dashboards/user/my_bookings.php', 'bi bi-journal-check', 2),
(30, 26, 'Payment System', 'dashboards/super_admin/manage_payments.php', 'bi bi-bank', 3),
(31, 0, 'Review\'s', '#', 'bi bi-star-fill', 6),
(33, 31, 'Current Status', 'dashboards/super_admin/current_status.php', 'bi bi-activity', 1),
(34, 31, 'Requirement', 'explore/reviews.php', 'bi bi-card-checklist', 2),
(35, 0, 'Booking Calendar', 'dashboards/super_admin/manage_availability.php', 'bi bi-calendar3-range-fill', 1),
(36, 35, 'Tour Availability', 'dashboards/super_admin/manage_availability.php', 'bi bi-calendar-check', 1),
(37, 0, 'Notification System', '#', 'bi bi-bell-fill', 8),
(38, 37, 'Email Triggers', 'dashboards/super_admin/manage_notifications.php', 'bi bi-envelope-check', 1),
(39, 0, 'Languages', 'explore/languages.php', 'bi bi-translate', 9);

-- --------------------------------------------------------

--
-- Table structure for table `sys_roles`
--

CREATE TABLE `sys_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_key` varchar(50) NOT NULL,
  `is_system_role` tinyint(1) DEFAULT 0 COMMENT '1=Cannot Delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sys_roles`
--

INSERT INTO `sys_roles` (`id`, `role_name`, `role_key`, `is_system_role`) VALUES
(1, 'Super Admin', 'super_admin', 1),
(2, 'Administrator', 'admin', 0),
(3, 'Student', 'student', 0),
(4, 'Suspended', 'suspended', 1),
(5, 'Librarian', 'librarian', 0),
(6, 'SalesPerson', 'salesperson', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tour_availability`
--

CREATE TABLE `tour_availability` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `tour_date` date NOT NULL,
  `total_slots` int(11) NOT NULL DEFAULT 10,
  `booked_slots` int(11) NOT NULL DEFAULT 0,
  `status` enum('Open','Closed','Full') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tour_availability`
--

INSERT INTO `tour_availability` (`id`, `destination_id`, `tour_date`, `total_slots`, `booked_slots`, `status`, `created_at`) VALUES
(1, 6, '2026-05-13', 10, 10, 'Full', '2026-05-12 03:13:48'),
(2, 3, '2026-05-14', 10, 1, 'Open', '2026-05-12 03:20:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `identity_no` varchar(50) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `identity_no`, `registration_no`, `is_active`) VALUES
(1, 'Root Admin', 'admin@sys.com', '$2y$10$RfTBViBJzdDbXHsutM6PTes01/TyPyM/Bwy1SvaLTZszV/I8UqBU2', 'super_admin', '12345-1234567-1', 'ADM-001', 1),
(2, 'Hadi', 'hadibhatti75@gmail.com', '$2y$10$x5aDAcnbRTSUgxvBlIjr2u789moyEJ1QDeoZ2S3AV0arfATOrmfvG', 'salesperson', NULL, NULL, 1),
(3, 'Test Admin', 'test@example.com', '$2y$10$JQVZqZN3gdoYF2smVeRG0.l8RWU18ymD0fy36MhZBG6gF0dwmoNa6', 'admin', '1234567890123', '12345', 1),
(4, 'Test Admin', 'test4@example.com', '$2y$10$O6tfhy2TAvbHjsCD4J7gv.px23DrL2bnWzCvUV0R/y.yDWZCCTqf.', 'student', '1111111111111', '11111', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_key` (`template_key`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `role_access`
--
ALTER TABLE `role_access`
  ADD PRIMARY KEY (`role_key`,`page_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `sys_pages`
--
ALTER TABLE `sys_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_roles`
--
ALTER TABLE `sys_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_key` (`role_key`);

--
-- Indexes for table `tour_availability`
--
ALTER TABLE `tour_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `destination_id` (`destination_id`,`tour_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_email` (`email`),
  ADD UNIQUE KEY `idx_identity` (`identity_no`),
  ADD UNIQUE KEY `idx_reg_no` (`registration_no`),
  ADD KEY `role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sys_pages`
--
ALTER TABLE `sys_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `sys_roles`
--
ALTER TABLE `sys_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tour_availability`
--
ALTER TABLE `tour_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tour_availability`
--
ALTER TABLE `tour_availability`
  ADD CONSTRAINT `tour_availability_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
