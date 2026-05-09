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
INSERT INTO `users` VALUES (18,'Unknown User','manager-sunibertson','sonysunico02@gmail.com','$2y$10$CVq1neYKgTLqg1FVSdDMkeXzseBwPfATvRzONK3LNi3rjbYQGCqau','sunibertson R. sunico','sunibertson','roncesvalles','sunico',NULL,'09153520035',NULL,NULL,'2026-04-08 02:48:20',1,'Developer','approved',NULL,NULL,'[\"dashboard\",\"units.*\",\"driver-management.*\",\"activity-logs.*\",\"boundaries.*\",\"office-expenses.*\",\"salary.*\",\"salaries.*\",\"maintenance.*\",\"coding.*\",\"driver-behavior.*\",\"live-tracking.*\",\"analytics.*\",\"unit-profitability.*\",\"decision-management.*\",\"staff.*\",\"archive.*\",\"boundary-rules.*\",\"spare-parts.*\",\"suppliers.*\"]',1,0,NULL,0,NULL,NULL,NULL,'2026-04-07 23:51:23','2026-04-30 07:40:58','$2y$10$JNcE.e3Q3m/yOi7.T01wS.kX3qzNqo2pbFo/zmGF5eDdDFvVmDgg.','XPNOawwZnQu24t0KZu084BiJEonNcYEBbq63KLzyOfQKB4vnUWYixXMjNMkl','2026-04-30 07:40:58',NULL,NULL),(125,'Unknown User','super_admin-robert','robertgarcia.owner@gmail.com','$2y$10$W3zSVCSZjBJ7xmO/Z/MGbuo05gl28L7WARB5EGZGe85CWud.iZNuS','Robert Garcia','Robert',NULL,'Garcia',NULL,'09000000000',NULL,NULL,NULL,1,'super_admin','approved',NULL,NULL,NULL,1,0,NULL,0,NULL,NULL,NULL,'2026-04-27 01:26:36','2026-04-30 11:25:19','$2y$10$kc.aYhv6hwGpTtVo8LvX7ulfObGTtFquRUMIfKJbtJdw/Wcvq60eK','DoslstGQqJlpD30OnolOw1R57NHFAIM25cc5iUVY8D7WGfjh2zXpDP5KklWj','2026-04-30 10:58:05',NULL,NULL),(129,'shiella marie orilla','shiellamarieorilla428','shiellamarie.sec@gmail.com','$2y$10$is5i.B6EWLVPW65vr4IHVetpgJg2qPB6UlTcZ3aFvWTv.yZLjb/ny','shiella marie orilla','shiella marie',NULL,'orilla',NULL,NULL,NULL,NULL,NULL,1,'secretary','approved',NULL,NULL,'[\"units.*\",\"driver-management.*\",\"activity-logs.*\",\"maintenance.*\",\"coding.*\",\"driver-behavior.*\",\"spare-parts.*\",\"suppliers.*\",\"boundaries.*\",\"office-expenses.*\",\"boundary-rules.*\",\"staff.*\",\"archive.*\"]',1,0,NULL,1,'JFK129$',NULL,NULL,'2026-04-30 10:20:22','2026-04-30 11:18:40','$2y$10$NpqvosTM.UzZ2S8xe.JkMur.CZySiXDEYtozoMs0Wi5Bnd4j9M1PS',NULL,NULL,NULL,NULL),(130,'rea remitra','rearemitra179','remitra.manager1@gmail.com','$2y$10$VLpyJ5vvTFUzSLg3WnD5Yug1vquD04Rb4jOcH23BndZGzEYtK6Ppi','rea remitra','rea',NULL,'remitra',NULL,NULL,NULL,NULL,NULL,1,'manager','approved',NULL,NULL,'[\"dashboard\",\"units.*\",\"driver-management.*\",\"activity-logs.*\",\"live-tracking.*\",\"maintenance.*\",\"coding.*\",\"driver-behavior.*\",\"spare-parts.*\",\"suppliers.*\",\"boundaries.*\",\"office-expenses.*\",\"salary.*\",\"boundary-rules.*\",\"decision-management.*\",\"staff.*\",\"archive.*\",\"analytics.*\"]',1,0,NULL,1,'SVE967@',NULL,NULL,'2026-04-30 10:21:08','2026-04-30 11:11:51','$2y$10$agYTzNuENMXCToSpK.lPEODA3oIjDTOXFfehPkPzYfjkhgXAQHzxG',NULL,NULL,NULL,NULL),(131,'Romy Thomas','romythomas658','Romy.dispatcher1@gmail.com','$2y$10$AFJm8NznXINElSV2g.fzcOIOJAuNLIk4ZedqlC4KggpIWlz55ocsy','Romy Thomas','Romy',NULL,'Thomas',NULL,NULL,NULL,NULL,NULL,1,'dispatcher','approved',NULL,NULL,'[\"units.*\",\"driver-management.*\",\"live-tracking.*\",\"coding.*\"]',1,0,NULL,0,NULL,NULL,NULL,'2026-04-30 10:21:40','2026-04-30 11:05:09','$2y$10$.8cdVqGYB1/Auw5nIpgxH.dw3y1AR4jlLazzuGzGcz4/JH9kzXiXu','0x4gkwwKTlHVzKm5OFsxLvq4z8iWYIImQ9ub2X0g7gBvWDTKoIX7GGPNzqEA','2026-04-30 11:05:09',NULL,NULL);
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

-- Dump completed on 2026-04-30 19:51:41
