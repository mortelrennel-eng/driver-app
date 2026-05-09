-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: eurotaxi
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(191) DEFAULT NULL,
  `user_role` varchar(191) DEFAULT NULL,
  `module` varchar(191) DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `subject_type` varchar(191) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `details` text DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(191) DEFAULT NULL,
  `user_agent` varchar(191) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_logs_module_index` (`module`),
  KEY `activity_logs_user_id_index` (`user_id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boundaries`
--

DROP TABLE IF EXISTS `boundaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boundaries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `expected_driver_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `boundary_amount` decimal(10,2) NOT NULL,
  `actual_boundary` decimal(10,2) DEFAULT NULL,
  `damage_payment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `debt_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `debt_balance_snapshot` decimal(10,2) NOT NULL DEFAULT 0.00,
  `debt_payment` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_extra_driver` tinyint(1) NOT NULL DEFAULT 0,
  `is_absent` tinyint(1) NOT NULL DEFAULT 0,
  `vehicle_damaged` tinyint(1) NOT NULL DEFAULT 0,
  `shortage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `excess` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','excess','shortage') DEFAULT 'pending',
  `has_incentive` tinyint(1) NOT NULL DEFAULT 1,
  `counted_for_incentive` tinyint(1) NOT NULL DEFAULT 1,
  `incentive_released_at` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `boundaries_unit_id_date_index` (`unit_id`,`date`),
  KEY `boundaries_driver_id_date_index` (`driver_id`,`date`),
  KEY `boundaries_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boundaries`
--

LOCK TABLES `boundaries` WRITE;
/*!40000 ALTER TABLE `boundaries` DISABLE KEYS */;
INSERT INTO `boundaries` VALUES (1,160,65,NULL,'2026-04-13',1100.00,500.00,0.00,0.00,0.00,0.00,0,0,0,600.00,0.00,'shortage',1,1,NULL,NULL,'2026-04-12 15:52:35','2026-04-12 15:52:35',18,18,NULL),(2,112,1,NULL,'2026-04-13',550.00,550.00,0.00,0.00,0.00,0.00,0,0,0,0.00,0.00,'paid',1,1,NULL,NULL,'2026-04-12 17:00:32','2026-04-12 17:00:32',18,18,NULL),(3,114,18,NULL,'2026-04-13',1200.00,1200.00,0.00,0.00,0.00,0.00,0,0,0,0.00,0.00,'paid',1,1,NULL,'EXTRA','2026-04-12 18:03:49','2026-04-12 18:03:49',18,18,NULL),(4,2,98,NULL,'2026-04-13',1100.00,1100.00,0.00,0.00,0.00,0.00,1,0,0,0.00,0.00,'paid',1,1,NULL,NULL,'2026-04-12 18:21:25','2026-04-12 18:21:25',18,18,NULL),(5,160,64,65,'2026-04-14',0.00,0.00,0.00,0.00,0.00,0.00,0,0,1,0.00,0.00,'paid',0,1,NULL,'oo [Automatic Violation: Late Boundary (Past 10:00 AM)] [Automatic Violation: Vehicle Damaged] [Unit Sent to Maintenance - Shift Schedule Paused (No Boundary)]','2026-04-14 02:46:34','2026-04-14 04:09:33',18,18,NULL),(6,133,29,29,'2026-04-14',0.00,0.00,0.00,0.00,0.00,0.00,0,0,1,0.00,0.00,'paid',0,1,NULL,'wadawwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww [Automatic Violation: Late Boundary (Past 10:00 AM)] [Automatic Violation: Vehicle Damaged] [Unit Sent to Maintenance - Shift Schedule Paused (No Boundary)]','2026-04-14 04:20:59','2026-04-14 04:20:59',18,18,NULL),(7,1,75,NULL,'2026-04-14',0.00,0.00,0.00,0.00,0.00,0.00,1,0,1,0.00,0.00,'paid',0,1,NULL,'qwwwwwwwwwwwdddeqwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww [Automatic Violation: Late Boundary (Past 10:00 AM)] [Automatic Violation: Vehicle Damaged] [Unit Sent to Maintenance - Shift Schedule Paused (No Boundary)]','2026-04-14 04:24:09','2026-04-14 04:24:09',18,18,NULL),(8,1,98,NULL,'2026-04-22',600.00,600.00,0.00,0.00,0.00,0.00,1,0,1,0.00,0.00,'paid',0,1,NULL,'[Automatic Violation: Vehicle Damaged]','2026-04-22 11:36:00','2026-04-22 11:36:00',18,18,NULL),(9,160,65,65,'2026-04-25',1200.00,1200.00,0.00,0.00,0.00,0.00,0,0,0,0.00,0.00,'paid',0,1,NULL,'ee [Automatic Violation: Low Fuel on Return]','2026-04-25 07:41:18','2026-04-25 07:41:18',18,18,NULL),(10,124,14,14,'2026-04-26',1200.00,1200.00,0.00,0.00,0.00,0.00,0,0,1,0.00,0.00,'paid',0,1,NULL,'[Automatic Violation: Late Boundary (Past 10:00 AM)] [Automatic Violation: Vehicle Damaged] [Automatic Violation: Low Fuel on Return]','2026-04-26 06:57:54','2026-04-26 06:57:54',18,18,NULL),(11,6,98,NULL,'2026-04-26',1000.00,1000.00,0.00,0.00,0.00,0.00,1,0,1,0.00,0.00,'paid',0,1,NULL,'[Automatic Violation: Late Boundary (Past 10:00 AM)] [Automatic Violation: Vehicle Damaged] [Automatic Violation: Low Fuel on Return]','2026-04-26 09:19:56','2026-04-26 09:19:56',18,18,NULL),(12,2,18,NULL,'2026-04-27',1100.00,1100.00,0.00,0.00,0.00,0.00,1,0,1,0.00,0.00,'paid',0,1,NULL,'[Automatic Violation: Vehicle Damaged] [Automatic Violation: Low Fuel on Return] [Unit Breakdown: 322.75 hrs x ₱45.83/hr - Schedule Paused]','2026-04-27 00:55:32','2026-04-27 00:55:32',18,18,NULL),(13,2,105,NULL,'2026-04-30',1100.00,1100.00,0.00,0.00,0.00,0.00,1,0,1,0.00,0.00,'paid',0,0,NULL,'[Automatic Violation: Late Boundary (Past 10:00 AM)] [Automatic Violation: Vehicle Damaged] [Automatic Violation: Low Fuel on Return] [Unit Breakdown: 79.18 hrs x ₱45.83/hr - Schedule Paused] [Disqualified: Recorded Incident - At-fault Accident]','2026-04-30 08:07:23','2026-04-30 08:07:23',125,125,NULL);
/*!40000 ALTER TABLE `boundaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boundary_rules`
--

DROP TABLE IF EXISTS `boundary_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boundary_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `start_year` year(4) NOT NULL,
  `end_year` year(4) NOT NULL,
  `regular_rate` decimal(10,2) NOT NULL,
  `sat_discount` decimal(10,2) NOT NULL DEFAULT 100.00,
  `sun_discount` decimal(10,2) NOT NULL,
  `coding_rate` decimal(10,2) NOT NULL,
  `coding_is_fixed` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boundary_rules`
--

LOCK TABLES `boundary_rules` WRITE;
/*!40000 ALTER TABLE `boundary_rules` DISABLE KEYS */;
INSERT INTO `boundary_rules` VALUES (1,'Legacy Models (2014 & Below)',2000,2014,1100.00,100.00,200.00,550.00,0,NULL,'2026-04-10 04:56:39','2026-04-10 04:56:39'),(2,'Standard Models (2015-2017)',2015,2017,1200.00,100.00,200.00,600.00,0,NULL,'2026-04-10 04:56:39','2026-04-10 04:56:39'),(3,'Modern Models (2018-2020)',2018,2020,1300.00,100.00,200.00,650.00,0,NULL,'2026-04-10 04:56:39','2026-04-10 04:56:39'),(4,'Premium Models (2021-2023)',2021,2025,1400.00,100.00,200.00,700.00,0,NULL,'2026-04-10 04:56:39','2026-04-10 04:56:39');
/*!40000 ALTER TABLE `boundary_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coding_records`
--

DROP TABLE IF EXISTS `coding_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coding_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `date` date DEFAULT NULL,
  `cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coding_records`
--

