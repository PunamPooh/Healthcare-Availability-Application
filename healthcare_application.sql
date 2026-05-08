-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 11:34 AM
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
-- Database: `healthcare_application`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `facility_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(3, 1, 6, '2026-04-23', '13:00:00', 'booked', '2026-04-22 11:09:32'),
(6, 1, 9, '2026-04-24', '10:00:00', 'booked', '2026-04-22 13:23:08');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('clinic','hospital','doctor_office') NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `opening_hours` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `type`, `address`, `latitude`, `longitude`, `phone`, `opening_hours`) VALUES
(6, 'Rigshospitalet', 'hospital', 'Blegdamsvej 9, 2100 København Ø', 55.69990000, 12.56600000, '+45 3545 3545', '24/7 - Emergency always open'),
(7, 'Bispebjerg Hospital', 'hospital', 'Bispebjerg Bakke 23, 2400 København NV', 55.71270000, 12.54280000, '+45 3863 5000', '24/7 - Emergency always open'),
(8, 'Hvidovre Hospital', 'hospital', 'Kettegård Allé 30, 2650 Hvidovre', 55.62460000, 12.48180000, '+45 3862 3862', '24/7 - Emergency always open'),
(9, 'Amager Hospital', 'hospital', 'Kastrupvej 399, 2770 Kastrup', 55.63720000, 12.64500000, '+45 3868 3868', '24/7 - Emergency always open'),
(10, 'Herlev Hospital', 'hospital', 'Borgmester Ib Juuls Vej 1, 2730 Herlev', 55.72530000, 12.44090000, '+45 3868 3868', '24/7 - Emergency always open'),
(11, 'Frederiksberg Hospital', 'hospital', 'Nordre Fasanvej 57, 2000 Frederiksberg', 55.68310000, 12.54100000, '+45 3816 3816', '24/7 - Emergency always open'),
(12, 'Ørestad Lægeklinik', 'clinic', 'Arne Jacobsens Allé 12, 2300 København S', 55.62560000, 12.57910000, '+45 3254 5454', '8AM-8PM Mon-Fri, 9AM-2PM Sat'),
(13, 'Lægerne Christianshavn', 'clinic', 'Torvegade 64, 1400 København K', 55.67340000, 12.59010000, '+45 3296 9696', '8AM-6PM Mon-Fri'),
(14, 'Vesterbro Lægehus', 'clinic', 'Vesterbrogade 39, 1620 København V', 55.67180000, 12.55690000, '+45 3324 2424', '8AM-7PM Mon-Thu, 8AM-5PM Fri'),
(15, 'Nørrebro Lægeklinik', 'clinic', 'Nørrebrogade 108, 2200 København N', 55.69140000, 12.54800000, '+45 3535 3636', '9AM-6PM Mon-Fri'),
(16, 'Østerbro Lægehus', 'clinic', 'Østerbrogade 162, 2100 København Ø', 55.70500000, 12.58500000, '+45 3526 2626', '8AM-5PM Mon-Fri'),
(17, 'Dr. Henrik Lund', 'doctor_office', 'Vester Voldgade 88, 1552 København V', 55.67610000, 12.56930000, '+45 3333 4545', '9AM-4PM Mon-Thu, 9AM-3PM Fri'),
(18, 'Dr. Mette Frederiksen', 'doctor_office', 'Øster Allé 42, 2100 København Ø', 55.69860000, 12.57900000, '+45 3525 2525', '8AM-3PM Mon-Fri'),
(19, 'Dr. Jens Hansen', 'doctor_office', 'Amagerbrogade 151, 2300 København S', 55.65630000, 12.62020000, '+45 3268 6868', '9AM-5PM Mon-Fri'),
(20, 'Vibenshus Lægehus', 'clinic', 'Vibenshus Allé 2, 2100 København Ø', 55.70880000, 12.56160000, '+45 3929 2929', '8AM-7PM Mon-Wed, 8AM-6PM Thu-Fri');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `resource_name` varchar(100) NOT NULL,
  `resource_type` enum('mri','ct_scan','xray','ultrasound','eye_exam','blood_test','vaccination','physiotherapy','dental','general_consultation') NOT NULL,
  `description` text DEFAULT NULL,
  `price_range` varchar(50) DEFAULT NULL,
  `requires_referral` tinyint(1) DEFAULT 1,
  `available_days` varchar(50) DEFAULT NULL COMMENT 'e.g., Mon,Wed,Fri',
  `available_time_start` time DEFAULT NULL,
  `available_time_end` time DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 30,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `facility_id`, `resource_name`, `resource_type`, `description`, `price_range`, `requires_referral`, `available_days`, `available_time_start`, `available_time_end`, `duration_minutes`, `is_available`) VALUES
(40, 6, 'MRI - Full Body', 'mri', 'High-field 3T MRI scanner. Full body scan including brain, spine, joints.', '3000-5000 DKK', 1, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '18:00:00', 60, 1),
(41, 6, 'CT Scan', 'ct_scan', 'Advanced CT imaging for rapid diagnosis.', '1500-3000 DKK', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '08:00:00', '20:00:00', 30, 1),
(42, 6, 'X-Ray', 'xray', 'Digital X-ray service for bones and chest.', '500-1000 DKK', 0, 'Mon,Tue,Wed,Thu,Fri,Sat', '08:00:00', '22:00:00', 15, 1),
(43, 6, 'Ultrasound', 'ultrasound', 'Abdominal, pelvic, and pregnancy ultrasound.', '800-1500 DKK', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '16:00:00', 30, 1),
(44, 7, 'MRI - Spine', 'mri', 'Full spine MRI for back pain diagnosis.', '3000-5000 DKK', 1, 'Mon,Thu,Fri', '08:00:00', '16:00:00', 60, 1),
(45, 7, 'CT - Chest', 'ct_scan', 'Chest CT for lung assessment.', '1800-3000 DKK', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', 30, 1),
(46, 7, 'Ultrasound - Pregnancy', 'ultrasound', 'Pregnancy dating and anatomy scan.', '800-1500 DKK', 1, 'Mon,Wed,Fri', '09:00:00', '15:00:00', 45, 1),
(47, 8, 'MRI - Brain', 'mri', 'Specialized brain MRI with contrast.', '3500-5500 DKK', 1, 'Mon,Wed,Fri', '09:00:00', '17:00:00', 60, 1),
(48, 8, 'X-Ray - Emergency', 'xray', '24/7 emergency X-ray service.', '400-800 DKK', 0, 'Mon,Tue,Wed,Thu,Fri,Sat,Sun', '00:00:00', '23:59:00', 15, 1),
(49, 8, 'Blood Test Lab', 'blood_test', 'Complete blood work including hormones, vitamins, infection markers.', '200-600 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '07:00:00', '15:00:00', 10, 1),
(50, 9, 'Vaccination', 'vaccination', 'Flu, COVID-19, HPV, and travel vaccines.', '150-500 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '10:00:00', '16:00:00', 20, 1),
(51, 9, 'Blood Test - Quick', 'blood_test', 'Rapid blood test for immediate results.', '300-800 DKK', 0, 'Mon,Tue,Wed,Thu,Fri,Sat', '08:00:00', '12:00:00', 15, 1),
(52, 9, 'CT Scan', 'ct_scan', 'Emergency CT for trauma cases.', '2000-3500 DKK', 1, 'Mon,Tue,Wed,Thu,Fri,Sat,Sun', '00:00:00', '23:59:00', 30, 1),
(53, 10, 'CT Angiography', 'ct_scan', 'CT scan for blood vessels and heart.', '2500-4000 DKK', 1, 'Mon,Tue,Thu', '10:00:00', '18:00:00', 45, 1),
(54, 10, 'Ultrasound - Heart', 'ultrasound', 'Echocardiography for heart assessment.', '1200-2200 DKK', 1, 'Mon,Wed,Fri', '09:00:00', '15:00:00', 45, 1),
(55, 10, 'X-Ray - Dental', 'xray', 'Dental panoramic X-ray.', '300-600 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '16:00:00', 10, 1),
(56, 11, 'Eye Examination', 'eye_exam', 'Complete eye health check, vision test, glaucoma screening.', '500-1200 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '18:00:00', 45, 1),
(57, 11, 'MRI - Knee/Shoulder', 'mri', 'Orthopedic MRI for joints.', '2500-4000 DKK', 1, 'Mon,Wed,Fri', '08:00:00', '14:00:00', 45, 1),
(58, 11, 'Physiotherapy Assessment', 'physiotherapy', 'Initial physiotherapy evaluation and treatment plan.', '400-800 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '20:00:00', 30, 1),
(59, 12, 'General Consultation', 'general_consultation', 'Standard doctor consultation for common illnesses.', '300-600 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '20:00:00', 20, 1),
(60, 12, 'Blood Test', 'blood_test', 'Routine blood work and testing.', '250-500 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '18:00:00', 15, 1),
(61, 13, 'General Consultation', 'general_consultation', 'Doctor consultation.', '350-600 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '18:00:00', 20, 1),
(62, 13, 'Vaccination', 'vaccination', 'Standard vaccinations for adults and children.', '200-400 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', 20, 1),
(63, 14, 'General Consultation', 'general_consultation', 'Family doctor consultation.', '300-500 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', 20, 1),
(64, 14, 'Physiotherapy', 'physiotherapy', 'Physical therapy and rehabilitation.', '400-700 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '19:00:00', 30, 1),
(65, 15, 'General Consultation', 'general_consultation', 'Standard consultation.', '300-550 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '20:00:00', 20, 1),
(66, 15, 'Blood Test', 'blood_test', 'Routine blood work.', '200-450 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '16:00:00', 15, 1),
(67, 16, 'General Consultation', 'general_consultation', 'Doctor consultation.', '350-600 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '18:00:00', 20, 1),
(68, 16, 'Eye Exam', 'eye_exam', 'Basic vision screening and eye health check.', '400-800 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '16:00:00', 30, 1),
(69, 17, 'General Consultation', 'general_consultation', 'Private doctor consultation.', '400-700 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '19:00:00', 20, 1),
(70, 17, 'Vaccination', 'vaccination', 'Travel and routine vaccinations.', '250-500 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '10:00:00', '16:00:00', 20, 1),
(71, 18, 'General Consultation', 'general_consultation', 'Family practice consultation.', '300-550 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '20:00:00', 20, 1),
(72, 19, 'General Consultation', 'general_consultation', 'Doctor consultation.', '350-600 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '18:00:00', 20, 1),
(73, 20, 'General Consultation', 'general_consultation', 'Standard consultation.', '300-550 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '20:00:00', 20, 1),
(74, 20, 'Physiotherapy', 'physiotherapy', 'Physical therapy sessions.', '400-750 DKK', 0, 'Mon,Tue,Wed,Thu,Fri', '08:00:00', '18:00:00', 30, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resource_bookings`
--

CREATE TABLE `resource_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked',
  `patient_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `is_admin`) VALUES
(1, 'Punam Chochangi Pun', 'punam@gmail.com', '$2y$10$CpUM6lairraKl4eQrCM3euSMVMsae/oSFjEbdhK77PccXkhfqSp5i', '2026-04-20 06:23:11', 0),
(4, 'Admin User', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-30 09:56:20', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `resource_bookings`
--
ALTER TABLE `resource_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `resource_bookings`
--
ALTER TABLE `resource_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`);

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_bookings`
--
ALTER TABLE `resource_bookings`
  ADD CONSTRAINT `resource_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `resource_bookings_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