LOCK TABLES `coding_records` WRITE;
/*!40000 ALTER TABLE `coding_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `coding_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coding_rules`
--

DROP TABLE IF EXISTS `coding_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coding_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coding_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `restricted_plate_numbers` varchar(50) NOT NULL,
  `coding_type` enum('full_ban','partial') NOT NULL DEFAULT 'full_ban',
  `allowed_areas` text DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_coding_rules_day_status` (`coding_day`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coding_rules`
--

LOCK TABLES `coding_rules` WRITE;
/*!40000 ALTER TABLE `coding_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `coding_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coding_violations`
--

DROP TABLE IF EXISTS `coding_violations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coding_violations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `violation_type` varchar(191) NOT NULL,
  `location_name` varchar(191) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `violation_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coding_violations_unit_id_violation_time_index` (`unit_id`,`violation_time`),
  CONSTRAINT `coding_violations_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coding_violations`
--

LOCK TABLES `coding_violations` WRITE;
/*!40000 ALTER TABLE `coding_violations` DISABLE KEYS */;
INSERT INTO `coding_violations` VALUES (15,136,'Standard Coding','Museo Pambata, Roxas Boulevard, Ermita, Fifth District, Manila, Capital District, Metro Manila, 1000, Philippines',14.57953100,120.97728000,'2026-04-20 00:35:31','2026-04-20 00:46:44','2026-04-20 00:46:44'),(16,152,'Standard Coding','Trinity Restaurant, President Diosdado Macapagal Boulevard, Metropolitan Park, Barangay 76, Zone 10, District 1, Pasay, Southern Manila District, Metro Manila, 1308, Philippines',14.54516800,120.98607100,'2026-04-20 00:38:30','2026-04-20 00:47:03','2026-04-20 00:47:03');
/*!40000 ALTER TABLE `coding_violations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashcam_devices`
--

DROP TABLE IF EXISTS `dashcam_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashcam_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `manufacturer` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `firmware_version` varchar(50) DEFAULT NULL,
  `installation_date` date NOT NULL,
  `status` enum('active','inactive','maintenance','retired') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`),
  KEY `idx_unit_id` (`unit_id`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_dashcam_devices_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashcam_devices`
--

LOCK TABLES `dashcam_devices` WRITE;
/*!40000 ALTER TABLE `dashcam_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashcam_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashcam_events`
--

DROP TABLE IF EXISTS `dashcam_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashcam_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dashcam_device_id` int(11) NOT NULL,
  `event_type` enum('accident','emergency','sudden_brake','hard_acceleration','collision','manual') NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `duration` int(11) DEFAULT 0 COMMENT 'Duration in seconds',
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `speed_before` decimal(5,2) DEFAULT 0.00,
  `speed_after` decimal(5,2) DEFAULT 0.00,
  `g_force` decimal(4,2) DEFAULT 0.00,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_dashcam_device_id` (`dashcam_device_id`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_device_timestamp` (`dashcam_device_id`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashcam_events`
--

LOCK TABLES `dashcam_events` WRITE;
/*!40000 ALTER TABLE `dashcam_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashcam_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashcam_footage`
--

DROP TABLE IF EXISTS `dashcam_footage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashcam_footage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `camera_type` enum('front','interior','rear') DEFAULT 'front',
  `is_incident` tinyint(1) DEFAULT 0,
  `incident_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `incident_id` (`incident_id`),
  CONSTRAINT `dashcam_footage_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `dashcam_footage_ibfk_2` FOREIGN KEY (`incident_id`) REFERENCES `driver_behavior` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashcam_footage`
--

LOCK TABLES `dashcam_footage` WRITE;
/*!40000 ALTER TABLE `dashcam_footage` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashcam_footage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashcam_settings`
--

DROP TABLE IF EXISTS `dashcam_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashcam_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dashcam_device_id` int(11) NOT NULL,
  `video_quality` int(11) DEFAULT 1080 COMMENT 'Video quality (720, 1080, 4K)',
  `recording_mode` enum('continuous','event','manual') DEFAULT 'continuous',
  `event_recording_enabled` tinyint(1) DEFAULT 1,
  `g_sensor_enabled` tinyint(1) DEFAULT 1,
  `wifi_enabled` tinyint(1) DEFAULT 1,
  `auto_upload_enabled` tinyint(1) DEFAULT 1,
  `storage_alert` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dashcam_device_id` (`dashcam_device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashcam_settings`
--

LOCK TABLES `dashcam_settings` WRITE;
/*!40000 ALTER TABLE `dashcam_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashcam_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashcam_test_logs`
--

DROP TABLE IF EXISTS `dashcam_test_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashcam_test_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dashcam_device_id` int(11) NOT NULL,
  `test_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`test_result`)),
  `test_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dashcam_device_id` (`dashcam_device_id`),
  KEY `idx_test_date` (`test_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashcam_test_logs`
--

LOCK TABLES `dashcam_test_logs` WRITE;
/*!40000 ALTER TABLE `dashcam_test_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashcam_test_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_alerts`
--

DROP TABLE IF EXISTS `device_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type` enum('gps','dashcam') NOT NULL,
  `device_id` int(11) NOT NULL,
  `alert_type` enum('offline','low_battery','storage_full','error','maintenance') NOT NULL,
  `alert_message` text NOT NULL,
  `alert_level` enum('info','warning','critical') NOT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `idx_device_type` (`device_type`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_resolved` (`resolved`),
  CONSTRAINT `device_alerts_ibfk_1` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_alerts`
--

LOCK TABLES `device_alerts` WRITE;
/*!40000 ALTER TABLE `device_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_import_history`
--

DROP TABLE IF EXISTS `device_import_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_import_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `import_type` enum('gps','dashcam','both') NOT NULL,
  `device_count` int(11) NOT NULL,
  `import_status` enum('success','partial','failed') NOT NULL,
  `import_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`import_details`)),
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_unit_id` (`unit_id`),
  KEY `idx_import_type` (`import_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_import_history`
--

LOCK TABLES `device_import_history` WRITE;
/*!40000 ALTER TABLE `device_import_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_import_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver_balances`
--

DROP TABLE IF EXISTS `driver_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `driver_balances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `incident_id` int(11) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `remaining_balance` decimal(12,2) NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `driver_balances_driver_id_foreign` (`driver_id`),
  KEY `driver_balances_incident_id_foreign` (`incident_id`),
  CONSTRAINT `driver_balances_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `driver_balances_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `driver_behavior` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver_balances`
--

LOCK TABLES `driver_balances` WRITE;
/*!40000 ALTER TABLE `driver_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `driver_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver_behavior`
--

DROP TABLE IF EXISTS `driver_behavior`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `driver_behavior` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `incident_type` varchar(191) DEFAULT NULL,
  `sub_classification` varchar(191) DEFAULT NULL,
  `traffic_fine_amount` decimal(10,2) DEFAULT NULL,
  `sub_type` varchar(191) DEFAULT NULL,
  `cause_of_incident` varchar(191) DEFAULT NULL,
  `severity` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `third_party_name` varchar(191) DEFAULT NULL,
  `third_party_vehicle` varchar(191) DEFAULT NULL,
  `own_unit_damage_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `third_party_damage_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_driver_fault` tinyint(1) NOT NULL DEFAULT 0,
  `total_charge_to_driver` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `incentive_released_at` date DEFAULT NULL,
  `charge_status` varchar(191) DEFAULT 'none',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `incident_date` date DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` varchar(191) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `driver_behavior_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver_behavior`
--

LOCK TABLES `driver_behavior` WRITE;
/*!40000 ALTER TABLE `driver_behavior` DISABLE KEYS */;
INSERT INTO `driver_behavior` VALUES (3,133,29,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-14 04:20:58',NULL,'','2026-04-14 04:20:58',NULL,'2026-04-22 12:15:36'),(4,133,29,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Breakdown]: Unit broke down immediately upon deployment. No boundary collected (No Boundary).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-14 04:20:58',NULL,'','2026-04-14 04:20:58',NULL,'2026-04-22 12:15:25'),(5,1,75,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-14 04:24:09',NULL,'','2026-04-14 04:24:09',NULL,'2026-04-22 09:42:48'),(6,1,75,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Breakdown]: Unit broke down immediately upon deployment. No boundary collected (No Boundary).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-14 04:24:09',NULL,'','2026-04-14 04:24:09',NULL,'2026-04-22 09:43:06'),(7,1,75,'Accident',NULL,NULL,NULL,NULL,'critical','Subagent Test Incident - Final Fix Verification','Juan Dela Cruz','Sedan',1500.00,0.00,1,1500.00,1500.00,0.00,NULL,'paid',0.00000000,0.00000000,'2026-04-22 02:34:12','2026-04-22','','2026-04-22 02:34:12',NULL,NULL),(8,160,64,'Other',NULL,NULL,NULL,NULL,'high','qdwdqwd - VERIFIED BY AIUpdated Incident Details - Final Check',NULL,NULL,650.00,0.00,1,650.00,650.00,0.00,NULL,'paid',0.00000000,0.00000000,'2026-04-22 03:09:28','2026-04-22','','2026-04-22 03:09:28',NULL,NULL),(9,1,98,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-22 11:36:00',NULL,'','2026-04-22 11:36:00',NULL,NULL),(10,112,1,'Other',NULL,NULL,NULL,NULL,'medium','bgugyuu',NULL,NULL,938.00,0.00,1,938.00,0.00,938.00,NULL,'pending',0.00000000,0.00000000,'2026-04-22 11:43:44','2026-04-22','','2026-04-22 11:43:44',NULL,NULL),(11,1,98,'Coding Violation',NULL,NULL,NULL,NULL,'medium','Verif',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-22 11:51:27','2026-04-22','','2026-04-22 11:51:27',NULL,NULL),(12,160,65,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Low Fuel]: Driver returned the unit without refueling (Kulang sa gas).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-25 07:41:18','2026-04-25','','2026-04-25 07:41:18',NULL,NULL),(13,124,14,'Late Remittance',NULL,NULL,NULL,NULL,'medium','Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-26 06:57:54','2026-04-26','','2026-04-26 06:57:54',NULL,NULL),(14,124,14,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-26 06:57:54',NULL,'','2026-04-26 06:57:54',NULL,NULL),(15,124,14,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Low Fuel]: Driver returned the unit without refueling (Kulang sa gas).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-26 06:57:54','2026-04-26','','2026-04-26 06:57:54',NULL,NULL),(16,124,14,'Late Remittance',NULL,NULL,NULL,NULL,'medium','Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',NULL,NULL,'2026-04-26 06:57:54',NULL,NULL,'2026-04-26 06:57:54',NULL,NULL),(17,6,98,'Late Remittance',NULL,NULL,NULL,NULL,'medium','Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-26 09:19:56','2026-04-26','','2026-04-26 09:19:56',NULL,NULL),(18,6,98,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-26 09:19:56',NULL,'','2026-04-26 09:19:56',NULL,NULL),(19,6,98,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Low Fuel]: Driver returned the unit without refueling (Kulang sa gas).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-26 09:19:56','2026-04-26','','2026-04-26 09:19:56',NULL,NULL),(20,6,98,'Late Remittance',NULL,NULL,NULL,NULL,'medium','Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',NULL,NULL,'2026-04-26 09:19:56',NULL,NULL,'2026-04-26 09:19:56',NULL,NULL),(21,112,1,'Other',NULL,NULL,NULL,NULL,'medium','ehhe',NULL,NULL,64999350.00,0.00,1,64999350.00,0.00,64999350.00,NULL,'pending',0.00000000,0.00000000,'2026-04-26 17:01:56','2026-04-27','','2026-04-26 17:01:56',NULL,NULL),(22,2,18,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-27 00:55:32',NULL,'','2026-04-27 00:55:32',NULL,NULL),(23,2,18,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Low Fuel]: Driver returned the unit without refueling (Kulang sa gas).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-27 00:55:32','2026-04-27','','2026-04-27 00:55:32',NULL,NULL),(24,2,18,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Breakdown]: Unit broke down after 322.75 hrs on shift.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-27 00:55:32',NULL,'','2026-04-27 00:55:32',NULL,NULL),(25,112,1,'Vehicle Damage',NULL,NULL,NULL,NULL,'medium','bg',NULL,NULL,1200.00,0.00,1,1200.00,0.00,1200.00,NULL,'pending',0.00000000,0.00000000,'2026-04-27 01:00:03','2026-04-27','','2026-04-27 01:00:03',NULL,NULL),(26,132,27,'Accident',NULL,NULL,NULL,NULL,'high','Lashing',NULL,NULL,0.00,0.00,1,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-27 06:41:27','2026-04-27','','2026-04-27 06:41:27',NULL,NULL),(27,160,64,'Vehicle Damage',NULL,NULL,NULL,NULL,'critical','Reported by LTFRB  of fare contract',NULL,NULL,0.00,0.00,1,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-27 07:24:05','2026-04-27','','2026-04-27 07:24:05',NULL,NULL),(28,160,64,'missing_unit_overdue',NULL,NULL,NULL,NULL,'high','Auto-logged [Flagdown]: Unit NEF 4940 is overdue for >48 hours (Missing since Apr 26, 2026). Investigation required.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-28 07:58:21','2026-04-26','','2026-04-28 07:58:21',NULL,NULL),(29,124,15,'missing_unit_overdue',NULL,NULL,NULL,NULL,'high','Auto-logged [Flagdown]: Unit CAV 9662 is overdue for >48 hours (Missing since Apr 27, 2026). Investigation required.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-29 08:05:13','2026-04-27','','2026-04-29 08:05:13',NULL,NULL),(30,2,105,'Late Remittance',NULL,NULL,NULL,NULL,'medium','Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-30 08:07:23','2026-04-30','','2026-04-30 08:07:23',NULL,NULL),(31,2,105,'other',NULL,NULL,NULL,NULL,'high','Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-30 08:07:23',NULL,'','2026-04-30 08:07:23',NULL,NULL),(32,2,105,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Low Fuel]: Driver returned the unit without refueling (Kulang sa gas).',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-30 08:07:23','2026-04-30','','2026-04-30 08:07:23',NULL,NULL),(33,2,105,'other',NULL,NULL,NULL,NULL,'medium','Auto-logged [Breakdown]: Unit broke down after 79.18 hrs on shift.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',0.00000000,0.00000000,'2026-04-30 08:07:23',NULL,'','2026-04-30 08:07:23',NULL,NULL),(34,2,105,'Late Remittance',NULL,NULL,NULL,NULL,'medium','Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',NULL,NULL,0.00,0.00,0,0.00,0.00,0.00,NULL,'none',NULL,NULL,'2026-04-30 08:07:23',NULL,NULL,'2026-04-30 08:07:23',NULL,NULL),(35,160,105,'Vehicle Damage',NULL,NULL,NULL,NULL,'high','engot',NULL,NULL,17000.00,0.00,1,17000.00,5000.00,12000.00,NULL,'pending',0.00000000,0.00000000,'2026-04-30 10:01:09','2026-04-30','','2026-04-30 10:01:09',NULL,NULL);
/*!40000 ALTER TABLE `driver_behavior` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver_incentives`
--

DROP TABLE IF EXISTS `driver_incentives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `driver_incentives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `incentive_type` enum('performance','safety','attendance','customer_service','fuel_efficiency','boundary_target','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `incentive_date` date NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `performance_metric` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_driver` (`driver_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_month_year` (`month`,`year`),
  KEY `idx_date` (`incentive_date`),
  CONSTRAINT `driver_incentives_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `driver_incentives_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver_incentives`
--

LOCK TABLES `driver_incentives` WRITE;
/*!40000 ALTER TABLE `driver_incentives` DISABLE KEYS */;
/*!40000 ALTER TABLE `driver_incentives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(191) DEFAULT NULL,
  `last_name` varchar(191) DEFAULT NULL,
  `nickname` varchar(191) DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_expiry` date NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `daily_boundary_target` decimal(10,2) DEFAULT 1100.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `driver_type` enum('regular','senior','trainee') DEFAULT 'regular',
  `driver_status` enum('available','assigned','on_leave','suspended') DEFAULT 'available',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `license_number` (`license_number`),
  CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drivers`
--

LOCK TABLES `drivers` WRITE;
/*!40000 ALTER TABLE `drivers` DISABLE KEYS */;
INSERT INTO `drivers` VALUES (1,NULL,'Jesus','Duero',NULL,'TBD-32001EFF','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-12 17:46:44','regular','available',NULL,NULL,NULL),(2,NULL,'Randy','Genchez',NULL,'TBD-3F8AA113','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-27 06:03:29','regular','available',NULL,NULL,'2026-04-27 06:03:29'),(3,NULL,'Sanjali','Untal',NULL,'TBD-C9FCB570','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(4,NULL,'Norodin','Dimanda',NULL,'TBD-01E746AB','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(5,NULL,'Henry','Belen',NULL,'TBD-97C6F120','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(6,NULL,'Arwin','Azarcon',NULL,'TBD-88BF328B','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(7,NULL,'Arvy','Rodriguez',NULL,'TBD-C97E22A7','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(8,NULL,'Bensar','Kalaing',NULL,'TBD-51CE31E1','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(9,NULL,'Jose','Camillotes',NULL,'TBD-81CE3AFC','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(10,NULL,'Jamie','Ferrer',NULL,'TBD-1301FC96','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(11,NULL,'Joel','Sumando',NULL,'TBD-0B402E3D','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(12,NULL,'Virgilio','Ramos',NULL,'TBD-6F9CEC83','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(13,NULL,'Dindo','Defeo',NULL,'TBD-D9A510FD','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(14,NULL,'Rodel','Gudran',NULL,'TBD-D8123B07','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(15,NULL,'Rodel','Gundran',NULL,'TBD-73FB9CB8','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(16,NULL,'Angelo','Taboada',NULL,'TBD-A5A6914F','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(17,NULL,'Virgilio','Reponte',NULL,'TBD-8816FACA','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(18,NULL,'Elmer','Andrade',NULL,'TBD-7E5B68F8','2026-03-18','09153520035','nagcarlam laguna\r\n464644','asdasdasda','09153520035','2026-04-30',1400.00,'2026-04-10 03:49:10','2026-04-30 06:21:31','regular','available',NULL,125,NULL),(19,NULL,'Felimon','Evangilista',NULL,'TBD-0843B864','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(20,NULL,'Norlando','Fernandez',NULL,'TBD-AB326AFE','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(21,NULL,'Nelson','Castro',NULL,'TBD-1764CD13','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(22,NULL,'Willy','Bautista',NULL,'TBD-EC9C7E7D','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(23,NULL,'Ramil','Cadalzo',NULL,'TBD-6F4BCD5E','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(24,NULL,'Freddie','Lamigo',NULL,'TBD-8B25B9B4','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(25,NULL,'Erwin','Pajanilla',NULL,'TBD-4CDA3ECF','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(26,NULL,'Roel','Norombaba',NULL,'TBD-338EA643','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(27,NULL,'Roel','Peñol',NULL,'TBD-642148BE','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(28,NULL,'Domingo','Tresvalles',NULL,'TBD-C66E77B8','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(29,NULL,'Simeon','Miranda',NULL,'TBD-12607E9D','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(30,NULL,'Carlito','Sitoy',NULL,'TBD-13308793','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(31,NULL,'Francisco','Baja',NULL,'TBD-31EAC3C7','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(32,NULL,'Juanito','Cabales',NULL,'TBD-D1DCF7F4','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(33,NULL,'Almar','Monarba',NULL,'TBD-76CE1F0E','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(34,NULL,'Nelson','Juluat',NULL,'TBD-C8A89BDF','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(35,NULL,'Aldrin','Laya',NULL,'TBD-4B0B572C','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(36,NULL,'Elmar','Pabalate',NULL,'TBD-29EEA458','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(37,NULL,'Agapito','Ostonal',NULL,'TBD-6BC7B1E1','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(38,NULL,'Melencio','Singalawa',NULL,'TBD-C2310849','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(39,NULL,'Efren','Trinidad',NULL,'TBD-B6E39CF4','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(40,NULL,'Rogelio','Sanchez',NULL,'TBD-8875EE6B','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(41,NULL,'Michael','Fontanilla',NULL,'TBD-B7C6B411','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(42,NULL,'Wilfredo','Domingo',NULL,'TBD-66F042EE','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(43,NULL,'Yasse','Tangginog',NULL,'TBD-AF996EF1','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(44,NULL,'Dayanodin','Tangginog',NULL,'TBD-63A6B25F','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(45,NULL,'Domingo','Uyangorin',NULL,'TBD-0BEBFA4A','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(46,NULL,'Ricardo','Cuevas',NULL,'TBD-460577C7','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(47,NULL,'Gerse','Matallano',NULL,'TBD-93459098','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(48,NULL,'Gerse','Matallino',NULL,'TBD-331ADD63','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(49,NULL,'Ibrahim','Kaiting',NULL,'TBD-24752FB3','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(50,NULL,'Felimon','Malunes',NULL,'TBD-374A8078','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(51,NULL,'Alkisar','Makapundag',NULL,'TBD-5A32F300','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(52,NULL,'Mark Lester','Gundran',NULL,'TBD-5BE5E02E','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(53,NULL,'Radzmil','Nur',NULL,'TBD-DEBBC346','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(54,NULL,'Ruben','Patajo',NULL,'TBD-A7F4AC17','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(55,NULL,'Paulo','Ubag',NULL,'TBD-913DCC00','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(56,NULL,'Lito','Ayag',NULL,'TBD-D7722629','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(57,NULL,'Mario','Opeña',NULL,'TBD-85DED9A3','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(58,NULL,'Wilfredo','Orias',NULL,'TBD-EF88B2BC','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(59,NULL,'R','Laurente',NULL,'TBD-97778A34','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(60,NULL,'Javier','Ramber',NULL,'TBD-3DCD2E21','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(61,NULL,'Felix','Ausa',NULL,'TBD-10D37CD1','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:10','2026-04-10 03:49:10','regular','available',NULL,NULL,NULL),(62,NULL,'Joseph','Penaflor',NULL,'TBD-E435D4EF','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(63,NULL,'Victor','Manalo',NULL,'TBD-AE1A242A','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(64,NULL,'July','Sunico',NULL,'TBD-5DDC5FF9','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(65,NULL,'Roberto','Sunico',NULL,'TBD-552F0D2F','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(66,NULL,'Jimmy','Gundran',NULL,'TBD-EF5EB292','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(67,NULL,'Rommel','Gonzales',NULL,'TBD-806EC1B3','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(68,NULL,'Apolinario','Calingasan',NULL,'TBD-BB0FD0DD','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(69,NULL,'Apolinario','Calisangan',NULL,'TBD-0749A4DC','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(70,NULL,'Morlino','Boroy',NULL,'TBD-614DB287','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(71,NULL,'Henner','Bonsol',NULL,'TBD-22953AE4','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(72,NULL,'Leonildo','Calubag',NULL,'TBD-6721635B','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(73,NULL,'Marlito','Baguioro',NULL,'TBD-3ECA69B6','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(74,NULL,'Peter','Leyva',NULL,'TBD-DD721DD5','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(75,NULL,'Sismundo','Candelaria',NULL,'TBD-438D1D7B','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(76,NULL,'Jefrrey','Tandual',NULL,'TBD-DE6108ED','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(77,NULL,'Edwin','Satar',NULL,'TBD-2ABDB36A','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(78,NULL,'Ricky','Romera',NULL,'TBD-421F8DF0','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(79,NULL,'Jose','Rio',NULL,'TBD-6B7ECDFB','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(80,NULL,'Alejandro','Ramos',NULL,'TBD-5F6031ED','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(81,NULL,'Joey','Motol',NULL,'TBD-80310A21','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(82,NULL,'Hermilio','Granado',NULL,'TBD-258E02E3','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(83,NULL,'Ronipo','Quijado',NULL,'TBD-5812B9FE','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(84,NULL,'Daud','Utap',NULL,'TBD-848C8A42','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(85,NULL,'Joseph','Piandiong',NULL,'TBD-DDCE7112','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(86,NULL,'Oliver','Ariola',NULL,'TBD-49E4877E','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(87,NULL,'Edward','Nieva',NULL,'TBD-EDF6CD3F','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(88,NULL,'Rolly','Cuballes',NULL,'TBD-88375B7B','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(89,NULL,'Angel','Salazar',NULL,'TBD-7F1D6F2B','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(90,NULL,'Domingo','Jorojoro',NULL,'TBD-8FED4CF2','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(91,NULL,'Monico','Funtanilla',NULL,'TBD-70B90598','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(92,NULL,'William','Monisit',NULL,'TBD-5A58CAD8','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(93,NULL,'Jayson','Borromeo',NULL,'TBD-0AD5AF1A','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(94,NULL,'Edwin','Joquino',NULL,'TBD-3404AC5D','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(95,NULL,'Fernando','Razo',NULL,'TBD-70D35714','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(96,NULL,'Renato','Cortez',NULL,'TBD-FD364D1A','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(97,NULL,'Noel','Tequillo',NULL,'TBD-03354041','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(98,NULL,'Nelson','Adobas',NULL,'TBD-15312D74','2026-01-16','09153520035','nagcarlam laguna\r\n464644','1124434343','09153520035','2026-04-29',1400.00,'2026-04-10 03:49:11','2026-04-29 13:16:05','regular','available',NULL,125,'2026-04-29 13:16:05'),(99,NULL,'Armando','Cruz',NULL,'TBD-D1599031','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(100,NULL,'Napoleon','Emberso',NULL,'TBD-B77035A6','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(101,NULL,'Alfredo','Hagad',NULL,'TBD-00AC1C90','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(102,NULL,'Francisco','Raagas',NULL,'TBD-F4A2CA4F','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(103,NULL,'Gary','Lorenzo',NULL,'TBD-C1D70343','2027-01-01',NULL,NULL,NULL,NULL,NULL,1100.00,'2026-04-10 03:49:11','2026-04-10 03:49:11','regular','available',NULL,NULL,NULL),(104,NULL,'sunibertson','sunico',NULL,'2121131','2026-04-09','09153520035','nagcarlam laguna\r\n464644','asdasdasda','09153520035','2026-04-13',1100.00,'2026-04-12 17:37:20','2026-04-12 17:47:17','regular','available',18,18,'2026-04-12 17:47:17'),(105,NULL,'sunibertson','sunico',NULL,'TBD-7E5B68F8w','2026-04-30','09153520035','nagcarlam laguna\r\n464644','vvvvvv','09153520035','2026-04-30',1100.00,'2026-04-30 07:38:17','2026-04-30 07:42:39','regular','available',125,18,NULL);
/*!40000 ALTER TABLE `drivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `employee_type` enum('mechanic','office_staff','driver','manager') NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary_rate` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `spare_part_id` bigint(20) unsigned DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recorded_by` int(11) DEFAULT NULL,
  `expense_category` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_expenses_date` (`date`),
  KEY `idx_expenses_category` (`category`),
  KEY `expenses_spare_part_id_index` (`spare_part_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,'Spare Parts Purchase','PURCHASED: 17 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,14450.00,NULL,NULL,NULL,'2026-04-25',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-25 12:57:37','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(2,'Spare Parts Purchase','PURCHASED: 1 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,850.00,NULL,NULL,NULL,'2026-04-25',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-25 13:26:07','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(3,'Spare Parts Purchase','PURCHASED: 29 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,24650.00,NULL,NULL,NULL,'2026-04-25',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-25 13:37:18','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(4,'Spare Parts Purchase','PURCHASED: 11 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,9350.00,NULL,NULL,NULL,'2026-04-25',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-25 13:37:39','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(5,'Spare Parts Purchase','PURCHASED: 1 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,850.00,NULL,NULL,NULL,'2026-04-25',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-25 14:04:33','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(6,'Spare Parts Purchase','PURCHASED: 1 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,850.00,NULL,NULL,NULL,'2026-04-26',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-26 07:11:11','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(7,'Spare Parts Purchase','PURCHASED: 78 pcs of Air Filter (Toyota Vios/Hiace) from Unspecified Supplier',NULL,66300.00,NULL,NULL,NULL,'2026-04-26',NULL,NULL,NULL,NULL,NULL,'approved',NULL,'2026-04-26 10:19:03','2026-04-26 11:07:17',18,'Spare Parts Purchase',18,NULL,NULL),(8,'Electricity (Meralco)','aa','meralco',1000.00,NULL,NULL,'Cash','2026-04-26','2131231331',NULL,NULL,NULL,NULL,'pending',NULL,'2026-04-26 11:06:15','2026-04-26 11:06:15',18,NULL,18,18,NULL),(9,'Spare Parts Purchase','PURCHASED: Air Filter (Toyota Vios/Hiace)','A. BONIFACIO AUTO',1700.00,2,850.00,'Cash','2026-04-26','123123',NULL,2,NULL,NULL,'pending',NULL,'2026-04-26 14:14:49','2026-04-26 14:14:49',18,NULL,18,18,NULL),(10,'Spare Parts Purchase','PURCHASED: bb','A. BONIFACIO AUTO',2024.00,23,88.00,'Cash','2026-04-26','12312312312',NULL,23,NULL,NULL,'pending',NULL,'2026-04-26 14:16:06','2026-04-26 14:16:06',18,NULL,18,18,NULL),(11,'Spare Parts Purchase','Inventory STOCK: 1 pcs of ATF / CVT Transmission Fluid (1L)','Unspecified Supplier',650.00,1,650.00,NULL,'2026-04-26',NULL,NULL,14,NULL,NULL,'approved',NULL,'2026-04-26 14:18:07','2026-04-26 14:18:07',18,NULL,18,18,NULL),(12,'Spare Parts Purchase','PURCHASED: Brake Fluid (500ml)','AMONLATHE WORKS',350.00,1,350.00,'Cash','2026-04-26','1wwww1w1w',NULL,13,NULL,NULL,'pending',NULL,'2026-04-26 14:28:40','2026-04-26 14:28:40',18,NULL,18,18,NULL),(13,'Spare Parts Purchase','REGISTERED & PURCHASED: jj','ABC AUTO PARTS',81.00,9,9.00,'Cash','2026-04-26',NULL,NULL,26,NULL,NULL,'pending',NULL,'2026-04-26 15:33:49','2026-04-26 15:33:49',18,NULL,18,18,NULL),(14,'Internet & WiFi','uso bayad','balot vendor',2000.00,NULL,NULL,'Transfer','2026-04-27',NULL,NULL,NULL,NULL,NULL,'pending',NULL,'2026-04-26 16:55:33','2026-04-26 16:55:33',18,NULL,18,18,NULL),(15,'Damage Recovery','Direct cash payment from Sismundo Candelaria for accident debt (Incident Date: 2026-04-22)',NULL,-100.00,NULL,NULL,'Cash','2026-04-30',NULL,1,NULL,NULL,NULL,'approved',NULL,'2026-04-30 06:59:40','2026-04-30 06:59:40',125,NULL,125,125,NULL),(16,'Damage Recovery','Direct cash payment from Sismundo Candelaria for accident debt (Incident Date: 2026-04-22)',NULL,-100.00,NULL,NULL,'Cash','2026-04-30',NULL,1,NULL,NULL,NULL,'approved',NULL,'2026-04-30 07:55:15','2026-04-30 07:55:15',125,NULL,125,125,NULL),(17,'Spare Parts Purchase','Inventory STOCK: 1 pcs of ATF / CVT Transmission Fluid (1L)','Unspecified Supplier',650.00,1,650.00,NULL,'2026-04-30',NULL,NULL,14,NULL,NULL,'approved',NULL,'2026-04-30 08:20:06','2026-04-30 08:20:06',125,NULL,125,125,NULL),(18,'Spare Parts Purchase','Inventory STOCK: 1 pcs of ATF / CVT Transmission Fluid (1L)','Unspecified Supplier',650.00,1,650.00,NULL,'2026-04-30',NULL,NULL,14,NULL,NULL,'approved',NULL,'2026-04-30 08:20:13','2026-04-30 08:20:13',125,NULL,125,125,NULL),(19,'Spare Parts Purchase','Inventory STOCK: 1 pcs of ATF / CVT Transmission Fluid (1L)','Unspecified Supplier',650.00,1,650.00,NULL,'2026-04-30',NULL,NULL,14,NULL,NULL,'approved',NULL,'2026-04-30 08:20:13','2026-04-30 08:20:13',125,NULL,125,125,NULL),(20,'Spare Parts Purchase','Inventory STOCK: 11 pcs of ATF / CVT Transmission Fluid (1L)','Unspecified Supplier',7150.00,11,650.00,NULL,'2026-04-30',NULL,NULL,14,NULL,NULL,'approved',NULL,'2026-04-30 08:20:29','2026-04-30 08:20:29',125,NULL,125,125,NULL),(21,'Spare Parts Purchase','PURCHASED: Air Filter (Toyota Vios/Hiace)','A. BONIFACIO AUTO',5950.00,7,850.00,'Cash','2026-04-30',NULL,NULL,2,NULL,NULL,'pending',NULL,'2026-04-30 08:22:12','2026-04-30 08:22:12',125,NULL,125,125,NULL),(22,'Spare Parts Purchase','PURCHASED: Air Filter (Toyota Vios/Hiace)','A. BONIFACIO AUTO',5950.00,7,850.00,'Cash','2026-04-30',NULL,NULL,2,NULL,NULL,'pending',NULL,'2026-04-30 08:22:12','2026-04-30 08:22:12',125,NULL,125,125,NULL),(23,'Electricity (Meralco)','meralco bills',NULL,1100.00,NULL,NULL,'Cash','2026-04-30','2131231331',NULL,NULL,NULL,NULL,'pending',NULL,'2026-04-30 08:23:01','2026-04-30 08:23:01',125,NULL,125,125,NULL),(24,'Spare Parts Purchase','REGISTERED & PURCHASED: brake hose','A. BONIFACIO AUTO',5000.00,10,500.00,'Cash','2026-04-30','111',NULL,27,NULL,NULL,'pending',NULL,'2026-04-30 09:34:50','2026-04-30 09:34:50',125,NULL,125,125,NULL),(25,'Damage Recovery','Direct cash payment from sunibertson sunico for accident debt (Incident Date: 2026-04-30)',NULL,-5000.00,NULL,NULL,'Cash','2026-04-30',NULL,160,NULL,NULL,NULL,'approved',NULL,'2026-04-30 10:01:42','2026-04-30 10:01:42',125,NULL,125,125,NULL),(26,'Damage Recovery','Direct cash payment from Sismundo Candelaria for accident debt (Incident Date: 2026-04-22)',NULL,-1300.00,NULL,NULL,'Cash','2026-04-30',NULL,1,NULL,NULL,NULL,'approved',NULL,'2026-04-30 10:02:07','2026-04-30 10:02:07',125,NULL,125,125,NULL),(27,'Damage Recovery','Direct cash payment from July Sunico for accident debt (Incident Date: 2026-04-22)',NULL,-300.00,NULL,NULL,'Cash','2026-04-30',NULL,160,NULL,NULL,NULL,'approved',NULL,'2026-04-30 10:02:37','2026-04-30 10:02:37',125,NULL,125,125,NULL),(28,'Damage Recovery','Direct cash payment from July Sunico for accident debt (Incident Date: 2026-04-22)',NULL,-350.00,NULL,NULL,'Cash','2026-04-30',NULL,160,NULL,NULL,NULL,'approved',NULL,'2026-04-30 10:03:04','2026-04-30 10:03:04',125,NULL,125,125,NULL),(29,'Water (Maynilad)','123','meralco',500000.00,NULL,NULL,'Cash','2026-04-30','12312312312',NULL,NULL,125,'2026-04-30 10:17:18','approved',NULL,'2026-04-30 10:17:18','2026-04-30 10:17:18',125,NULL,125,125,NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `franchise_case_units`
--

DROP TABLE IF EXISTS `franchise_case_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `franchise_case_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `franchise_case_id` int(11) NOT NULL,
  `make` varchar(191) DEFAULT NULL,
  `motor_no` varchar(191) DEFAULT NULL,
  `chasis_no` varchar(191) DEFAULT NULL,
  `plate_no` varchar(191) DEFAULT NULL,
  `year_model` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `franchise_case_units_franchise_case_id_foreign` (`franchise_case_id`),
  CONSTRAINT `franchise_case_units_franchise_case_id_foreign` FOREIGN KEY (`franchise_case_id`) REFERENCES `franchise_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `franchise_case_units`
--

LOCK TABLES `franchise_case_units` WRITE;
/*!40000 ALTER TABLE `franchise_case_units` DISABLE KEYS */;
INSERT INTO `franchise_case_units` VALUES (96,1,'TOYOTA VIOS','1NRX142517','PA1B119F30H4027929','NCN 8583','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(97,1,'TOYOTA VIOS','1NRX428108','PA1B119F33K4083254','NEI 4883','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(98,2,'TOYOTA VIOS','1NRX428966','PA1B13F37K4083631','NDI 2585','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(99,2,'TOYOTA VIOS','1NRX699044','PA1B18F32M4147994','NEW 3821','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(100,2,'TOYOTA VIOS','1NRX573855','PA1B18F3XL4116880','CAV 2607','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(101,3,'TOYOTA VIOS','1NRX665295','PA1B18F3XM4139156','CBM 1979','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(102,3,'TOYOTA VIOS','1NRX593251','PA1B18F33L4123685','DAT 2567','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(103,3,'TOYOTA VIOS','1NRX662804','PA1B18F32M4138437','NEP 2440','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(104,4,'TOYOTA VIOS','1NRX539051','PA1B18F35L4109741','DAZ 9769','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(105,4,'TOYOTA VIOS','1NRX554443','PA1B18F3XL4112067','DBA 5420','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(106,4,'TOYOTA VIOS','1NRX585027','PA1B18F33L4120575','NGA 7736','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(107,5,'TOYOTA VIOS','1NRX570523','PA1B18F35L4115295','EAE 1247','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(108,5,'TOYOTA VIOS','1NRX288337','PA1B19F31J060654','NCW 5011','2018','2026-04-13 02:18:20','2026-04-13 02:18:20'),(109,5,'TOYOTA VIOS','1NRX617160','PA1B18F3XL4124719','NGB 6033','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(110,6,'TOYOTA VIOS','1NRX479141','PA1B13F39K4095280','NEN 2955','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(111,6,'TOYOTA VIOS','1NRX478775','PA1B13F37K4095102','NEN 2957','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(112,6,'TOYOTA VIOS','1NRX592060','PA1B18F34L4123212','EAF 7245','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(113,7,'TOYOTA VIOS','1NRX519089','PA1B18F37K4105320','CAT 6073','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(114,7,'TOYOTA VIOS','1NRX544017','PA1B118F30L4110974','DBA 1887','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(115,7,'TOYOTA VIOS','1NRX560364','PA1B18F35L4113725','NAN 1349','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(116,7,'TOYOTA VIOS','1NRX563284','PA1B18F33L4114131','NFZ 8295','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(117,7,'TOYOTA VIOS','1NRX513727','PA1B13F32K4103414','NGA 5044','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(118,8,'TOYOTA VIOS','1NRX364595','PA1B13F35J4069838','DAJ 7468','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(119,9,'TOYOTA VIOS','1NRX728802','PA1B18F33M4156266','EAE 4949','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(120,10,'TOYOTA VIOS','1NRX670488','PA1B18F33M4140536','NEP 9750','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(121,11,'TOYOTA VIOS','1NRX399793','PA1B13F30J4076793','NDA 8102','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(122,12,'TOYOTA VIOS','1NRX382535','PA1B13F37J4074295','NDA 5429','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(123,13,'TOYOTA VIOS','1NRX711083','PA1B18F35M4150503','NET6100','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(124,14,'TOYOTA VIOS','1NRX511105','PA1B13F30K4102617','EAD 7438','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(125,15,'TOYOTA VIOS','1NRX265877','PA1B19F33J4055018','NAM 1610','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(126,16,'TOYOTA VIOS','1NRX765584','PA1B18F37N4171824','CAX 5430','2022','2026-04-13 02:18:20','2026-04-13 02:18:20'),(127,17,'TOYOTA VIOS','1NRX400695','PA1B13F38J4076895','NDA 8106','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(128,18,'TOYOTA VIOS','1NRX399472','PA1B13F38J4076640','NEA 1292','2019','2026-04-13 02:18:20','2026-04-13 02:18:20'),(129,19,'TOYOTA VIOS','1NRX505510','PA1B13F34K4101664','NGF 1484','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(130,20,'TOYOTA VIOS','1NRX684775','PA1B18F354143793','EAE 1919','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(131,21,'TOYOTA VIOS','1NRX676394','PA1B18F39M4141920','VAA 9864','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(132,22,'TOYOTA VIOS','1NRX587826','PA1B18F37L4121826','NGO 2629','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(133,23,'TOYOTA VIOS','2NZ6564244','NCP92-964857','VFL 543','2013','2026-04-13 02:18:20','2026-04-13 02:18:20'),(134,24,'TOYOTA VIOS','1NRX728865','PA1B18F35M4156270','NEV 5065','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(135,25,'TOYOTA VIOS','1NRX586443','PA1B18F37L4121129','DAT 1367','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(136,26,'TOYOTA VIOS','1NRX711080','PA1B18F33M4150502','NEW 6279','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(137,27,'TOYOTA VIOS','1NRX530110','PA1B18F38K4108095','DBA 2302','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(138,28,'TOYOTA VIOS','1NRX587947','PA1B18F34L4121976','EAF 6347','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(139,29,'TOYOTA VIOS','1NRX758930','PA1B18F35N4169456','NFH 3664','2022','2026-04-13 02:18:20','2026-04-13 02:18:20'),(140,30,'TOYOTA VIOS','1NRX494346','PA1B13F39K4098339','NEU 5546','2020','2026-04-13 02:18:20','2026-04-13 02:18:20'),(141,31,'TOYOTA VIOS','1NRX622805','PA1B18F33L4126120','CAV 9662','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(142,32,'TOYOTA VIOS','1NRX622596','PA1B18F33L4125985','CAV 9716','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(143,33,'TOYOTA VIOS','1NRX735643','PA1B18F3XM4159021','EAE 5883','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(144,34,'TOYOTA VIOS','1NRX626439','PA1B18F30L4128830','NGP 1887','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(146,36,'TOYOTA VIOS','1NRX593170','PA1B18F36L4123549','NGB 2854','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(147,42,'TOYOTA VIOS','1NRX591797','PA1B18F34L4123081','CAV 6803','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(148,43,'TOYOTA VIOS','1NRX669745','PA1B18F39M4140346','DAU 9027','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(149,39,'TOYOTA VIOS','1NRX703030','PA1B18F3XM4149041','NEO 6716','2021','2026-04-13 02:18:20','2026-04-13 02:18:20'),(150,40,'TOYOTA VIOS','2NZ7307868','NCP151-2031009','AAK 9196','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(151,40,'TOYOTA VIOS','2NZ6978423','NCP151-2012488','AAA 4591','2014','2026-04-13 02:18:20','2026-04-13 02:18:20'),(152,40,'TOYOTA VIOS','2NZ7160776','NCP151-2022506','AAQ 1743','2014','2026-04-13 02:18:20','2026-04-13 02:18:20'),(153,40,'TOYOTA VIOS','2NZ7384223','NCP151-2036531','ALA 3699','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(154,40,'TOYOTA VIOS','2NZ7494105','NCP151-2043398','ABG 7479','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(155,40,'TOYOTA VIOS','2NZ7400896','NCP151-2037524','ABL 6901','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(156,40,'TOYOTA VIOS','2NZ7542383','NCP151-2046832','ABL 1667','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(157,40,'TOYOTA VIOS','2NZ7301579','NCP151-2030436','AEA 9630','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(158,40,'TOYOTA VIOS','2NZ7470861','NCP151-2042785','ABF 7471','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(159,40,'TOYOTA VIOS','2NZ7557953','NCP151-2048091','ABP 2705','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(160,40,'TOYOTA VIOS','2NZ7541411','NCP151-2046789','ABP 7643','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(161,40,'TOYOTA VIOS','2NZ7263141','NCP151-2028527','AOA 8917','2015','2026-04-13 02:18:20','2026-04-13 02:18:20'),(162,40,'TOYOTA VIOS','1NRX128495','PA1B19F32H4024496','DAD 7555','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(163,40,'TOYOTA VIOS','1NRX049858','PA1B19F37G4007336','DCQ 1551','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(164,40,'TOYOTA VIOS','1NRX136597','PA1B19F31H4026529','NBX 4348','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(165,40,'TOYOTA VIOS','2NZ7666502','NCP151-2055742','NBW 7071','2016','2026-04-13 02:18:20','2026-04-13 02:18:20'),(166,40,'TOYOTA VIOS','1NRX118001','PA1B19F35H4021382','NAE 7193','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(167,40,'TOYOTA VIOS','1NRX093367','PA1B19F36G4016559','NAD 1140','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(168,40,'TOYOTA VIOS','1NRX072072','PA1B19F3XG4012319','NAC 4989','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(169,40,'TOYOTA VIOS','1NRX074746','PA1B19F32G4012928','NDG 7105','2017','2026-04-13 02:18:20','2026-04-13 02:18:20'),(170,44,'TOYOTA VIOS','2NZ7847183','NCP1512071757','ADY2597','2016','2026-04-13 02:21:30','2026-04-13 02:21:30'),(171,44,'TOYOTA VIOS','2NZ7868669','NCP1512074065','ADY2599','2016','2026-04-13 02:21:30','2026-04-13 02:21:30'),(172,44,'TOYOTA VIOS','2NZ7868643','NCP1512074063','ADY2598','2016','2026-04-13 02:21:30','2026-04-13 02:21:30'),(173,44,'TOYOTA VIOS','2NZ7474668','NCP1512042968','ASA6135','2015','2026-04-13 02:21:30','2026-04-13 02:21:30'),(174,44,'TOYOTA VIOS','2NZ7871027','NCP1512074362','NCJ7661','2018','2026-04-13 02:21:30','2026-04-13 02:21:30'),(175,44,'TOYOTA VIOS','1NRX051542','PA1B19F37G4007854','NDC7363','2017','2026-04-13 02:21:30','2026-04-13 02:21:30'),(176,44,'TOYOTA VIOS','1NRX078597','PA1B19F33G4013649','EAA4540','2017','2026-04-13 02:21:30','2026-04-13 02:21:30'),(177,44,'TOYOTA VIOS','1NRX202099','PA1B19F36H4042726','EAA9555','2017','2026-04-13 02:21:30','2026-04-13 02:21:30'),(178,44,'TOYOTA VIOS','1NRX344222','PA1B13F32J4064743','NBR1341','2018','2026-04-13 02:21:30','2026-04-13 02:21:30'),(179,44,'TOYOTA VIOS','1NRX366474','PA1B13F36J4070268','EAB8186','2019','2026-04-13 02:21:30','2026-04-13 02:21:30'),(180,45,'TOYOTA VIOS','1NRX507225','PA1B13F31K4102013','NEF 4940','2020','2026-04-13 02:31:53','2026-04-13 02:31:53');
/*!40000 ALTER TABLE `franchise_case_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `franchise_cases`
--

DROP TABLE IF EXISTS `franchise_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `franchise_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_name` varchar(255) NOT NULL,
  `case_no` varchar(100) NOT NULL,
  `type_of_application` varchar(255) NOT NULL,
  `denomination` varchar(255) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `date_filed` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('pending','approved','denied','expired') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_no` (`case_no`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `franchise_cases`
--

LOCK TABLES `franchise_cases` WRITE;
/*!40000 ALTER TABLE `franchise_cases` DISABLE KEYS */;
INSERT INTO `franchise_cases` VALUES (1,'EUROTAXI INC.','NCR 2014-01300','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2027-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(2,'EUROTAXI INC.','NCR 2014-01302','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2022-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(3,'EUROTAXI INC.','NCR 2014-01299','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2024-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(4,'EUROTAXI INC.','NCR 2014-01301','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2029-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(5,'EUROTAXI INC.','NCR 2014-01286','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2025-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(6,'EUROTAXI INC.','NCR 2014-01303','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2024-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(7,'EUROTAXI INC.','NCR 2014-01304','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2026-02-27','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(8,'EUROTAXI INC.','NCR 2014-01287','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2027-07-11','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(9,'EUROTAXI INC.','NCR 2014-01285','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2027-10-14','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(10,'EUROTAXI INC.','NCR 2014-01288','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2027-10-19','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(11,'EUROTAXI INC.','NCR 2014-01289','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2027-10-27','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(12,'EUROTAXI INC.','NCR 2014-01149','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2024-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(13,'EUROTAXI INC.','NCR 2014-01233','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2025-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(14,'EUROTAXI INC.','NCR 2014-01148','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2029-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(15,'EUROTAXI INC.','NCR 2014-01231','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2029-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(16,'EUROTAXI INC.','NCR 2014-01151','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2029-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(17,'EUROTAXI INC.','NCR 2014-01235','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2029-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(18,'EUROTAXI INC.','NCR 2014-01234','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2025-07-11','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(19,'EUROTAXI INC.','NCR 2014-01232','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2025-10-18','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(20,'EUROTAXI INC.','NCR 2014-01150','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2025-12-08','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(21,'EUROTAXI INC.','NCR 2014-01152','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2026-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(22,'EUROTAXI INC.','NCR 2014-01153','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2026-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(23,'EUROTAXI INC.','NCR 2014-01147','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2029-06-12','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(24,'CENTRAL','CENTRAL 96-9555','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(25,'CENTRAL','CENTRAL 95-866','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-11-01','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(26,'CENTRAL','CENTRAL 95-20643','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-11-02','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(27,'CENTRAL','CENTRAL 95-9798','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-11-03','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(28,'CENTRAL','CENTRAL 95-3745','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-11-04','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(29,'CENTRAL','CENTRAL 95-27627','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-11-05','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(30,'CENTRAL','CENTRAL 97-00846','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-11-06','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(31,'RQG TRANSPORT','NCR 2015-02362','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2022-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(32,'RQG TRANSPORT','NCR 2015-02366','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-08-02','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(33,'RQG TRANSPORT','NCR 2015-02368','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(34,'RQG TRANSPORT','NCR 2015-02853','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(36,'RQG TRANSPORT','NCR 2015-02367','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(39,'RQG TRANSPORT','NCR 2015-02363','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2028-10-31','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(40,'RQG TRANSPORT','NCR 2015-00083','Franchise Renewal/Transfer','Taxi',NULL,'2024-01-01','2027-09-02','approved',NULL,'2026-04-13 01:40:12','2026-04-13 01:40:12',NULL),(42,'RQG TRANSPORT','NCR 2018-4-2015-02365','Extension of Validity','Taxi Airconditioned Service',NULL,'2023-10-31','2028-10-31','pending',NULL,'2026-04-13 02:18:20','2026-04-13 02:18:20',NULL),(43,'RQG TRANSPORT','NCR 2018-4-2015-02370','Extension of Validity','Taxi Airconditioned Service',NULL,'2023-10-31','2028-10-31','pending',NULL,'2026-04-13 02:18:20','2026-04-13 02:18:20',NULL),(44,'DELA CRUZ EDUARDO','2012-0502','Franchise Verification','Taxi Airconditioned Service',NULL,'2021-11-08','2026-02-09','pending',NULL,'2026-04-13 02:21:30','2026-04-13 02:21:30',NULL),(45,'RQG TRANSPORT','NCR 2018-4-2015-02364','Extension of Validity','Taxi Airconditioned Service',NULL,'2018-10-31','2023-10-31','pending',NULL,'2026-04-13 02:31:53','2026-04-13 02:31:53',NULL);
/*!40000 ALTER TABLE `franchise_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `franchise_units`
--

DROP TABLE IF EXISTS `franchise_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `franchise_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `make` varchar(255) NOT NULL,
  `motor_no` varchar(255) NOT NULL,
  `chasis_no` varchar(255) NOT NULL,
  `plate_no` varchar(255) NOT NULL,
  `year_model` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  CONSTRAINT `franchise_units_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `franchise_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `franchise_units`
--

LOCK TABLES `franchise_units` WRITE;
/*!40000 ALTER TABLE `franchise_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `franchise_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_devices`
--

DROP TABLE IF EXISTS `gps_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gps_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `manufacturer` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `firmware_version` varchar(50) DEFAULT NULL,
  `installation_date` date NOT NULL,
  `status` enum('active','inactive','maintenance','retired') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`),
  KEY `idx_unit_id` (`unit_id`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_gps_devices_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_devices`
--

LOCK TABLES `gps_devices` WRITE;
/*!40000 ALTER TABLE `gps_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_logs`
--

DROP TABLE IF EXISTS `gps_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gps_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gps_device_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `speed` decimal(5,2) DEFAULT 0.00 COMMENT 'Speed in km/h',
  `heading` int(11) DEFAULT 0 COMMENT 'Heading in degrees',
  `altitude` decimal(8,2) DEFAULT 0.00 COMMENT 'Altitude in meters',
  `accuracy` decimal(6,2) DEFAULT 0.00 COMMENT 'Accuracy in meters',
  `battery_level` int(11) DEFAULT 100 COMMENT 'Battery level in percentage',
  `signal_strength` int(11) DEFAULT 0 COMMENT 'Signal strength',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gps_device_id` (`gps_device_id`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_device_timestamp` (`gps_device_id`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_logs`
--

LOCK TABLES `gps_logs` WRITE;
/*!40000 ALTER TABLE `gps_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_settings`
--

DROP TABLE IF EXISTS `gps_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gps_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gps_device_id` int(11) NOT NULL,
  `update_interval` int(11) DEFAULT 30 COMMENT 'Update interval in seconds',
  `accuracy_threshold` int(11) DEFAULT 10 COMMENT 'Accuracy threshold in meters',
  `speed_threshold` int(11) DEFAULT 5 COMMENT 'Speed threshold in km/h',
  `geofencing_enabled` tinyint(1) DEFAULT 1,
  `geofence_radius` int(11) DEFAULT 100 COMMENT 'Geofence radius in meters',
  `low_battery_alert` tinyint(1) DEFAULT 1,
  `offline_alert` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gps_device_id` (`gps_device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_settings`
--

LOCK TABLES `gps_settings` WRITE;
/*!40000 ALTER TABLE `gps_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_test_logs`
--

DROP TABLE IF EXISTS `gps_test_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gps_test_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gps_device_id` int(11) NOT NULL,
  `test_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`test_result`)),
  `test_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gps_device_id` (`gps_device_id`),
  KEY `idx_test_date` (`test_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_test_logs`
--

LOCK TABLES `gps_test_logs` WRITE;
/*!40000 ALTER TABLE `gps_test_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_test_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_tracking`
--

DROP TABLE IF EXISTS `gps_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gps_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `speed` decimal(5,2) DEFAULT NULL,
  `heading` decimal(5,2) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ignition_status` tinyint(1) DEFAULT 0,
  `odo` decimal(12,2) DEFAULT NULL,
  `daily_start_mileage` decimal(12,2) DEFAULT NULL,
  `daily_start_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_unit_timestamp` (`unit_id`,`timestamp`),
  CONSTRAINT `gps_tracking_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_tracking`
--

LOCK TABLES `gps_tracking` WRITE;
/*!40000 ALTER TABLE `gps_tracking` DISABLE KEYS */;
INSERT INTO `gps_tracking` VALUES (1,112,14.66827600,121.06991100,0.00,231.00,'2026-04-23 01:39:00',0,93844.95,93844.95,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(2,1,14.66828400,121.07003600,0.00,303.00,'2026-04-27 11:06:34',0,15686.38,15686.38,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(3,115,14.66828000,121.07009800,0.00,60.00,'2026-04-28 05:39:32',0,43537.22,43537.22,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(4,187,14.66811900,121.07013300,0.00,86.00,'2026-04-27 08:36:38',0,79846.24,79846.24,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(5,116,14.64196800,121.03870200,16.00,151.00,'2026-04-30 02:40:38',1,72068.30,72012.72,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(6,118,14.66874800,121.06981300,0.00,9.00,'2026-04-29 21:12:38',0,40775.07,40774.97,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(7,7,14.66810900,121.07003600,0.00,222.00,'2026-04-14 18:56:45',0,20745.58,20745.58,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(8,120,14.66851700,121.06984000,0.00,103.00,'2026-04-29 17:07:49',0,75086.02,75086.02,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:31'),(9,122,14.66854000,121.06993800,0.00,9.00,'2026-04-30 01:57:54',0,116393.67,116379.08,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(10,136,14.66861600,121.07002700,0.00,325.00,'2026-04-30 01:32:04',0,107427.70,107427.47,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(11,138,14.66837600,121.06958200,0.00,154.00,'2026-04-29 21:05:05',0,102833.32,102833.30,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(12,139,14.66827000,121.06995600,0.00,180.00,'2026-04-29 21:59:42',0,97349.96,97204.12,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(13,8,14.66824800,121.06992000,0.00,271.00,'2026-04-29 23:28:41',0,41516.16,41516.12,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(14,186,14.62013900,121.04719100,0.00,139.00,'2026-04-30 02:25:47',0,115533.87,115489.48,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(15,149,14.66808200,121.07008000,0.00,179.00,'2026-04-29 12:35:29',0,60179.47,60179.47,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(16,20,14.66397100,121.03631100,4.00,342.00,'2026-04-30 02:28:00',1,85151.27,85070.35,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(17,151,14.64976400,121.03471100,5.00,333.00,'2026-04-30 02:38:02',1,11996.35,11917.45,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(18,152,14.55093300,121.00585800,0.00,136.00,'2026-04-29 22:08:05',0,50484.32,50477.04,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(19,156,14.75877400,121.07641800,26.00,276.00,'2026-04-30 02:39:01',1,94346.31,94265.27,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(20,157,14.66842200,121.06969800,0.00,47.00,'2026-04-28 23:41:46',0,44231.53,44231.53,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32'),(21,185,14.66955600,121.04048000,19.00,177.00,'2026-04-30 02:44:00',1,59823.86,59773.16,'2026-04-30','2026-04-12 06:17:07','2026-04-30 10:46:32');
/*!40000 ALTER TABLE `gps_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incident_classifications`
--

DROP TABLE IF EXISTS `incident_classifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_classifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `default_severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `color` varchar(191) NOT NULL DEFAULT 'gray',
  `icon` varchar(191) NOT NULL DEFAULT 'alert-circle',
  `behavior_mode` enum('narrative','complaint','traffic','damage') NOT NULL DEFAULT 'narrative',
  `sub_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sub_options`)),
  `auto_ban_trigger` tinyint(1) NOT NULL DEFAULT 0,
  `ban_trigger_value` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `incident_classifications_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incident_classifications`
--

LOCK TABLES `incident_classifications` WRITE;
/*!40000 ALTER TABLE `incident_classifications` DISABLE KEYS */;
INSERT INTO `incident_classifications` VALUES (1,'Coding Violation','high','red','shield-alert','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL),(2,'Late Remittance','medium','orange','clock','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-30 08:13:11',NULL),(3,'Short Boundary','medium','yellow','trending-down','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL),(4,'Vehicle Damage','high','gray','alert-circle','damage','[]',0,NULL,'2026-04-28 08:28:18','2026-04-29 04:51:55',NULL),(5,'Accident','critical','red','alert-octagon','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL),(6,'Traffic Violation','medium','orange','traffic-cone','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL),(7,'Absent / No Show','low','gray','user-x','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 10:45:39',NULL),(8,'Passenger Complaint','medium','gray','alert-circle','complaint','[]',0,NULL,'2026-04-28 08:28:18','2026-04-29 05:55:44',NULL),(9,'Speeding','high','red','gauge','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL),(10,'Hard Braking','low','orange','zap','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL),(11,'Other','low','gray','alert-circle','narrative',NULL,0,NULL,'2026-04-28 08:28:18','2026-04-28 08:28:18',NULL);
/*!40000 ALTER TABLE `incident_classifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incident_involved_parties`
--

DROP TABLE IF EXISTS `incident_involved_parties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_involved_parties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `driver_behavior_id` int(11) NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `vehicle_type` varchar(191) DEFAULT NULL,
  `plate_number` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incident_involved_parties_driver_behavior_id_foreign` (`driver_behavior_id`),
  CONSTRAINT `incident_involved_parties_driver_behavior_id_foreign` FOREIGN KEY (`driver_behavior_id`) REFERENCES `driver_behavior` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incident_involved_parties`
--

LOCK TABLES `incident_involved_parties` WRITE;
/*!40000 ALTER TABLE `incident_involved_parties` DISABLE KEYS */;
INSERT INTO `incident_involved_parties` VALUES (1,7,'Juan Dela Cruz','Sedan','ABC 1234','2026-04-22 02:34:12','2026-04-22 02:34:12');
/*!40000 ALTER TABLE `incident_involved_parties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incident_parts_estimates`
--

DROP TABLE IF EXISTS `incident_parts_estimates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_parts_estimates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `driver_behavior_id` int(11) NOT NULL,
  `spare_part_id` bigint(20) unsigned DEFAULT NULL,
  `custom_part_name` varchar(191) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_charged_to_driver` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incident_parts_estimates_spare_part_id_foreign` (`spare_part_id`),
  KEY `incident_parts_estimates_driver_behavior_id_foreign` (`driver_behavior_id`),
  CONSTRAINT `incident_parts_estimates_driver_behavior_id_foreign` FOREIGN KEY (`driver_behavior_id`) REFERENCES `driver_behavior` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_parts_estimates_spare_part_id_foreign` FOREIGN KEY (`spare_part_id`) REFERENCES `spare_parts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incident_parts_estimates`
--

LOCK TABLES `incident_parts_estimates` WRITE;
/*!40000 ALTER TABLE `incident_parts_estimates` DISABLE KEYS */;
INSERT INTO `incident_parts_estimates` VALUES (1,7,21,NULL,1,1500.00,1500.00,1,'2026-04-22 02:34:12','2026-04-22 02:34:12'),(2,8,14,NULL,1,650.00,650.00,1,'2026-04-22 03:09:28','2026-04-22 03:09:28'),(3,10,NULL,NULL,1,88.00,88.00,1,'2026-04-22 11:43:44','2026-04-22 11:43:44'),(4,10,2,NULL,1,850.00,850.00,1,'2026-04-22 11:43:44','2026-04-22 11:43:44'),(5,21,14,NULL,99999,650.00,64999350.00,1,'2026-04-26 17:01:56','2026-04-26 17:01:56'),(6,25,2,NULL,1,850.00,850.00,1,'2026-04-27 01:00:03','2026-04-27 01:00:03'),(7,25,13,NULL,1,350.00,350.00,1,'2026-04-27 01:00:03','2026-04-27 01:00:03'),(8,35,2,NULL,20,850.00,17000.00,1,'2026-04-30 10:01:09','2026-04-30 10:01:09');
/*!40000 ALTER TABLE `incident_parts_estimates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_audit`
--

DROP TABLE IF EXISTS `login_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_audit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(191) DEFAULT NULL,
  `user_email` varchar(191) DEFAULT NULL,
  `user_role` varchar(191) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `notes` varchar(191) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `login_audit_user_id_action_index` (`user_id`,`action`),
  KEY `login_audit_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_audit`
--

LOCK TABLES `login_audit` WRITE;
/*!40000 ALTER TABLE `login_audit` DISABLE KEYS */;
INSERT INTO `login_audit` VALUES (1,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 01:55:42'),(2,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 01:57:56'),(3,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 02:04:01'),(4,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 02:04:58'),(5,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 02:09:34'),(6,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 02:10:14'),(7,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 02:13:03'),(8,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 02:13:41'),(9,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 02:14:41'),(10,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: admin@eurotaxisystem.com','2026-04-27 02:14:49'),(11,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 02:15:56'),(12,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 03:40:04'),(13,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:22:05'),(14,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:22:17'),(15,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:23:15'),(16,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:23:53'),(17,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:24:03'),(18,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:25:32'),(19,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:31:32'),(20,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 05:38:23'),(21,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 05:38:31'),(22,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 18:40:19'),(23,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:40:26'),(24,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:40:29'),(25,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:40:44'),(26,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:40:49'),(27,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:40:51'),(28,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:40:53'),(29,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:41:01'),(30,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:41:04'),(31,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: sonysunico02@gmail.com','2026-04-27 18:41:07'),(32,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 18:43:09'),(33,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 18:43:39'),(34,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 18:44:34'),(35,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 19:26:03'),(36,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 19:29:11'),(37,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 19:29:46'),(38,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 19:32:34'),(39,126,'Secretary Test','appcarrental2025@gmail.com','secretary','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 20:55:13'),(40,127,'Secretary Test','appcarrental2025@gmail.com','secretary','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Staff account created by Robert Garcia with role: secretary','2026-04-27 20:58:59'),(41,127,'Secretary Test','appcarrental2025@gmail.com','secretary','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 21:00:05'),(42,128,'Secretary Test','appcarrental2025@gmail.com','secretary','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Staff account created by Robert Garcia with role: secretary','2026-04-27 21:05:52'),(43,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: appcarrental2025@gmail.com','2026-04-27 21:06:52'),(44,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: appcarrental2025@gmail.com','2026-04-27 21:06:54'),(45,128,'Secretary Test','appcarrental2025@gmail.com','secretary','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login via MFA device verification.','2026-04-27 21:07:30'),(46,128,'Secretary Test','appcarrental2025@gmail.com','secretary','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-27 21:15:10'),(47,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-28 08:35:26'),(48,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-28 08:41:53'),(49,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-29 03:43:29'),(50,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Driver Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: Nelson Adobas\nUpdated details and status to Available','2026-04-29 06:31:05'),(51,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Driver Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: Nelson Adobas\nUpdated details and status to Available','2026-04-29 06:31:26'),(52,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Archived Unit','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: AAK 4591 moved to archive system.','2026-04-29 08:05:29'),(53,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Restored Unit','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: AAK 4591 was restored from the system archive.','2026-04-29 08:06:28'),(54,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Permanently Deleted Unit','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: TX-0011 was permanently wiped from the database.','2026-04-29 08:06:38'),(55,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Permanently Deleted Unit','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: ABC123 was permanently wiped from the database.','2026-04-29 08:06:41'),(56,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Archived Driver','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: Nelson Adobas moved to archive.','2026-04-29 13:16:05'),(57,128,'Secretary Test','appcarrental2025@gmail.com','secretary','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account deactivated by Robert Garcia','2026-04-30 05:33:53'),(58,128,'Secretary Test','appcarrental2025@gmail.com','secretary','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account re-activated by Robert Garcia','2026-04-30 05:33:55'),(59,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account deactivated by Robert Garcia','2026-04-30 05:35:52'),(60,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account re-activated by Robert Garcia','2026-04-30 05:35:52'),(61,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account deactivated by Robert Garcia','2026-04-30 05:35:55'),(62,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account re-activated by Robert Garcia','2026-04-30 05:35:58'),(63,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account disabled by Robert Garcia Reason: aa','2026-04-30 05:47:44'),(64,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 05:49:51'),(65,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: appcarrental2025@gmail.com','2026-04-30 05:53:25'),(66,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: appcarrental2025@gmail.com','2026-04-30 05:53:29'),(67,NULL,NULL,NULL,NULL,'failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Failed login for: appcarrental2025@gmail.com','2026-04-30 05:56:57'),(68,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login blocked: account disabled. Reason: aa','2026-04-30 05:57:04'),(69,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login blocked: account disabled. Reason: aa','2026-04-30 05:57:06'),(70,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login blocked: account disabled. Reason: aa','2026-04-30 05:57:10'),(71,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','failed_login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Login blocked: account disabled. Reason: aa','2026-04-30 05:57:16'),(72,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account enabled by Robert Garcia','2026-04-30 05:57:30'),(73,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 05:57:57'),(74,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account disabled by Robert Garcia Reason: aaaaaa','2026-04-30 05:58:10'),(75,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account disabled by Robert Garcia Reason: aaaaaa','2026-04-30 05:58:12'),(76,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','approved','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account enabled by Robert Garcia','2026-04-30 05:58:38'),(77,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Permanently Deleted Staff','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: Romy M. Tomas was permanently wiped from the database.','2026-04-30 06:11:51'),(78,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Driver Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: Elmer Andrade\nUpdated details and status to Available','2026-04-30 06:20:48'),(79,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Driver Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: Elmer Andrade\nUpdated details and status to Available','2026-04-30 06:21:31'),(80,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Debt Payment','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Processed ₱100.00 cash payment from Sismundo Candelaria for accident debt.','2026-04-30 06:59:40'),(81,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Driver Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: sunibertson sunico\nLicense: TBD-7E5B68F8w\nStatus: Available','2026-04-30 07:38:17'),(82,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Archived Driver','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: sunibertson sunico moved to archive.','2026-04-30 07:39:00'),(83,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 07:40:58'),(84,18,'Sunibertson R. Sunico','sonysunico02@gmail.com','Developer','Restored Driver','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: sunibertson sunico was restored from the system archive.','2026-04-30 07:41:26'),(85,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Unit','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: TX-00122\nCategory: TOYOTA VIOS (2026)\nStatus: Active','2026-04-30 07:42:39'),(86,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Archived Unit','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: TX-00122 moved to archive system.','2026-04-30 07:43:34'),(87,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Debt Payment','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Processed ₱100.00 cash payment from Sismundo Candelaria for accident debt.','2026-04-30 07:55:15'),(88,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Boundary Remittance','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: AAQ 1743\nDriver: sunibertson sunico\nDate: 2026-04-30\nCollected: ₱1,100.00\nStatus: Paid','2026-04-30 08:07:23'),(89,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Archived Maintenance Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: AAQ 1743\nType: Corrective\nRecord archived and stock returned.','2026-04-30 08:16:57'),(90,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Restored Maintenance','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: Automatic entry: Reported broken down during boundary turnover (Half Boundary).\nComputation: 79.18 hrs x ₱45.83/hr was restored from the system archive.','2026-04-30 08:17:28'),(91,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Restored Maintenance','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: Automatic entry: Reported broken down during boundary turnover (Half Boundary).\nComputation: 79.18 hrs x ₱45.83/hr was restored from the system archive.','2026-04-30 08:17:29'),(92,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Restored Maintenance','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Item: Automatic entry: Reported broken down during boundary turnover (Half Boundary).\nComputation: 79.18 hrs x ₱45.83/hr was restored from the system archive.','2026-04-30 08:17:30'),(93,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Restored Spare Part','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Part: Toyota Super Long Life Coolant (1L) restored from archive.','2026-04-30 08:18:36'),(94,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Restored Spare Part','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Part: Toyota Super Long Life Coolant (1L) restored from archive.','2026-04-30 08:18:37'),(95,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Spare Part','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Part: ATF / CVT Transmission Fluid (1L)\nPrice: ₱650.00\nStock Added: +1 units (New total: 11)\nOffice Expense recorded: #17','2026-04-30 08:20:06'),(96,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Spare Part','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Part: ATF / CVT Transmission Fluid (1L)\nPrice: ₱650.00\nStock Added: +1 units (New total: 12)\nOffice Expense recorded: #18','2026-04-30 08:20:13'),(97,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Spare Part','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Part: ATF / CVT Transmission Fluid (1L)\nPrice: ₱650.00\nStock Added: +1 units (New total: 13)\nOffice Expense recorded: #19','2026-04-30 08:20:13'),(98,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Updated Spare Part','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Part: ATF / CVT Transmission Fluid (1L)\nPrice: ₱650.00\nStock Added: +11 units (New total: 24)\nOffice Expense recorded: #20','2026-04-30 08:20:29'),(99,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Office Expense','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Category: Spare Parts Purchase\nDescription: PURCHASED: Air Filter (Toyota Vios/Hiace)\nAmount: ₱5,950.00','2026-04-30 08:22:12'),(100,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Office Expense','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Category: Spare Parts Purchase\nDescription: PURCHASED: Air Filter (Toyota Vios/Hiace)\nAmount: ₱5,950.00','2026-04-30 08:22:12'),(101,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Office Expense','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Category: Electricity (Meralco)\nDescription: meralco bills\nAmount: ₱1,100.00','2026-04-30 08:23:01'),(102,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Office Expense','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Category: Spare Parts Purchase\nDescription: REGISTERED & PURCHASED: brake hose\nAmount: ₱5,000.00','2026-04-30 09:34:50'),(103,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Maintenance Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: ADY 2599\nType: Preventive\nCost: ₱5,351.00\nParts used: Air Filter (Toyota Vios/Hiace) (x2), ATF / CVT Transmission Fluid (1L) (x1), kupal','2026-04-30 09:39:14'),(104,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Maintenance Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: DBA 1887\nType: Preventive\nCost: ₱16,500.00\nParts used: Air Filter (Toyota Vios/Hiace) (x1), ATF / CVT Transmission Fluid (1L) (x1), labordaybukas','2026-04-30 09:40:31'),(105,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Recorded Incident','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Driver: sunibertson sunico\nUnit: NEF 4940\nType: Vehicle Damage\nSeverity: High','2026-04-30 10:01:09'),(106,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Debt Payment','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Processed ₱5,000.00 cash payment from sunibertson sunico for accident debt.','2026-04-30 10:01:42'),(107,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Debt Payment','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Processed ₱1,300.00 cash payment from Sismundo Candelaria for accident debt.','2026-04-30 10:02:07'),(108,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Debt Payment','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Processed ₱300.00 cash payment from July Sunico for accident debt.','2026-04-30 10:02:37'),(109,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Debt Payment','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Processed ₱350.00 cash payment from July Sunico for accident debt.','2026-04-30 10:03:04'),(110,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Office Expense','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Category: Water (Maynilad)\nDescription: 123\nAmount: ₱500,000.00','2026-04-30 10:17:18'),(111,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','Created Maintenance Record','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Unit: ABF 2705\nType: Preventive\nCost: ₱650,850.00\nParts used: Air Filter (Toyota Vios/Hiace) (x1), 222','2026-04-30 10:18:13'),(112,129,'Shiella Marie Orilla','shiellamarie.sec@gmail.com','secretary','created','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Staff account created by Robert Garcia with role: secretary','2026-04-30 10:20:27'),(113,130,'Rea Remitra','remitra.manager1@gmail.com','manager','created','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Staff account created by Robert Garcia with role: manager','2026-04-30 10:21:13'),(114,131,'Romy Thomas','Romy.dispatcher1@gmail.com','dispatcher','created','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Staff account created by Robert Garcia with role: dispatcher','2026-04-30 10:21:45'),(115,128,'Secretary Test','appcarrental2025@gmail.com','secretary','rejected','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Account archived by Robert Garcia','2026-04-30 10:23:52'),(116,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 10:56:15'),(117,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 10:58:05'),(118,131,'Romy Thomas','Romy.dispatcher1@gmail.com','dispatcher','password_changed','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Staff forced password change completed.','2026-04-30 11:05:09'),(119,131,'Romy Thomas','Romy.dispatcher1@gmail.com','dispatcher','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 11:05:09'),(120,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','logout','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 11:25:19'),(121,125,'Robert Garcia','robertgarcia.owner@gmail.com','super_admin','login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',NULL,'2026-04-30 11:53:53');
/*!40000 ALTER TABLE `login_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance`
--

DROP TABLE IF EXISTS `maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `maintenance_type` enum('preventive','corrective','emergency') NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL,
  `odometer_reading` int(11) DEFAULT NULL,
  `date_started` date NOT NULL,
  `date_completed` date DEFAULT NULL,
  `status` enum('pending','in_progress','in_shop','testing','completed','cancelled') DEFAULT 'pending',
  `mechanic_name` varchar(100) DEFAULT NULL,
  `parts_list` text DEFAULT NULL,
  `parts_used` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `maintenance_driver_id_foreign` (`driver_id`),
  CONSTRAINT `maintenance_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance`
--

LOCK TABLES `maintenance` WRITE;
/*!40000 ALTER TABLE `maintenance` DISABLE KEYS */;
INSERT INTO `maintenance` VALUES (1,160,65,'preventive','ARAY',850.00,NULL,'2026-04-13','2026-04-14','completed','Abran A. Oracion, Callito A.  Belmar','Air Filter (Toyota Vios/Hiace) (x1)',NULL,'2026-04-13 00:31:03','2026-04-14 02:48:31',18,18,NULL),(2,160,64,'corrective','Automatic entry: Reported broken down immediately upon deployment (No Boundary).',0.00,NULL,'2026-04-14','2026-04-25','completed',NULL,NULL,NULL,'2026-04-14 02:45:39','2026-04-25 14:43:08',18,18,NULL),(3,160,64,'corrective','Automatic entry: Reported broken down immediately upon deployment (No Boundary).',0.00,NULL,'2026-04-14','2026-04-25','completed',NULL,NULL,NULL,'2026-04-14 02:46:34','2026-04-25 14:42:56',18,18,NULL),(4,133,29,'corrective','Automatic entry: Reported broken down immediately upon deployment (No Boundary).',0.00,NULL,'2026-04-14','2026-04-25','completed',NULL,NULL,NULL,'2026-04-14 04:20:58','2026-04-25 14:03:08',18,18,NULL),(5,1,75,'emergency','Automatic entry: Reported broken down immediately upon deployment (No Boundary).\r\n\r\nDispatcher Notes:\r\nqwwwwwwwwwwwdddeqwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww',14830.00,NULL,'2026-04-14','2026-04-25','completed','Callito A.  Belmar','ATF / CVT Transmission Fluid (1L) (x1), Brake Shoes Rear (x1), Clutch Disc (Genuine) (x1), Shock Absorber Front (Pair) (x1), Wheel Hub / Bearing Front (x1), wdqfniwfninn3ifnini3nfi34nfi3nnffffffffffffffffffffffffffffffffffffffffffffffffffffffff',NULL,'2026-04-14 04:24:09','2026-04-25 14:43:15',18,18,NULL),(6,12,51,'preventive',NULL,850.00,NULL,'2026-04-25','2026-04-25','pending','Joel H. Llouido','Air Filter (Toyota Vios/Hiace) (x1)',NULL,'2026-04-25 14:46:37','2026-04-25 14:46:37',18,18,NULL),(7,5,68,'preventive',NULL,850.00,NULL,'2026-04-25',NULL,'pending','Nilo E. Dugu','Air Filter (Toyota Vios/Hiace) (x1)',NULL,'2026-04-25 14:51:06','2026-04-25 14:51:06',18,18,NULL),(8,6,35,'preventive',NULL,850.00,NULL,'2026-04-26','2026-04-26','completed','Callito A.  Belmar, Abran A. Oracion','Air Filter (Toyota Vios/Hiace) (x1)',NULL,'2026-04-26 09:21:55','2026-04-26 09:27:50',18,18,NULL),(9,2,18,'corrective','Automatic entry: Reported broken down during boundary turnover (Half Boundary).\r\nComputation: 322.75 hrs x ₱45.83/hr',650.00,NULL,'2026-04-27',NULL,'testing','Callito A.  Belmar','ATF / CVT Transmission Fluid (1L) (x1)',NULL,'2026-04-27 00:55:32','2026-04-30 00:23:56',18,125,NULL),(10,2,105,'corrective','Automatic entry: Reported broken down during boundary turnover (Half Boundary).\nComputation: 79.18 hrs x ₱45.83/hr',0.00,NULL,'2026-04-30',NULL,'testing',NULL,NULL,NULL,'2026-04-30 08:07:23','2026-04-30 08:19:08',125,125,NULL),(11,5,37,'preventive',NULL,5351.00,NULL,'2026-04-30','2026-04-30','pending','Marlon P. Nalaluan','Air Filter (Toyota Vios/Hiace) (x2), ATF / CVT Transmission Fluid (1L) (x1), kupal',NULL,'2026-04-30 09:39:14','2026-04-30 09:39:14',125,125,NULL),(12,133,29,'preventive',NULL,16500.00,NULL,'2026-04-30','2026-04-30','pending','Callito A.  Belmar','Air Filter (Toyota Vios/Hiace) (x1), ATF / CVT Transmission Fluid (1L) (x1), labordaybukas',NULL,'2026-04-30 09:40:31','2026-04-30 09:40:31',125,125,NULL),(13,12,35,'preventive',NULL,650850.00,NULL,'2026-04-30','2026-04-24','pending','Callito A.  Belmar','Air Filter (Toyota Vios/Hiace) (x1), 222',NULL,'2026-04-30 10:18:13','2026-04-30 10:18:13',125,125,NULL);
/*!40000 ALTER TABLE `maintenance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_parts`
--

DROP TABLE IF EXISTS `maintenance_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_parts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_id` int(11) NOT NULL,
  `part_id` bigint(20) unsigned DEFAULT NULL,
  `part_name` varchar(191) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_parts_maintenance_id_foreign` (`maintenance_id`),
  CONSTRAINT `maintenance_parts_maintenance_id_foreign` FOREIGN KEY (`maintenance_id`) REFERENCES `maintenance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_parts`
--

LOCK TABLES `maintenance_parts` WRITE;
/*!40000 ALTER TABLE `maintenance_parts` DISABLE KEYS */;
INSERT INTO `maintenance_parts` VALUES (5,1,2,'Air Filter (Toyota Vios/Hiace)',1,850.00,850.00,'2026-04-13 03:22:38','2026-04-13 03:22:38'),(11,5,14,'ATF / CVT Transmission Fluid (1L)',1,650.00,650.00,'2026-04-14 04:27:07','2026-04-14 04:27:07'),(12,5,4,'Brake Shoes Rear',1,1650.00,1650.00,'2026-04-14 04:27:07','2026-04-14 04:27:07'),(13,5,15,'Clutch Disc (Genuine)',1,3200.00,3200.00,'2026-04-14 04:27:07','2026-04-14 04:27:07'),(14,5,18,'Shock Absorber Front (Pair)',1,5500.00,5500.00,'2026-04-14 04:27:07','2026-04-14 04:27:07'),(15,5,17,'Wheel Hub / Bearing Front',1,3500.00,3500.00,'2026-04-14 04:27:07','2026-04-14 04:27:07'),(16,5,NULL,'wdqfniwfninn3ifnini3nfi34nfi3nnffffffffffffffffffffffffffffffffffffffffffffffffffffffff',1,330.00,330.00,'2026-04-14 04:27:07','2026-04-14 04:27:07'),(17,6,2,'Air Filter (Toyota Vios/Hiace)',1,850.00,850.00,'2026-04-25 14:46:37','2026-04-25 14:46:37'),(18,7,2,'Air Filter (Toyota Vios/Hiace)',1,850.00,850.00,'2026-04-25 14:51:06','2026-04-25 14:51:06'),(19,8,2,'Air Filter (Toyota Vios/Hiace)',1,850.00,850.00,'2026-04-26 09:21:55','2026-04-26 09:21:55'),(20,9,14,'ATF / CVT Transmission Fluid (1L)',1,650.00,650.00,'2026-04-27 00:57:34','2026-04-27 00:57:34'),(21,11,2,'Air Filter (Toyota Vios/Hiace)',2,850.00,1700.00,'2026-04-30 09:39:14','2026-04-30 09:39:14'),(22,11,14,'ATF / CVT Transmission Fluid (1L)',1,650.00,650.00,'2026-04-30 09:39:14','2026-04-30 09:39:14'),(23,11,NULL,'kupal',1,3001.00,3001.00,'2026-04-30 09:39:14','2026-04-30 09:39:14'),(24,12,2,'Air Filter (Toyota Vios/Hiace)',1,850.00,850.00,'2026-04-30 09:40:31','2026-04-30 09:40:31'),(25,12,14,'ATF / CVT Transmission Fluid (1L)',1,650.00,650.00,'2026-04-30 09:40:31','2026-04-30 09:40:31'),(26,12,NULL,'labordaybukas',1,15000.00,15000.00,'2026-04-30 09:40:31','2026-04-30 09:40:31'),(27,13,2,'Air Filter (Toyota Vios/Hiace)',1,850.00,850.00,'2026-04-30 10:18:13','2026-04-30 10:18:13'),(28,13,NULL,'222',1,650000.00,650000.00,'2026-04-30 10:18:13','2026-04-30 10:18:13');
/*!40000 ALTER TABLE `maintenance_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_records`
--

DROP TABLE IF EXISTS `maintenance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `type` enum('preventive','corrective','breakdown','inspection') NOT NULL DEFAULT 'preventive',
  `description` text NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `mechanic_name` varchar(100) DEFAULT NULL,
  `maintenance_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_records`
--

LOCK TABLES `maintenance_records` WRITE;
/*!40000 ALTER TABLE `maintenance_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `managed_expenses`
--

DROP TABLE IF EXISTS `managed_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `managed_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `billing_month` tinyint(4) NOT NULL,
  `billing_year` int(11) NOT NULL,
  `date_paid` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('paid','unpaid') NOT NULL DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `managed_expenses`
--

LOCK TABLES `managed_expenses` WRITE;
/*!40000 ALTER TABLE `managed_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `managed_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2024_01_01_000001_create_eurotaxi_tables',2),(9,'2024_01_01_000002_fix_boundaries_table',6),(10,'2024_01_01_000003_add_status_to_franchise_cases',6),(11,'2026_03_16_101713_create_units_table',6),(12,'2026_03_16_102827_create_expenses_table',6),(13,'2026_03_16_103033_create_drivers_table',6),(14,'2026_03_16_103348_create_maintenance_table',6),(15,'2026_03_16_130521_create_gps_tracking_table',6),(16,'2026_03_18_183437_create_sessions_table',6),(17,'2026_03_18_201840_add_github_columns_to_users_table',6),(18,'2026_03_19_203137_create_system_alerts_table',7),(19,'2026_03_20_190859_add_details_to_boundaries_table',8),(20,'2026_03_23_141825_change_role_to_string_on_users_table',9),(21,'2026_03_23_153918_add_tracking_columns_to_tables',10),(22,'2026_03_24_133100_fix_franchise_cases_status',11),(23,'2026_03_24_000000_create_franchise_case_units_table',12),(24,'2026_03_24_135200_fix_infinityfree_database',12),(25,'2026_03_25_000001_add_gps_imei_to_units_table',13),(26,'2026_03_25_083116_rename_gps_imei_to_gps_link_on_units_table',14),(27,'2026_03_25_124128_create_staff_table',15),(28,'2026_03_25_160000_add_user_profile_fields',16),(29,'2026_03_25_174500_add_profile_image_to_users_table',17),(30,'2026_03_27_224504_add_soft_deletes_to_multiple_tables',18),(31,'2026_03_27_225253_add_soft_deletes_to_users_table',19),(32,'2026_03_28_123336_create_coding_records_table',20),(33,'2026_03_28_185943_make_email_nullable_on_users_table',21),(34,'2026_03_28_211209_add_parts_list_to_maintenance_if_missing',22),(35,'2026_03_28_211230_add_audit_columns_to_maintenance_if_missing',23),(36,'2026_04_05_000000_add_suffix_and_phone_to_users_table',24),(37,'2026_04_05_175540_add_otp_fields_to_users_table',24),(38,'2026_04_08_110126_create_user_recognized_devices_table',25),(39,'2026_04_08_112144_add_constraints_to_user_verified_browsers_table',26),(40,'2026_04_08_214628_create_boundary_rules_table',27),(41,'2026_04_09_124319_relax_unit_number_constraint_on_units_table',28),(43,'2026_04_09_225925_add_names_to_drivers_table',29),(44,'2026_04_10_000000_remove_unit_number_from_units_table',30),(45,'2026_04_10_113434_add_vacant_to_unit_status_enum',31),(46,'2026_04_10_142229_drop_fuel_status_from_units_table',32),(47,'2026_04_10_144928_add_imei_to_units_table',33),(48,'2026_04_12_141601_add_updated_at_to_gps_tracking_table',34),(49,'2026_04_12_161111_add_daily_stats_to_gps_tracking_table',35),(50,'2026_04_12_235100_align_boundary_status_enum',36),(51,'2026_04_13_021301_add_is_extra_driver_to_boundaries_table',37),(52,'2026_04_13_074218_create_spare_parts_table',38),(53,'2026_04_13_074257_create_maintenance_parts_table',39),(54,'2026_04_13_080927_add_driver_id_to_maintenance_table',40),(55,'2026_04_13_103731_alter_units_table_swap_color_for_motor_chassis',41),(56,'2026_04_13_120015_sync_motor_chassis_data_v2',42),(57,'2026_04_13_203105_create_coding_violations_table',43),(58,'2026_04_13_220807_add_swapping_fields_to_units_and_boundaries',44),(59,'2026_04_13_225815_add_shift_deadline_to_units',45),(60,'2026_04_14_092300_add_vehicle_damaged_to_boundaries',46),(61,'2026_04_14_121315_fix_foreign_key_on_driver_behavior_table',47),(62,'2026_04_14_170000_add_accident_fields_to_driver_behavior',48),(63,'2026_04_14_170001_add_incentive_tracking_to_boundaries',48),(64,'2026_04_14_173000_add_incentive_released_at_to_driver_behavior',49),(65,'2026_04_14_183000_add_is_absent_to_boundaries',50),(67,'2026_04_14_203036_create_driver_behaviors_table',52),(68,'2026_04_14_205413_create_driver_debts_table',52),(69,'2026_04_14_205414_add_debt_payment_to_boundaries',52),(70,'2026_04_14_201942_create_incident_advanced_tables',53),(71,'2026_04_14_222616_create_incident_deep_records_tables',54),(72,'2026_04_14_230001_create_god_level_accident_tables',55),(73,'2026_04_14_230002_add_comprehensive_debt_fields',55),(74,'2026_04_20_090545_add_is_charged_to_driver_to_incident_parts_estimates',56),(75,'2026_04_22_114057_add_soft_deletes_to_driver_behavior_table',57),(76,'2026_04_22_000000_relax_driver_behavior_columns',58),(77,'2026_04_24_112000_repair_incident_involved_parties_column',58),(78,'2026_04_24_161321_add_is_pinned_missing_to_units_table',58),(79,'2026_04_24_165332_add_surveillance_to_unit_status_enum',59),(80,'2026_04_25_174753_add_stock_and_supplier_to_spare_parts_table',60),(81,'2026_04_25_180304_create_suppliers_table',61),(82,'2026_04_25_220445_add_soft_deletes_to_spare_parts',62),(83,'2026_04_25_224513_make_description_nullable_in_maintenance_table',63),(84,'2026_04_26_072100_rename_surveillance_to_at_risk_in_units_status',64),(85,'2026_04_26_192131_add_inventory_link_to_expenses',65),(86,'2026_04_27_000001_add_super_admin_columns_to_users_table',66),(87,'2026_04_27_000002_create_login_audit_table',66),(88,'2026_04_27_104336_create_activity_logs_table',67),(89,'2026_04_28_033707_add_must_change_password_to_users_table',68),(90,'2026_04_27_001600_add_vendor_and_payment_to_expenses',69),(91,'2026_04_27_002000_convert_expenses_category_to_string',69),(92,'2026_04_27_011935_add_in_shop_to_maintenance_status_enum',69),(93,'2026_04_28_162443_create_incident_classifications_table',69),(94,'2026_04_28_170947_add_soft_deletes_to_incident_classifications_table',70),(96,'2026_04_29_112509_add_sub_classification_to_driver_behavior_table',71),(97,'2026_04_30_125848_create_roles_table',72),(98,'2026_04_28_030000_add_source_to_salaries_table',73),(99,'2026_04_28_051833_change_action_to_string_on_login_audit_table',73),(100,'2026_04_29_141949_add_contact_address_deleted_at_to_staff_table',73),(101,'2026_04_29_145016_add_emergency_phone_to_staff_table',73),(102,'2026_04_30_134000_add_disable_fields_to_users_table',73),(103,'2026_04_30_140603_create_system_settings_table',74),(104,'2026_04_30_165010_add_category_to_spare_parts_table',75),(105,'2026_04_30_181629_add_approval_columns_to_expenses_table',76);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `label` varchar(191) NOT NULL,
  `description` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'manager','Manager',NULL,'2026-04-30 04:59:21','2026-04-30 04:59:21',NULL),(2,'dispatcher','Dispatcher',NULL,'2026-04-30 04:59:21','2026-04-30 06:13:46',NULL),(3,'secretary','Secretary',NULL,'2026-04-30 04:59:21','2026-04-30 04:59:21',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salaries`
--

DROP TABLE IF EXISTS `salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `source` varchar(10) DEFAULT 'user',
  `employee_type` varchar(191) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `overtime_pay` decimal(10,2) DEFAULT 0.00,
  `holiday_pay` decimal(10,2) DEFAULT 0.00,
  `night_differential` decimal(10,2) DEFAULT 0.00,
  `allowance` decimal(10,2) DEFAULT 0.00,
  `total_salary` decimal(10,2) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `pay_date` date NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salaries`
--

LOCK TABLES `salaries` WRITE;
/*!40000 ALTER TABLE `salaries` DISABLE KEYS */;
INSERT INTO `salaries` VALUES (2,6,'staff','Mechanic',999.98,0.00,0.00,0.00,0.00,999.98,8,2026,'2026-04-27',18,'2026-04-26 18:02:30','2026-04-26 18:02:30'),(3,6,'staff','Mechanic',499.99,0.00,0.00,0.00,0.00,499.99,4,2026,'2026-04-27',18,'2026-04-26 18:03:10','2026-04-26 18:03:59');
/*!40000 ALTER TABLE `salaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spare_parts`
--

DROP TABLE IF EXISTS `spare_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spare_parts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `category` varchar(191) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `supplier` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spare_parts`
--

LOCK TABLES `spare_parts` WRITE;
/*!40000 ALTER TABLE `spare_parts` DISABLE KEYS */;
INSERT INTO `spare_parts` VALUES (1,'Toyota Genuine Oil Filter',NULL,450.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(2,'Air Filter (Toyota Vios/Hiace)',NULL,850.00,158,'A. BONIFACIO AUTO','2026-04-12 23:50:06','2026-04-30 08:22:12',NULL),(3,'Brake Pads Front (Genuine)',NULL,2450.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(4,'Brake Shoes Rear',NULL,1650.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(5,'Iridium Spark Plugs (Set of 4)',NULL,1800.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(6,'Fully Synthetic Engine Oil (4L)',NULL,2200.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(7,'Toyota Super Long Life Coolant (1L)',NULL,450.00,0,NULL,'2026-04-12 23:50:06','2026-04-30 08:18:36',NULL),(8,'Toyota Genuine Wiper Blade (Set)',NULL,750.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(9,'Fuel Filter (Genuine)',NULL,2800.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(10,'Cabin/AC Filter',NULL,450.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(11,'Serpentine Belt',NULL,950.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(12,'Motolite Gold Battery (NS40)',NULL,4800.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(13,'Brake Fluid (500ml)',NULL,350.00,1,'AMONLATHE WORKS','2026-04-12 23:50:06','2026-04-26 14:28:40',NULL),(14,'ATF / CVT Transmission Fluid (1L)',NULL,650.00,22,NULL,'2026-04-12 23:50:06','2026-04-30 08:20:29',NULL),(15,'Clutch Disc (Genuine)',NULL,3200.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(16,'Release Bearing (Genuine)',NULL,1200.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(17,'Wheel Hub / Bearing Front',NULL,3500.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(18,'Shock Absorber Front (Pair)',NULL,5500.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(19,'Shock Absorber Rear (Pair)',NULL,4200.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(20,'Tie Rod End (Pair)',NULL,1800.00,0,NULL,'2026-04-12 23:50:06','2026-04-12 23:50:06',NULL),(21,'Brake Pads',NULL,1500.00,0,NULL,'2026-04-22 02:30:50','2026-04-22 02:30:50',NULL),(23,'bb',NULL,88.00,23,'A. BONIFACIO AUTO','2026-04-22 11:39:23','2026-04-26 14:16:06',NULL),(26,'jj',NULL,9.00,9,'ABC AUTO PARTS','2026-04-26 15:33:49','2026-04-26 15:33:49',NULL),(27,'brake hose',NULL,500.00,10,'A. BONIFACIO AUTO','2026-04-30 09:34:50','2026-04-30 09:34:50',NULL);
/*!40000 ALTER TABLE `spare_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `role` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `emergency_phone` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Callito A.  Belmar','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:10:20','2026-04-12 23:10:20',NULL),(2,'Nilo E. Dugu','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:11:05','2026-04-12 23:11:05',NULL),(3,'Joel H. Llouido','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:12:00','2026-04-12 23:12:00',NULL),(4,'Marlon P. Nalaluan','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:12:53','2026-04-12 23:13:20',NULL),(5,'Willard A. Nialega','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:13:53','2026-04-12 23:13:53',NULL),(6,'Abran A. Oracion','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:14:24','2026-04-12 23:14:24',NULL),(7,'Rembert V. Tortogo','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:15:05','2026-04-12 23:15:05',NULL),(8,'Mark Ben  O. Arguelles','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:15:43','2026-04-12 23:15:43',NULL),(9,'Manuel M. Lusanta','Mechanic',NULL,NULL,NULL,NULL,'active','2026-04-12 23:16:04','2026-04-12 23:16:04',NULL),(10,'Pagay A. Salvador','Guard',NULL,NULL,NULL,NULL,'active','2026-04-12 23:16:38','2026-04-12 23:16:38',NULL),(11,'Romy M. Tomas','Guard Dispatcher',NULL,NULL,NULL,NULL,'active','2026-04-12 23:17:33','2026-04-12 23:17:33',NULL);
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `phone_number` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'APOLLO ZONE',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(2,'MEGA GRANDIS',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(3,'LUCKY TWO',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(4,'SHARON HUNG',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(5,'A. BONIFACIO AUTO',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(6,'NELSON PROVIDO',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(7,'SAUYO MACHINE SHOP',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(8,'Q.C. TOYORAMA MOTOR CORP.',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(9,'WYL MOTORS',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(10,'ABC AUTO PARTS',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(11,'AMONLATHE WORKS',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(12,'VISCO MOTOR SUPPLY',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(13,'T.A. FRESCO CORP.',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(14,'TRACKSPEED',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(15,'BEST COLT',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(16,'WEST ELM TREE',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(17,'AUTOPHIL ZONE SALES',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(18,'REGASCO GASOLINE',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13'),(19,'ANDALUCIA',NULL,NULL,NULL,NULL,'2026-04-25 10:04:13','2026-04-25 10:04:13');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_alerts`
--

DROP TABLE IF EXISTS `system_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_alerts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'info',
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_alerts_is_resolved_created_at_index` (`is_resolved`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_alerts`
--

LOCK TABLES `system_alerts` WRITE;
/*!40000 ALTER TABLE `system_alerts` DISABLE KEYS */;
INSERT INTO `system_alerts` VALUES (1,'Today\'s Coding Units','There are 16 units restricted today (Monday).','coding_notice',0,NULL,'2026-04-13 13:28:53','2026-04-13 13:28:53'),(2,'Today\'s Coding Units','There are 18 units restricted today (Tuesday).','coding_notice',0,NULL,'2026-04-14 05:24:18','2026-04-14 05:24:18'),(3,'Today\'s Coding Units','There are 18 units restricted today (Tuesday).','coding_notice',0,NULL,'2026-04-21 02:56:43','2026-04-21 02:56:43'),(4,'Today\'s Coding Units','There are 19 units restricted today (Wednesday).','coding_notice',0,NULL,'2026-04-22 01:33:07','2026-04-22 01:33:07'),(5,'Accident Reported: AAK 9196','Driver Sismundo Candelaria reported an accident. Fault: NO. Charge: ₱1,500.00','danger',0,NULL,'2026-04-22 02:34:12','2026-04-22 02:34:12'),(6,'Today\'s Coding Units','There are 22 units restricted today (Friday).','coding_notice',0,NULL,'2026-04-24 01:42:55','2026-04-24 01:42:55'),(7,'🚨 Missing Unit: AAK 9196','Unit AAK 9196 has not remitted a boundary for 1 day(s). The last driver on record is Unknown Driver. Need to locate this unit before another driver can use it.','missing_unit',0,NULL,'2026-04-24 11:20:21','2026-04-24 11:20:21'),(8,'Today\'s Unit Coding','There are 16 units on coding today (Monday).','coding_notice',0,NULL,'2026-04-26 16:25:26','2026-04-26 16:25:26'),(9,'Accident Reported: AAK 4591','Driver Jesus Duero reported an accident. Fault: YES. Charge: ₱1,200.00','danger',0,NULL,'2026-04-27 01:00:03','2026-04-27 01:00:03'),(10,'Accident Reported: DAZ 9769','Driver Roel Peñol reported an accident. Fault: YES. Charge: ₱0.00','danger',0,NULL,'2026-04-27 06:41:27','2026-04-27 06:41:27'),(11,'Accident Reported: NEF 4940','Driver July Sunico reported an accident. Fault: YES. Charge: ₱0.00','danger',0,NULL,'2026-04-27 07:24:05','2026-04-27 07:24:05'),(12,'🚨 Missing Unit: NEF 4940','Unit NEF 4940 has not remitted a boundary for 4 day(s). The last driver on record is July Sunico. Need to locate this unit before another driver can use it.','missing_unit',0,NULL,'2026-04-27 07:45:57','2026-04-30 11:05:14'),(13,'Today\'s Unit Coding','There are 18 units on coding today (Tuesday).','coding_notice',0,NULL,'2026-04-27 18:22:43','2026-04-27 18:22:43'),(14,'🚨 Missing Unit: CAV 9662','Unit CAV 9662 has not remitted a boundary for 3 day(s). The last driver on record is Rodel Gundran. Need to locate this unit before another driver can use it.','missing_unit',0,NULL,'2026-04-28 07:06:14','2026-04-30 11:05:14'),(15,'Today\'s Unit Coding','There are 19 units on coding today (Wednesday).','coding_notice',0,NULL,'2026-04-29 03:43:31','2026-04-29 03:43:31'),(16,'Today\'s Unit Coding','There are 16 units on coding today (Thursday).','coding_notice',0,NULL,'2026-04-30 01:31:09','2026-04-30 01:31:09'),(17,'Accident Reported: NEF 4940','Driver sunibertson sunico reported an accident. Fault: YES. Charge: ₱17,000.00','danger',0,NULL,'2026-04-30 10:01:09','2026-04-30 10:01:09');
/*!40000 ALTER TABLE `system_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(191) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'archive_deletion_password','$2y$10$SIeSgKqpNvGv/A7j7027K.EwI5rTT.arKtoD9z7QixIgbckKNip22','security','2026-04-30 10:25:04','2026-04-30 10:25:04');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_assignments`
--

DROP TABLE IF EXISTS `unit_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `assignment_type` enum('permanent','temporary','relal') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `monthly_target` decimal(10,2) DEFAULT 0.00,
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_driver` (`driver_id`),
  KEY `idx_dates` (`start_date`,`end_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `unit_assignments_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_assignments_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_assignments`
--

LOCK TABLES `unit_assignments` WRITE;
/*!40000 ALTER TABLE `unit_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `unit_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(20) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `motor_no` varchar(191) DEFAULT NULL,
  `chassis_no` varchar(191) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `status` enum('active','maintenance','coding','retired','vacant','at_risk') NOT NULL DEFAULT 'active',
  `is_pinned_missing` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'True if unit is under surveillance/missing',
  `driver_id` int(11) DEFAULT NULL,
  `secondary_driver_id` int(11) DEFAULT NULL,
  `current_turn_driver_id` bigint(20) unsigned DEFAULT NULL,
  `last_swapping_at` timestamp NULL DEFAULT NULL,
  `shift_deadline_at` timestamp NULL DEFAULT NULL,
  `boundary_rate` decimal(10,2) DEFAULT 1100.00,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(12,2) DEFAULT NULL,
  `roi_achieved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unit_type` enum('new','old') DEFAULT 'new',
  `device_installed` tinyint(1) DEFAULT 0,
  `device_installation_date` date DEFAULT NULL,
  `gps_device_count` int(11) DEFAULT 0,
  `gps_link` text DEFAULT NULL,
  `imei` varchar(20) DEFAULT NULL COMMENT 'Tracksolid Pro device identifier',
  `dashcam_device_count` int(11) DEFAULT 0,
  `coding_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `is_coding_exempt` tinyint(1) DEFAULT 0,
  `coding_updated_at` timestamp NULL DEFAULT NULL,
  `max_drivers` int(11) DEFAULT 2,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `driver_id` (`driver_id`),
  KEY `idx_units_roi` (`roi_achieved`),
  KEY `fk_units_secondary_driver` (`secondary_driver_id`),
  KEY `units_imei_index` (`imei`),
  CONSTRAINT `units_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `units_secondary_driver_id_foreign` FOREIGN KEY (`secondary_driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'AAK 9196','Toyota','Vios','2NZ7307868','NCP151-2031009',2015,'active',0,NULL,NULL,NULL,'2026-04-22 11:36:00','2026-04-23 02:00:00',1200.00,NULL,500000.00,0,'2026-04-10 03:29:12','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097285388',0,'Wednesday',0,NULL,2,NULL,18,NULL),(2,'AAQ 1743','Toyota','Vios','2NZ7160776','NCP151-2022506',2014,'maintenance',0,NULL,NULL,NULL,'2026-04-30 08:07:23',NULL,1100.00,NULL,500000.00,0,'2026-04-10 03:29:12','2026-04-30 08:19:08','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,125,NULL),(3,'ABL 6901','Toyota','Vios','2NZ7400896','NCP151-2037524',2015,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:29:12','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(4,'ADY 2597','Toyota','Vios',NULL,NULL,2023,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:29:12','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(5,'ADY 2599','Toyota','Vios',NULL,NULL,2023,'maintenance',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:29:12','2026-04-30 09:39:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(6,'AEA 9630','Toyota','Vios','2NZ7301579','NCP151-2030436',2015,'active',0,NULL,NULL,NULL,'2026-04-26 09:19:56','2026-04-27 09:19:56',1200.00,NULL,500000.00,0,'2026-04-10 03:29:12','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,18,NULL),(7,'ALA 3699','Toyota','Vios','2NZ7384223','NCP151-2036531',2015,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:29:12','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097294869',0,NULL,0,NULL,2,NULL,NULL,NULL),(8,'NAD 1140','Toyota','Vios','1NRX093367','PA1B19F36G4016559',2017,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:29:12','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097292061',0,NULL,0,NULL,2,NULL,NULL,NULL),(12,'ABF 2705','Toyota','Vios',NULL,NULL,2023,'maintenance',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:41:13','2026-04-30 10:18:13','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(17,'EAD 7438','Toyota','Vios','1NRX511105','PA1B13F30K4102617',2020,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:41:13','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(19,'NAM 1610','Toyota','Vios','1NRX265877','PA1B19F33J4055018',2017,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:41:13','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(20,'NCJ 7661','Toyota','Vios',NULL,NULL,2023,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:41:13','2026-04-14 01:14:14','new',0,NULL,0,NULL,'352503097253287',0,NULL,0,NULL,2,NULL,NULL,NULL),(21,'NDA 8102','Toyota','Vios','1NRX399793','PA1B13F30J4076793',2019,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:41:13','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(22,'VFL 543','Toyota','Vios','2NZ6564244','NCP92-964857',2013,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1100.00,NULL,500000.00,0,'2026-04-10 03:41:13','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(51,'CAX 5430','Toyota','Vios','1NRX765584','PA1B18F37N4171824',2022,'active',0,18,NULL,18,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:49:10','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(112,'AAK 4591','Toyota','Vios',NULL,NULL,2014,'coding',0,1,NULL,1,'2026-04-13 14:10:10',NULL,1100.00,NULL,500000.00,0,'2026-04-10 03:53:37','2026-04-29 08:06:28','new',0,NULL,0,NULL,'352503096887481',0,'Monday',0,NULL,2,NULL,125,NULL),(113,'ABF 7471','Toyota','Vios','2NZ7470861','NCP151-2042785',2015,'active',0,NULL,NULL,2,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:37','2026-04-27 06:03:29','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(114,'ABG 7479','Toyota','Vios','2NZ7494105','NCP151-2043398',2015,'active',0,3,NULL,3,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:37','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(115,'ABL 1667','Toyota','Vios','2NZ7542383','NCP151-2046832',2015,'vacant',0,4,NULL,4,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:37','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097303199',0,NULL,0,NULL,2,NULL,NULL,NULL),(116,'ABP 7643','Toyota','Vios','2NZ7541411','NCP151-2046789',2015,'active',0,5,NULL,5,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:37','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503096872566',0,NULL,0,NULL,2,NULL,NULL,NULL),(117,'ACH 5774','Toyota','Vios',NULL,NULL,2023,'vacant',0,6,NULL,6,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:37','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(118,'ADY 2598','Toyota','Vios',NULL,NULL,2023,'vacant',0,7,NULL,7,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:37','2026-04-14 01:14:14','new',0,NULL,0,NULL,'352503097292152',0,NULL,0,NULL,2,NULL,NULL,NULL),(119,'AOA 8917','Toyota','Vios','2NZ7263141','NCP151-2028527',2015,'active',0,8,NULL,8,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:37','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(120,'ASA 6135','Toyota','Vios',NULL,NULL,2023,'active',0,9,NULL,9,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:37','2026-04-14 01:14:14','new',0,NULL,0,NULL,'352503097284233',0,NULL,0,NULL,2,NULL,NULL,NULL),(121,'CAT 6073','Toyota','Vios','1NRX519089','PA1B18F37K4105320',2020,'active',0,10,NULL,10,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:37','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(122,'CAV 2607','Toyota','Vios','1NRX573855','PA1B18F3XL4116880',2020,'active',0,11,NULL,11,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097246554',0,NULL,0,NULL,2,NULL,NULL,NULL),(123,'CAV 6803','Toyota','Vios','1NRX591797','PA1B18F34L4123081',2021,'active',0,12,13,12,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(124,'CAV 9662','Toyota','Vios','1NRX622805','PA1B18F33L4126120',2021,'active',0,14,15,15,'2026-04-26 06:57:54','2026-04-27 06:57:54',1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,18,NULL),(125,'CAV 9716','Toyota','Vios','1NRX622596','PA1B18F33L4125985',2021,'active',0,16,17,16,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(126,'CBM 1979','Toyota','Vios','1NRX665295','PA1B18F3XM4139156',2021,'active',0,19,20,19,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(127,'DAD 7555','Toyota','Vios','1NRX128495','PA1B19F32H4024496',2017,'active',0,21,NULL,21,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(128,'DAJ 7468','Toyota','Vios','1NRX364595','PA1B13F35J4069838',2019,'active',0,22,NULL,22,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(129,'DAT 1367','Toyota','Vios','1NRX586443','PA1B18F37L4121129',2021,'active',0,23,NULL,23,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(130,'DAT 2657','Toyota','Vios',NULL,NULL,2023,'active',0,24,NULL,24,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(131,'DAU 9027','Toyota','Vios','1NRX669745','PA1B18F39M4140346',2021,'active',0,25,26,25,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(132,'DAZ 9769','Toyota','Vios','1NRX539051','PA1B18F35L4109741',2020,'active',0,27,28,27,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(133,'DBA 1887','Toyota','Vios','1NRX544017','PA1B118F30L4110974',2020,'maintenance',0,29,NULL,29,'2026-04-14 04:20:58',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-30 09:40:31','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,18,NULL),(134,'DBA 2302','Toyota','Vios','1NRX530110','PA1B18F38K4108095',2020,'active',0,30,31,30,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(135,'DBA 5420','Toyota','Vios','1NRX554443','PA1B18F3XL4112067',2020,'active',0,32,NULL,32,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(136,'DCQ 1551','Toyota','Vios','1NRX049858','PA1B19F37G4007336',2017,'active',0,33,NULL,33,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503096888661',0,NULL,0,NULL,2,NULL,NULL,NULL),(137,'EAA 4540','Toyota','Vios',NULL,NULL,2023,'active',0,34,NULL,34,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(138,'EAA 9555','Toyota','Vios',NULL,NULL,2023,'active',0,35,NULL,35,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,'352503097289034',0,NULL,0,NULL,2,NULL,NULL,NULL),(139,'EAB 8186','Toyota','Vios',NULL,NULL,2023,'active',0,36,NULL,36,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,'352503097248097',0,NULL,0,NULL,2,NULL,NULL,NULL),(140,'EAE 1247','Toyota','Vios','1NRX570523','PA1B18F35L4115295',2020,'active',0,37,38,37,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(141,'EAE 1919','Toyota','Vios','1NRX684775','PA1B18F354143793',2021,'active',0,39,40,39,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(142,'EAE 4949','Toyota','Vios','1NRX728802','PA1B18F33M4156266',2021,'active',0,41,42,41,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(143,'EAE 5883','Toyota','Vios','1NRX735643','PA1B18F3XM4159021',2021,'active',0,43,44,43,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(144,'EAF 6347','Toyota','Vios','1NRX587947','PA1B18F34L4121976',2021,'active',0,45,NULL,45,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(145,'EAF 7245','Toyota','Vios','1NRX592060','PA1B18F34L4123212',2021,'active',0,46,NULL,46,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(146,'NAC 4989','Toyota','Vios','1NRX072072','PA1B19F3XG4012319',2017,'active',0,47,48,47,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(147,'NAE 7193','Toyota','Vios','1NRX118001','PA1B19F35H4021382',2017,'active',0,49,NULL,49,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(148,'NBR 1341','Toyota','Vios',NULL,NULL,2023,'active',0,50,NULL,50,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(149,'NBW 7071','Toyota','Vios','2NZ7666502','NCP151-2055742',2016,'active',0,51,NULL,51,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503096885121',0,NULL,0,NULL,2,NULL,NULL,NULL),(150,'NBX 4348','Toyota','Vios','1NRX136597','PA1B19F31H4026529',2017,'active',0,52,NULL,52,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(151,'NCN 8583','Toyota','Vios','1NRX142517','PA1B119F30H4027929',2017,'active',0,53,NULL,53,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097297284',0,NULL,0,NULL,2,NULL,NULL,NULL),(152,'NCW 5011','Toyota','Vios','1NRX288337','PA1B19F31J060654',2018,'active',0,54,NULL,54,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097285396',0,NULL,0,NULL,2,NULL,NULL,NULL),(153,'NDA 5429','Toyota','Vios','1NRX382535','PA1B13F37J4074295',2019,'vacant',0,55,NULL,55,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(154,'NAD 8102','Toyota','Vios',NULL,NULL,2023,'active',0,56,57,56,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(155,'NDA 8106','Toyota','Vios','1NRX400695','PA1B13F38J4076895',2019,'active',0,58,NULL,58,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(156,'NDC 7363','Toyota','Vios',NULL,NULL,2023,'active',0,59,NULL,59,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,'352503097248055',0,NULL,0,NULL,2,NULL,NULL,NULL),(157,'NDG 7105','Toyota','Vios','1NRX074746','PA1B19F32G4012928',2017,'active',0,60,NULL,60,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097303249',0,NULL,0,NULL,2,NULL,NULL,NULL),(158,'NDI 2585','Toyota','Vios','1NRX428966','PA1B13F37K4083631',2019,'active',0,61,62,61,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(159,'NEA 1292','Toyota','Vios','1NRX399472','PA1B13F38J4076640',2019,'active',0,63,NULL,63,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(160,'NEF 4940','Toyota','Vios','1NRX507225','PA1B13F31K4102013',2020,'active',0,64,65,64,'2026-04-25 07:41:18','2026-04-26 07:41:18',1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,18,NULL),(161,'NEI 4883','Toyota','Vios','1NRX428108','PA1B119F33K4083254',2019,'active',0,66,NULL,66,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(162,'NEN 2955','Toyota','Vios','1NRX479141','PA1B13F39K4095280',2019,'active',0,67,NULL,67,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(163,'NEN 2957','Toyota','Vios','1NRX478775','PA1B13F37K4095102',2019,'active',0,68,69,68,'2026-04-13 14:10:10',NULL,1300.00,NULL,500000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(164,'NEO 67116','Toyota','Vios',NULL,NULL,2021,'active',0,70,71,70,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(165,'NEP 2440','Toyota','Vios','1NRX662804','PA1B18F32M4138437',2021,'active',0,72,73,72,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(166,'NEP 9750','Toyota','Vios','1NRX670488','PA1B18F33M4140536',2021,'active',0,74,75,74,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(167,'NET 6100','Toyota','Vios',NULL,NULL,2021,'active',0,76,77,76,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(168,'NEU 5546','Toyota','Vios','1NRX494346','PA1B13F39K4098339',2020,'active',0,78,79,78,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(169,'NEV 5065','Toyota','Vios','1NRX728865','PA1B18F35M4156270',2021,'active',0,80,81,80,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(170,'NEW 3821','Toyota','Vios','1NRX699044','PA1B18F32M4147994',2021,'active',0,82,83,82,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(171,'NEW 6279','Toyota','Vios','1NRX711080','PA1B18F33M4150502',2021,'active',0,84,85,84,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(172,'NFH 3664','Toyota','Vios','1NRX758930','PA1B18F35N4169456',2022,'active',0,86,87,86,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(173,'NFZ 8295','Toyota','Vios','1NRX563284','PA1B18F33L4114131',2020,'active',0,88,NULL,88,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(174,'NGA 5044','Toyota','Vios','1NRX513727','PA1B13F32K4103414',2020,'active',0,89,89,89,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(175,'NGA 7736','Toyota','Vios','1NRX585027','PA1B18F33L4120575',2021,'active',0,90,NULL,90,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(176,'NGB 2854','Toyota','Vios','1NRX593170','PA1B18F36L4123549',2021,'active',0,91,NULL,91,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(177,'NGB 6033','Toyota','Vios','1NRX617160','PA1B18F3XL4124719',2021,'active',0,92,NULL,92,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(178,'NGF 1484','Toyota','Vios','1NRX505510','PA134K4101664',2020,'active',0,93,NULL,93,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(179,'NGO 2629','Toyota','Vios','1NRX587826','PA1B18F37L4121826',2021,'active',0,94,95,94,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(180,'NGP 1877','Toyota','Vios',NULL,NULL,2021,'active',0,96,97,96,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(181,'ULO 884','Toyota','Vios',NULL,NULL,2023,'vacant',0,NULL,99,98,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-29 13:16:05','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(182,'UWD 421','Toyota','Vios',NULL,NULL,2023,'active',0,100,NULL,100,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(183,'UWD 431','Toyota','Vios',NULL,NULL,2023,'active',0,101,NULL,101,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(184,'UWN 226','Toyota','Vios',NULL,NULL,2023,'active',0,102,NULL,102,'2026-04-13 14:10:10',NULL,1400.00,NULL,NULL,0,'2026-04-10 03:53:38','2026-04-14 01:14:14','new',0,NULL,0,NULL,NULL,0,NULL,0,NULL,2,NULL,NULL,NULL),(185,'VAA 9864','Toyota','Vios','1NRX676394','PA1B18F39M4141920',2021,'active',0,103,NULL,103,'2026-04-13 14:10:10',NULL,1400.00,NULL,590000.00,0,'2026-04-10 03:53:38','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503097295197',0,NULL,0,NULL,2,NULL,NULL,NULL),(186,'NAN 1349','Toyota','Vios','1NRX560364','PA1B18F35L4113725',2020,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1300.00,NULL,550000.00,0,'2026-04-10 04:28:45','2026-04-26 19:42:02','new',0,NULL,0,NULL,'865784053415173',0,NULL,0,NULL,2,NULL,NULL,NULL),(187,'ABP 2705','Toyota','Vios','2NZ7557953','NCP151-2048091',2015,'vacant',0,NULL,NULL,NULL,'2026-04-13 14:10:10',NULL,1200.00,NULL,500000.00,0,'2026-04-10 04:28:45','2026-04-26 19:42:02','new',0,NULL,0,NULL,'352503096881435',0,NULL,0,NULL,2,NULL,NULL,NULL),(190,'TX-00122','TOYOTA','VIOS',NULL,NULL,2026,'active',0,105,NULL,NULL,NULL,NULL,1100.00,'2026-04-28',500000.00,0,'2026-04-30 07:42:39','2026-04-30 07:43:34','new',0,NULL,0,NULL,NULL,0,'Monday',0,NULL,2,125,125,'2026-04-30 07:43:34');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_recognized_devices`
--

DROP TABLE IF EXISTS `user_recognized_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_recognized_devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `last_active_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_recognized_devices`
--

LOCK TABLES `user_recognized_devices` WRITE;
/*!40000 ALTER TABLE `user_recognized_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_recognized_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `user_id` (`user_id`),
  KEY `idx_session_token` (`session_token`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_verified_browsers`
--

DROP TABLE IF EXISTS `user_verified_browsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_verified_browsers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `browser_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `last_active_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_verified_browsers_browser_token_unique` (`browser_token`),
  KEY `user_verified_browsers_user_id_browser_token_index` (`user_id`,`browser_token`),
  CONSTRAINT `user_verified_browsers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_verified_browsers`
--

LOCK TABLES `user_verified_browsers` WRITE;
/*!40000 ALTER TABLE `user_verified_browsers` DISABLE KEYS */;
INSERT INTO `user_verified_browsers` VALUES (1,18,'5d422c028ac811c1d99686da1a7239361aaa8965b5fe088b70b88b404f15991c','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-13 05:34:52','2026-04-26 20:00:48','2026-04-13 05:34:52','2026-04-26 20:00:48'),(2,18,'3d44d5bf1c3c6f133c0a35f3c9d133966e3a1e3eb88c641e1e159351b31a73e3','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-13 11:43:51','2026-04-27 18:45:58','2026-04-13 11:43:51','2026-04-27 18:45:58'),(3,18,'7b70b784689280b7e3d51ad646a729b12111f12ebf63f71512c0ab34b31e6351','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-26 17:31:34','2026-04-26 17:31:34','2026-04-26 17:31:34','2026-04-26 17:31:34'),(4,125,'ca59afe01f4e5adc3c420388e150d2313cb3c3af29177e71eb51601c611895fe','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 01:57:56','2026-04-27 01:57:56','2026-04-27 01:57:56','2026-04-27 01:57:56'),(5,18,'ef61a22519b0fe2a58c1cd346df4c49b9faddb3c660e0955d1dce832f63c3928','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 02:04:58','2026-04-27 02:04:58','2026-04-27 02:04:58','2026-04-27 02:04:58'),(6,125,'94957a613f2ed3bfde9b0bd228d31c6e155b5fb9d7561cb3394d01709855543e','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 02:10:14','2026-04-27 02:10:14','2026-04-27 02:10:14','2026-04-27 02:10:14'),(7,18,'5b11f102f42bc92ab891faf757c6f9f66ccdcf29f060df19692e6af129099e6c','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 02:13:40','2026-04-27 02:13:40','2026-04-27 02:13:40','2026-04-27 02:13:40'),(8,125,'6f6cf2802f3e97d0091f72b29f803404478a4f38344f43e9342933bdfb9868f3','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 02:15:56','2026-04-27 02:15:56','2026-04-27 02:15:56','2026-04-27 02:15:56'),(9,18,'7479849e564a17ef2702ace087a0a9b12c6435c2c51b12083898762e2372f29c','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 18:43:09','2026-04-27 18:43:09','2026-04-27 18:43:09','2026-04-27 18:43:09'),(10,125,'b0a9ac89ad89142b0aeb6a46163b04480eaff0959febc1da8fc88f60fc1c7aa1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-27 18:44:34','2026-04-27 19:32:34','2026-04-27 18:44:34','2026-04-27 19:32:34'),(14,131,'4035b34e0eca60261b659f548d5c366a25d4bfa49f472c8dedd63f060a3b828f','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-30 11:04:23','2026-04-30 11:04:23','2026-04-30 11:04:23','2026-04-30 11:04:23');
/*!40000 ALTER TABLE `user_verified_browsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Unknown User',
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `first_name` varchar(191) DEFAULT NULL,
  `middle_name` varchar(191) DEFAULT NULL,
  `last_name` varchar(191) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `otp_code` varchar(191) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(50) DEFAULT 'staff',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `allowed_pages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_pages`)),
  `is_active` tinyint(1) DEFAULT 1,
  `is_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `disable_reason` text DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `temp_password` varchar(191) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password` varchar(191) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(191) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (18,'Unknown User','manager-sunibertson','sonysunico02@gmail.com','$2y$10$CVq1neYKgTLqg1FVSdDMkeXzseBwPfATvRzONK3LNi3rjbYQGCqau','sunibertson R. sunico','sunibertson','roncesvalles','sunico',NULL,'09153520035',NULL,NULL,'2026-04-08 02:48:20',1,'Developer','approved',NULL,NULL,'[\"dashboard\",\"units.*\",\"driver-management.*\",\"activity-logs.*\",\"boundaries.*\",\"office-expenses.*\",\"salary.*\",\"salaries.*\",\"maintenance.*\",\"coding.*\",\"driver-behavior.*\",\"live-tracking.*\",\"analytics.*\",\"unit-profitability.*\",\"decision-management.*\",\"staff.*\",\"archive.*\",\"boundary-rules.*\",\"spare-parts.*\",\"suppliers.*\"]',1,0,NULL,0,NULL,NULL,NULL,'2026-04-07 23:51:23','2026-04-30 07:40:58','$2y$10$JNcE.e3Q3m/yOi7.T01wS.kX3qzNqo2pbFo/zmGF5eDdDFvVmDgg.','XPNOawwZnQu24t0KZu084BiJEonNcYEBbq63KLzyOfQKB4vnUWYixXMjNMkl','2026-04-30 07:40:58',NULL,NULL),(125,'Unknown User','super_admin-robert','robertgarcia.owner@gmail.com','$2y$10$W3zSVCSZjBJ7xmO/Z/MGbuo05gl28L7WARB5EGZGe85CWud.iZNuS','Robert Garcia','Robert',NULL,'Garcia',NULL,'09000000000',NULL,NULL,NULL,1,'super_admin','approved',NULL,NULL,NULL,1,0,NULL,0,NULL,NULL,NULL,'2026-04-27 01:26:36','2026-04-30 11:53:53','$2y$10$kc.aYhv6hwGpTtVo8LvX7ulfObGTtFquRUMIfKJbtJdw/Wcvq60eK','DoslstGQqJlpD30OnolOw1R57NHFAIM25cc5iUVY8D7WGfjh2zXpDP5KklWj','2026-04-30 11:53:53',NULL,NULL),(129,'shiella marie orilla','shiellamarieorilla428','shiellamarie.sec@gmail.com','$2y$10$is5i.B6EWLVPW65vr4IHVetpgJg2qPB6UlTcZ3aFvWTv.yZLjb/ny','shiella marie orilla','shiella marie',NULL,'orilla',NULL,NULL,NULL,NULL,NULL,1,'secretary','approved',NULL,NULL,'[\"units.*\",\"driver-management.*\",\"activity-logs.*\",\"maintenance.*\",\"coding.*\",\"driver-behavior.*\",\"spare-parts.*\",\"suppliers.*\",\"boundaries.*\",\"office-expenses.*\",\"boundary-rules.*\",\"staff.*\",\"archive.*\"]',1,0,NULL,1,'JFK129$',NULL,NULL,'2026-04-30 10:20:22','2026-04-30 11:18:40','$2y$10$NpqvosTM.UzZ2S8xe.JkMur.CZySiXDEYtozoMs0Wi5Bnd4j9M1PS',NULL,NULL,NULL,NULL),(130,'rea remitra','rearemitra179','remitra.manager1@gmail.com','$2y$10$VLpyJ5vvTFUzSLg3WnD5Yug1vquD04Rb4jOcH23BndZGzEYtK6Ppi','rea remitra','rea',NULL,'remitra',NULL,NULL,NULL,NULL,NULL,1,'manager','approved',NULL,NULL,'[\"dashboard\",\"units.*\",\"driver-management.*\",\"activity-logs.*\",\"live-tracking.*\",\"maintenance.*\",\"coding.*\",\"driver-behavior.*\",\"spare-parts.*\",\"suppliers.*\",\"boundaries.*\",\"office-expenses.*\",\"salary.*\",\"boundary-rules.*\",\"decision-management.*\",\"staff.*\",\"archive.*\",\"analytics.*\"]',1,0,NULL,1,'SVE967@',NULL,NULL,'2026-04-30 10:21:08','2026-04-30 11:11:51','$2y$10$agYTzNuENMXCToSpK.lPEODA3oIjDTOXFfehPkPzYfjkhgXAQHzxG',NULL,NULL,NULL,NULL),(131,'Romy Thomas','romythomas658','Romy.dispatcher1@gmail.com','$2y$10$AFJm8NznXINElSV2g.fzcOIOJAuNLIk4ZedqlC4KggpIWlz55ocsy','Romy Thomas','Romy',NULL,'Thomas',NULL,NULL,NULL,NULL,NULL,1,'dispatcher','approved',NULL,NULL,'[\"units.*\",\"driver-management.*\",\"live-tracking.*\",\"coding.*\"]',1,0,NULL,0,NULL,NULL,NULL,'2026-04-30 10:21:40','2026-04-30 11:05:09','$2y$10$.8cdVqGYB1/Auw5nIpgxH.dw3y1AR4jlLazzuGzGcz4/JH9kzXiXu','0x4gkwwKTlHVzKm5OFsxLvq4z8iWYIImQ9ub2X0g7gBvWDTKoIX7GGPNzqEA','2026-04-30 11:05:09',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 21:31:55
