CREATE DATABASE IF NOT EXISTS `whatsapp_api`;
USE `whatsapp_api`;

﻿-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: navneet
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
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL DEFAULT 'Default key',
  `key_hash` char(64) NOT NULL,
  `key_prefix` varchar(32) NOT NULL,
  `secret_hash` varchar(255) NOT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `ip_allowlist` text DEFAULT NULL,
  `rate_per_minute` int(10) unsigned NOT NULL DEFAULT 20,
  `rate_per_hour` int(10) unsigned NOT NULL DEFAULT 300,
  `rate_per_day` int(10) unsigned NOT NULL DEFAULT 1000,
  `allow_humanize` tinyint(1) NOT NULL DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_hash` (`key_hash`),
  KEY `idx_api_keys_user_status` (`user_id`,`status`),
  CONSTRAINT `fk_api_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (16,1,'key','96fab6d2a873b79d5ce60db0ef49bae4d19dec4ca99faefee49fd399fa926e96','wa_live_24f504ef8a7990','$2y$10$DV/ZfFQo73M/FY/pURHgT.xJ7DJ/d8DXCHu.EXBD.btabXYTH169K','active',NULL,20,300,1000,1,'2026-09-29 17:52:12','2026-09-29 17:48:21','2026-09-30 06:08:20'),(17,1,'N-Key','f1e0049a73dbc09bd6772ab2b2ff1bc093791b671d7169e1594c8f856bfa7558','wa_live_cb78eb11b575d9','$2y$10$xMcb9RcLhMMNAdWgOaHPZet/a5qGoZ6Yqr8wv0QohTJFZVHhOpJjW','active','185.27.134.37',20,300,1000,1,'2026-09-30 11:42:43','2026-09-30 06:53:52','2026-09-30 11:42:43'),(18,9,'Test Key User 9','e6f0d8f46862498d07d98f2deb579632ac15d27069a3c7b37815b48c40771ede','wa_live_671e8845f7b671','$2y$10$ny4BkZsdbCSUsuM3hiYv7.Nm/q0NCUqsvAG6zsjY0vDOfjyEznXNG','active',NULL,20,300,1000,0,'2026-10-13 09:54:24','2026-10-13 09:47:41','2026-10-13 09:54:24');
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_request_logs`
--

DROP TABLE IF EXISTS `api_request_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_request_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `api_key_id` bigint(20) unsigned DEFAULT NULL,
  `endpoint` varchar(120) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `status_code` int(10) unsigned NOT NULL,
  `request_id` varchar(64) NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_api_request_logs_user_created` (`user_id`,`created_at`),
  KEY `idx_api_request_logs_key_created` (`api_key_id`,`created_at`),
  CONSTRAINT `fk_api_request_logs_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_api_request_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_request_logs`
--

LOCK TABLES `api_request_logs` WRITE;
/*!40000 ALTER TABLE `api_request_logs` DISABLE KEYS */;
INSERT INTO `api_request_logs` VALUES (1,1,NULL,'send','::1',202,'28dc986f3a7b9222c4ad26a4',NULL,'2026-06-02 10:19:09'),(2,1,NULL,'send','::1',202,'59f179cfbb3fc4d4fd5ca8c0',NULL,'2026-06-02 11:20:24'),(3,1,NULL,'send','::1',202,'694f408b036d689a3a3fd3f6',NULL,'2026-06-02 11:26:19'),(4,1,NULL,'send','::1',202,'1515a4735ea582a28db24885',NULL,'2026-06-02 11:26:49'),(5,1,NULL,'send','::1',202,'80f8f4770e9fa5177c3496cc',NULL,'2026-09-29 13:32:00'),(6,1,NULL,'send','::1',202,'2fba35054241739a15f7057f',NULL,'2026-09-29 13:32:34'),(7,1,NULL,'send','::1',202,'6f284b394b6faef51868ea33',NULL,'2026-09-29 15:45:06'),(8,1,16,'send','::1',202,'dcbdad68e527c348558e3b16',NULL,'2026-09-29 17:52:12'),(9,1,17,'send','::1',202,'1bc564d4f8c91e3271d64763',NULL,'2026-09-30 07:34:19'),(10,1,17,'send','::1',202,'c7c7fb9107c7e94bfe430d85',NULL,'2026-09-30 09:33:38'),(11,1,17,'send','::1',202,'f00454d9309bc4669066c309',NULL,'2026-09-30 09:34:15'),(12,1,17,'send','::1',202,'51e5173b21cfa65ec9d3c7ea',NULL,'2026-09-30 09:34:19'),(13,1,17,'send','::1',202,'b77b3b4a37655d76ae2dc8ec',NULL,'2026-09-30 10:25:04'),(14,1,17,'send','::1',202,'196b8700ccc4fd08fadfd152',NULL,'2026-09-30 10:51:10'),(15,1,17,'send','185.27.134.37',202,'bf4f00a7ac7bd7b14b5561f1',NULL,'2026-09-30 10:53:04'),(16,1,17,'send','185.27.134.37',202,'2009d949fe4551ed076e998b',NULL,'2026-09-30 10:54:20'),(17,1,17,'send','185.27.134.37',202,'67976dddd4be5b995e48cf9e',NULL,'2026-09-30 11:42:43'),(18,9,18,'send','::1',422,'9554e9882306d3e5b0281675','Missing fields.','2026-10-13 09:48:09'),(19,9,18,'send','::1',202,'144b92d27451220503d97721',NULL,'2026-10-13 09:48:59'),(20,9,18,'send','::1',202,'f614627c8488678e8ff25fe9',NULL,'2026-10-13 09:51:55'),(21,9,18,'send','::1',202,'5254e62b9fab42ff3b054789',NULL,'2026-10-13 09:54:24');
/*!40000 ALTER TABLE `api_request_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(160) NOT NULL,
  `message_template` text NOT NULL,
  `target_count` int(10) unsigned NOT NULL DEFAULT 0,
  `queued_count` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('queued','completed','cancelled') NOT NULL DEFAULT 'queued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_campaigns_user_created` (`user_id`,`created_at`),
  CONSTRAINT `fk_campaigns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_rules`
--

DROP TABLE IF EXISTS `chatbot_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `match_type` enum('exact','contains','starts_with','catch_all') DEFAULT 'exact',
  `reply_text` text NOT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `chatbot_rules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_rules`
--

LOCK TABLES `chatbot_rules` WRITE;
/*!40000 ALTER TABLE `chatbot_rules` DISABLE KEYS */;
INSERT INTO `chatbot_rules` VALUES (1,1,'Haa','exact','Haa','active','2026-09-30 17:19:09','2026-09-30 17:19:09');
/*!40000 ALTER TABLE `chatbot_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(80) NOT NULL,
  `normalized_phone` varchar(80) DEFAULT NULL,
  `opt_in` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contacts_user_phone` (`user_id`,`phone`),
  KEY `idx_contacts_user` (`user_id`),
  CONSTRAINT `fk_contacts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,1,'navneet yadav','9507286092',NULL,1,NULL,'2026-06-02 10:14:36','2026-06-02 10:14:36'),(5,1,'Mummy','+916202404133',NULL,1,NULL,'2026-09-30 06:49:14','2026-09-30 06:49:14'),(6,9,'Badmash','+919507286092',NULL,1,NULL,'2026-10-13 09:38:40','2026-10-13 09:38:40');
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_email_ip_time` (`email`,`ip_address`,`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_logs`
--

DROP TABLE IF EXISTS `message_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `recipient` varchar(80) NOT NULL,
  `message_preview` varchar(255) NOT NULL,
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_logs_user_created` (`user_id`,`created_at`),
  CONSTRAINT `fk_message_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_logs`
--

LOCK TABLES `message_logs` WRITE;
/*!40000 ALTER TABLE `message_logs` DISABLE KEYS */;
INSERT INTO `message_logs` VALUES (1,1,'9507286092','jajs','failed','{\"ok\":false,\"error\":\"WhatsApp device is not connected.\",\"session\":{\"userId\":\"1\",\"state\":\"idle\",\"qr\":null,\"hasQr\":false,\"phone\":null,\"pushName\":null,\"error\":null},\"status_code\":409}','WhatsApp device is not connected.','2026-06-02 07:16:39'),(2,1,'9507286092','jajs','sent','{\"ok\":true,\"messageId\":\"3EB06487BA53470AB8E37B\",\"status_code\":200}',NULL,'2026-06-02 07:20:41'),(3,1,'9507286092','navneet yadav','sent','{\"ok\":true,\"messageId\":\"3EB0A7BDC811CCCA005ED5\",\"status_code\":200}',NULL,'2026-06-02 07:21:09'),(4,1,'919507286092','navneet yadav','sent','{\"ok\":true,\"messageId\":\"3EB0ABC50BDCC610210BA4\",\"status_code\":200}',NULL,'2026-06-02 07:23:40'),(5,1,'919507286092','hellow','failed','{\"ok\":false,\"normalizedTo\":\"919507286092\",\"status_code\":500}',NULL,'2026-06-02 07:30:15'),(6,1,'919507286092','hellow','sent','{\"ok\":true,\"messageId\":\"3EB0EDE299B98F2B055FBB\",\"normalizedTo\":\"919507286092\",\"status_code\":200}',NULL,'2026-06-02 09:53:59'),(7,1,'919507286092','chut me land','sent','{\"ok\":true,\"messageId\":\"3EB0AD89A0682F3140DA80\",\"normalizedTo\":\"919507286092\",\"status_code\":200}',NULL,'2026-06-02 10:09:23'),(8,1,'919507286092','chut me land t','sent','{\"ok\":true,\"messageId\":\"3EB01C35FE87B5171FFF14\",\"normalizedTo\":\"919507286092\",\"status_code\":200}',NULL,'2026-06-02 10:09:35'),(9,1,'919507286092','chut me land t','sent','{\"ok\":true,\"messageId\":\"3EB0004EE7E870E5786F94\",\"normalizedTo\":\"919507286092\",\"status_code\":200}',NULL,'2026-06-02 10:09:42'),(10,1,'919507286092','chut me land t','sent','{\"ok\":true,\"messageId\":\"3EB0FA59126F892EDC1337\",\"normalizedTo\":\"919507286092\",\"status_code\":200}',NULL,'2026-06-02 10:09:55'),(11,1,'919507286092','ek ke gand me kidaa ghr me land bur me chudaie ka gand me pelaie ka gand','sent','{\"ok\":true,\"messageId\":\"3EB03846EFFCABC56880D4\",\"normalizedTo\":\"919507286092\",\"status_code\":200}',NULL,'2026-06-02 10:10:56'),(12,1,'919507286092','ek ke gand me kidaa ghr me land bur me chudaie ka gand me pelaie ka gand','sent','{\"ok\":true,\"messageId\":\"3EB024B064530E8EAAB3E0\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 10:18:21'),(13,1,'123','verification only','failed',NULL,'Invalid recipient. Use an international number or a 10-digit Indian mobile number.','2026-06-02 10:20:04'),(14,1,'919507286092','hahaha','sent','{\"ok\":true,\"messageId\":\"3EB0F616B19F6DE6D02FDB\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 10:22:29'),(15,1,'919507286092','555','sent','{\"ok\":true,\"messageId\":\"3EB0CDC4981336940EF10B\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:09:00'),(16,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB0B25A8009B2038DA2A4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:20:28'),(17,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB0FB6330A2C11DDA721E\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:26:23'),(18,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB08628D0B8C66FE36FA0\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:26:52'),(124,1,'9507286092','hello','failed',NULL,'WhatsApp device is not connected.','2026-09-29 13:08:29'),(125,1,'916202404133','hello mummy','sent','{\"ok\":true,\"messageId\":\"3EB0849892DC91CB6F8EF8\",\"normalizedTo\":\"916202404133\"}',NULL,'2026-09-29 13:32:06'),(126,1,'919257562834','Hello world','sent','{\"ok\":true,\"messageId\":\"3EB0913CC865C619E6DE52\",\"normalizedTo\":\"919257562834\"}',NULL,'2026-09-29 13:32:39'),(127,1,'123','verification only','failed',NULL,'Invalid recipient. Use an international number or a 10-digit Indian mobile number.','2026-09-29 13:36:00'),(128,1,'919507286092','navneet','sent','{\"ok\":true,\"messageId\":\"3EB02EEEC6135FD1C80140\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-29 15:45:12'),(129,1,'919507286092','Your WhatsApp API Hub password reset OTP is: *736514*\nIt expires in 15 minutes.','sent','{\"ok\":true,\"messageId\":\"3EB0B0E928357414BA173E\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-29 17:52:18'),(130,1,'919507286092','hello','sent','{\"ok\":true,\"messageId\":\"3EB03A6813D6516E2D3B7F\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 02:51:58'),(131,1,'9507286092','nn','failed',NULL,'WhatsApp device is not connected.','2026-09-30 04:47:53'),(132,1,'919507286092','nn','sent','{\"ok\":true,\"messageId\":\"3EB0A134C21211C828257E\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 04:49:08'),(133,1,'919507286092','hllo','sent','{\"ok\":true,\"messageId\":\"3EB05220595F6926E86B60\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:22:18'),(134,1,'919507286092','hii','sent','{\"ok\":true,\"messageId\":\"3EB07D3EA0CCF8C2D32459\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:22:48'),(135,1,'919507286092','hii noob','sent','{\"ok\":true,\"messageId\":\"3EB024AC7B99AFD577502B\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:23:03'),(136,1,'919507286092','xxx','sent','{\"ok\":true,\"messageId\":\"3EB091F72271409089946B\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:23:30'),(137,1,'919507286092','hii','sent','{\"ok\":true,\"messageId\":\"3EB059DCA8876DC7F34C41\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:28:26'),(138,1,'919507286092','hii i am navneet kumar yadav','sent','{\"ok\":true,\"messageId\":\"3EB071CC35E12F2964F468\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:28:49'),(139,1,'919507286092','hii','sent','{\"ok\":true,\"messageId\":\"3EB0F0992903BBCA8AFA56\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:29:31'),(140,1,'919507286092','giui hiakashhsh','sent','{\"ok\":true,\"messageId\":\"3EB05CA83993A392A450E1\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:36:10'),(141,1,'919507286092','hii navneet','sent','{\"ok\":true,\"messageId\":\"3EB00FAC7C8252C49433E3\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:48:58'),(142,1,'9507286092','randi choda','failed',NULL,'Cannot read properties of null (reading \'sendMessage\')','2026-09-30 05:50:24'),(143,1,'919507286092','randi choda','sent','{\"ok\":true,\"messageId\":\"3EB09A58E5FCA0FDA16BD3\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:53:39'),(144,1,'919507286092','Hii this is the dashboard','sent','{\"ok\":true,\"messageId\":\"3EB023C99754C623854500\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:56:31'),(145,1,'919507286092','hii randi','sent','{\"ok\":true,\"messageId\":\"3EB0318D2A552494F16784\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:56:52'),(146,1,'919507286092','Hii how are you','sent','{\"ok\":true,\"messageId\":\"3EB01E79E528BD02BA85C4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 07:33:42'),(147,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB0C2185B9C99DD0C079C\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 07:34:22'),(148,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB0A71D9A7476700B3875\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 09:34:32'),(149,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB05A1A0845B99328A495\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 09:34:37'),(150,1,'919507286092','Hello from WhatsApp API Hub test page.','sent','{\"ok\":true,\"messageId\":\"3EB0722798D1F6A3F1328C\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 09:34:40'),(151,1,'919507286092','hii','sent','{\"ok\":true,\"messageId\":\"3EB02305B51AC74B05188C\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:25:09'),(152,1,'919507286092','Hello My friend','sent','{\"ok\":true,\"messageId\":\"3EB099ED122F8A7A398FD0\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:51:14'),(153,1,'919507286092','naajaj','sent','{\"ok\":true,\"messageId\":\"3EB08A14F5E79F0CD50EC4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:53:10'),(154,1,'919507286092','ajajaj','sent','{\"ok\":true,\"messageId\":\"3EB0B8F280102C4D1A02D4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:54:28'),(155,1,'919507286092','ajajaj','sent','{\"ok\":true,\"messageId\":\"3EB0778FD04DB5BBAADC24\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 11:48:58'),(156,1,'28343109968029','Haa','failed',NULL,'The number 28343109968029 is not registered on WhatsApp.','2026-09-30 11:51:00'),(157,1,'28343109968029','Haa','failed',NULL,'The number 28343109968029 is not registered on WhatsApp.','2026-09-30 11:51:36'),(158,1,'28343109968029','Haa','failed',NULL,'The number 28343109968029 is not registered on WhatsApp.','2026-09-30 12:06:58'),(159,1,'28343109968029@lid','Haa','failed',NULL,'WhatsApp device is not connected.','2026-09-30 12:11:29'),(160,1,'28343109968029@lid','Haa','failed',NULL,'WhatsApp device is not connected.','2026-09-30 12:17:30'),(161,1,'28343109968029@lid','Haa','failed',NULL,'WhatsApp device is not connected.','2026-09-30 12:18:08'),(162,9,'9507286092','gello rand','sent','{\"ok\":true,\"messageId\":\"3EB08CAEFEE0DAFA1A2300\",\"normalizedTo\":\"9507286092\"}',NULL,'2026-10-13 09:31:01'),(163,9,'9507286092','Hii','sent','{\"ok\":true,\"messageId\":\"3EB0F118C19044BFBD1F43\",\"normalizedTo\":\"9507286092\"}',NULL,'2026-10-13 09:32:08'),(164,9,'919507286092','Hello Navneet! This is a test message via your WhatsApp API Key.','sent','{\"ok\":true,\"messageId\":\"3EB04A4FBDCB5098006D14\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-10-13 09:49:03'),(165,9,'919507286092','Testing via test page interface 3','sent','{\"ok\":true,\"messageId\":\"3EB0EF285BF22438AE5697\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-10-13 09:52:00'),(166,9,'919507286092','Hello Navneet! This is a test message via your WhatsApp API Key.','sent','{\"ok\":true,\"messageId\":\"3EB099EAF8B475168F07CD\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-10-13 09:54:28');
/*!40000 ALTER TABLE `message_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_queue`
--

DROP TABLE IF EXISTS `message_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `api_key_id` bigint(20) unsigned DEFAULT NULL,
  `recipient` varchar(80) NOT NULL,
  `normalized_recipient` varchar(80) DEFAULT NULL,
  `message_type` enum('text','image','document','video') NOT NULL DEFAULT 'text',
  `body` text NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `status` enum('queued','processing','sent','failed','cancelled') NOT NULL DEFAULT 'queued',
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `max_attempts` int(10) unsigned NOT NULL DEFAULT 3,
  `available_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` varchar(80) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `provider_message_id` varchar(120) DEFAULT NULL,
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_queue_worker` (`status`,`available_at`,`locked_at`),
  KEY `idx_message_queue_user_created` (`user_id`,`created_at`),
  KEY `idx_message_queue_api_key_created` (`api_key_id`,`created_at`),
  CONSTRAINT `fk_message_queue_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_message_queue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_queue`
--

LOCK TABLES `message_queue` WRITE;
/*!40000 ALTER TABLE `message_queue` DISABLE KEYS */;
INSERT INTO `message_queue` VALUES (1,1,NULL,'9507286092','919507286092','text','ek ke gand me kidaa ghr me land bur me chudaie ka gand me pelaie ka gand','{\"source\":\"dashboard\",\"type\":\"text\"}','sent',1,3,'2026-06-02 10:14:02',NULL,NULL,'2026-06-02 10:18:21','3EB024B064530E8EAAB3E0','{\"ok\":true,\"messageId\":\"3EB024B064530E8EAAB3E0\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 10:14:02','2026-06-02 10:18:21'),(2,1,NULL,'123','123','text','verification only','{\"source\":\"api\",\"type\":\"text\"}','failed',3,3,'2026-09-29 13:36:45',NULL,NULL,NULL,NULL,NULL,'Invalid recipient. Use an international number or a 10-digit Indian mobile number.','2026-06-02 10:19:09','2026-09-29 13:36:00'),(3,1,NULL,'9507286092\'','919507286092','text','hahaha','{\"source\":\"dashboard\",\"type\":\"text\"}','sent',1,3,'2026-06-02 10:22:23',NULL,NULL,'2026-06-02 10:22:29','3EB0F616B19F6DE6D02FDB','{\"ok\":true,\"messageId\":\"3EB0F616B19F6DE6D02FDB\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 10:22:23','2026-06-02 10:22:29'),(4,1,NULL,'9507286092','919507286092','text','555','{\"source\":\"dashboard\",\"type\":\"text\"}','sent',1,3,'2026-06-02 11:08:54',NULL,NULL,'2026-06-02 11:09:00','3EB0CDC4981336940EF10B','{\"ok\":true,\"messageId\":\"3EB0CDC4981336940EF10B\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:08:54','2026-06-02 11:09:00'),(5,1,NULL,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-06-02 11:20:24',NULL,NULL,'2026-06-02 11:20:28','3EB0B25A8009B2038DA2A4','{\"ok\":true,\"messageId\":\"3EB0B25A8009B2038DA2A4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:20:24','2026-06-02 11:20:28'),(6,1,NULL,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-06-02 11:26:19',NULL,NULL,'2026-06-02 11:26:23','3EB0FB6330A2C11DDA721E','{\"ok\":true,\"messageId\":\"3EB0FB6330A2C11DDA721E\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:26:19','2026-06-02 11:26:23'),(7,1,NULL,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-06-02 11:26:49',NULL,NULL,'2026-06-02 11:26:52','3EB08628D0B8C66FE36FA0','{\"ok\":true,\"messageId\":\"3EB08628D0B8C66FE36FA0\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-06-02 11:26:49','2026-06-02 11:26:52'),(112,1,NULL,'9507286092','919507286092','text','hello','{\"source\":\"manual\",\"type\":\"text\"}','sent',1,3,'2026-09-30 02:51:53',NULL,NULL,'2026-09-30 02:51:58','3EB03A6813D6516E2D3B7F','{\"ok\":true,\"messageId\":\"3EB03A6813D6516E2D3B7F\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-14 20:45:10','2026-09-30 02:51:58'),(113,1,NULL,'6202404133','916202404133','text','hello mummy','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-29 13:32:00',NULL,NULL,'2026-09-29 13:32:06','3EB0849892DC91CB6F8EF8','{\"ok\":true,\"messageId\":\"3EB0849892DC91CB6F8EF8\",\"normalizedTo\":\"916202404133\"}',NULL,'2026-09-29 13:32:00','2026-09-29 13:32:06'),(114,1,NULL,'9257562834','919257562834','text','Hello world','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-29 13:32:34',NULL,NULL,'2026-09-29 13:32:39','3EB0913CC865C619E6DE52','{\"ok\":true,\"messageId\":\"3EB0913CC865C619E6DE52\",\"normalizedTo\":\"919257562834\"}',NULL,'2026-09-29 13:32:34','2026-09-29 13:32:39'),(115,1,NULL,'919507286092','919507286092','text','navneet','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-29 15:45:06',NULL,NULL,'2026-09-29 15:45:12','3EB02EEEC6135FD1C80140','{\"ok\":true,\"messageId\":\"3EB02EEEC6135FD1C80140\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-29 15:45:06','2026-09-29 15:45:12'),(116,1,16,'9507286092','919507286092','text','Your WhatsApp API Hub password reset OTP is: *736514*\nIt expires in 15 minutes.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-29 17:52:12',NULL,NULL,'2026-09-29 17:52:18','3EB0B0E928357414BA173E','{\"ok\":true,\"messageId\":\"3EB0B0E928357414BA173E\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-29 17:52:12','2026-09-29 17:52:18'),(117,1,NULL,'9507286092','919507286092','text','nn','[]','sent',1,3,'2026-09-30 04:49:01',NULL,NULL,'2026-09-30 04:49:08','3EB0A134C21211C828257E','{\"ok\":true,\"messageId\":\"3EB0A134C21211C828257E\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 04:17:12','2026-09-30 04:49:08'),(118,1,NULL,'9507286092','919507286092','text','hllo','{\"media_url\":\"/whatsapp-api/public/uploads/media_6abc9c73cbed1.pdf\"}','sent',2,3,'2026-09-30 05:22:15',NULL,NULL,'2026-09-30 05:22:18','3EB05220595F6926E86B60','{\"ok\":true,\"messageId\":\"3EB05220595F6926E86B60\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:21:55','2026-09-30 05:22:18'),(119,1,NULL,'9507286092','919507286092','text','hii','[]','sent',1,3,'2026-09-30 05:22:43',NULL,NULL,'2026-09-30 05:22:48','3EB07D3EA0CCF8C2D32459','{\"ok\":true,\"messageId\":\"3EB07D3EA0CCF8C2D32459\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:22:43','2026-09-30 05:22:48'),(120,1,NULL,'9507286092','919507286092','text','hii noob','[]','sent',1,3,'2026-09-30 05:22:59',NULL,NULL,'2026-09-30 05:23:03','3EB024AC7B99AFD577502B','{\"ok\":true,\"messageId\":\"3EB024AC7B99AFD577502B\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:22:59','2026-09-30 05:23:03'),(121,1,NULL,'9507286092','919507286092','text','xxx','{\"media_url\":\"/whatsapp-api/public/uploads/media_6abc9ccdd33b5.pdf\"}','sent',1,3,'2026-09-30 05:23:25',NULL,NULL,'2026-09-30 05:23:30','3EB091F72271409089946B','{\"ok\":true,\"messageId\":\"3EB091F72271409089946B\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:23:25','2026-09-30 05:23:30'),(122,1,NULL,'9507286092','919507286092','text','hii','{\"media_url\":\"/whatsapp-api/public/uploads/media_6abc9df67dfb5.jpeg\"}','sent',1,3,'2026-09-30 05:28:22',NULL,NULL,'2026-09-30 05:28:26','3EB059DCA8876DC7F34C41','{\"ok\":true,\"messageId\":\"3EB059DCA8876DC7F34C41\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:28:22','2026-09-30 05:28:26'),(123,1,NULL,'9507286092','919507286092','text','hii i am navneet kumar yadav','[]','sent',1,3,'2026-09-30 05:28:44',NULL,NULL,'2026-09-30 05:28:49','3EB071CC35E12F2964F468','{\"ok\":true,\"messageId\":\"3EB071CC35E12F2964F468\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:28:44','2026-09-30 05:28:49'),(124,1,NULL,'9507286092','919507286092','text','hii','[]','sent',1,3,'2026-09-30 05:29:26',NULL,NULL,'2026-09-30 05:29:31','3EB0F0992903BBCA8AFA56','{\"ok\":true,\"messageId\":\"3EB0F0992903BBCA8AFA56\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:29:26','2026-09-30 05:29:31'),(125,1,NULL,'9507286092','919507286092','text','giui hiakashhsh','{\"media_url\":\"/whatsapp-api/public/uploads/media_6abc9fc3c411e.pdf\",\"media_path\":\"C:\\\\xampp\\\\htdocs\\\\whatsapp-api/public/uploads/media_6abc9fc3c411e.pdf\"}','sent',1,3,'2026-09-30 05:36:03',NULL,NULL,'2026-09-30 05:36:10','3EB05CA83993A392A450E1','{\"ok\":true,\"messageId\":\"3EB05CA83993A392A450E1\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:36:03','2026-09-30 05:36:10'),(126,1,NULL,'9507286092','919507286092','text','hii navneet','{\"attachment\":{\"path\":\"C:\\\\xampp\\\\htdocs\\\\whatsapp-api/public/uploads/media_6abca2c176436.pdf\",\"name\":\"BCCCO_2025_2597484 (1).pdf\",\"type\":\"document\",\"mime\":\"application/pdf\"},\"humanize\":true}','sent',1,3,'2026-09-30 05:48:49',NULL,NULL,'2026-09-30 05:48:58','3EB00FAC7C8252C49433E3','{\"ok\":true,\"messageId\":\"3EB00FAC7C8252C49433E3\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:48:49','2026-09-30 05:48:58'),(127,1,NULL,'9507286092','919507286092','text','randi choda','{\"humanize\":true}','sent',1,3,'2026-09-30 05:53:32',NULL,NULL,'2026-09-30 05:53:39','3EB09A58E5FCA0FDA16BD3','{\"ok\":true,\"messageId\":\"3EB09A58E5FCA0FDA16BD3\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:49:21','2026-09-30 05:53:39'),(128,1,NULL,'9507286092','919507286092','text','Hii this is the dashboard','{\"attachment\":{\"path\":\"C:\\\\xampp\\\\htdocs\\\\whatsapp-api/public/uploads/media_6abca481bd186.jpeg\",\"name\":\"WhatsApp Image 2026-09-29 at 18.27.16.jpeg\",\"type\":\"image\",\"mime\":\"image/jpeg\"},\"humanize\":true}','sent',1,3,'2026-09-30 05:56:17',NULL,NULL,'2026-09-30 05:56:31','3EB023C99754C623854500','{\"ok\":true,\"messageId\":\"3EB023C99754C623854500\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:56:17','2026-09-30 05:56:31'),(129,1,NULL,'9507286092','919507286092','text','hii randi','{\"humanize\":true}','sent',1,3,'2026-09-30 05:56:44',NULL,NULL,'2026-09-30 05:56:52','3EB0318D2A552494F16784','{\"ok\":true,\"messageId\":\"3EB0318D2A552494F16784\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 05:56:44','2026-09-30 05:56:52'),(130,1,NULL,'9507286092','919507286092','text','Hii how are you','{\"humanize\":true}','sent',1,3,'2026-09-30 07:33:32',NULL,NULL,'2026-09-30 07:33:42','3EB01E79E528BD02BA85C4','{\"ok\":true,\"messageId\":\"3EB01E79E528BD02BA85C4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 07:33:32','2026-09-30 07:33:42'),(131,1,17,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 07:34:19',NULL,NULL,'2026-09-30 07:34:22','3EB0C2185B9C99DD0C079C','{\"ok\":true,\"messageId\":\"3EB0C2185B9C99DD0C079C\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 07:34:19','2026-09-30 07:34:22'),(132,1,17,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 09:33:38',NULL,NULL,'2026-09-30 09:34:32','3EB0A71D9A7476700B3875','{\"ok\":true,\"messageId\":\"3EB0A71D9A7476700B3875\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 09:33:38','2026-09-30 09:34:32'),(133,1,17,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 09:34:15',NULL,NULL,'2026-09-30 09:34:37','3EB05A1A0845B99328A495','{\"ok\":true,\"messageId\":\"3EB05A1A0845B99328A495\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 09:34:15','2026-09-30 09:34:37'),(134,1,17,'9507286092','919507286092','text','Hello from WhatsApp API Hub test page.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 09:34:19',NULL,NULL,'2026-09-30 09:34:40','3EB0722798D1F6A3F1328C','{\"ok\":true,\"messageId\":\"3EB0722798D1F6A3F1328C\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 09:34:19','2026-09-30 09:34:40'),(135,1,17,'9507286092','919507286092','text','hii','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 10:25:04',NULL,NULL,'2026-09-30 10:25:09','3EB02305B51AC74B05188C','{\"ok\":true,\"messageId\":\"3EB02305B51AC74B05188C\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:25:04','2026-09-30 10:25:09'),(136,1,17,'9507286092','919507286092','text','Hello My friend','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 10:51:10',NULL,NULL,'2026-09-30 10:51:14','3EB099ED122F8A7A398FD0','{\"ok\":true,\"messageId\":\"3EB099ED122F8A7A398FD0\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:51:10','2026-09-30 10:51:14'),(137,1,17,'9507286092','919507286092','text','naajaj','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-09-30 10:53:04',NULL,NULL,'2026-09-30 10:53:10','3EB08A14F5E79F0CD50EC4','{\"ok\":true,\"messageId\":\"3EB08A14F5E79F0CD50EC4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:53:04','2026-09-30 10:53:10'),(138,1,17,'9507286092','919507286092','text','ajajaj','{\"source\":\"api\",\"type\":\"text\",\"humanize\":true}','sent',1,3,'2026-09-30 10:54:20',NULL,NULL,'2026-09-30 10:54:28','3EB0B8F280102C4D1A02D4','{\"ok\":true,\"messageId\":\"3EB0B8F280102C4D1A02D4\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 10:54:20','2026-09-30 10:54:28'),(139,1,17,'9507286092','919507286092','text','ajajaj','{\"source\":\"api\",\"type\":\"text\",\"humanize\":true}','sent',1,3,'2026-09-30 11:42:43',NULL,NULL,'2026-09-30 11:48:58','3EB0778FD04DB5BBAADC24','{\"ok\":true,\"messageId\":\"3EB0778FD04DB5BBAADC24\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-09-30 11:42:43','2026-09-30 11:48:58'),(140,1,NULL,'28343109968029@lid','28343109968029','text','Haa','{\"source\":\"chatbot\",\"rule_id\":1,\"type\":\"text\"}','failed',3,3,'2026-09-30 12:12:14',NULL,NULL,NULL,NULL,NULL,'WhatsApp device is not connected.','2026-09-30 11:50:06','2026-09-30 12:11:29'),(141,1,NULL,'28343109968029@lid','28343109968029','text','Haa','{\"source\":\"chatbot\",\"rule_id\":1,\"type\":\"text\"}','failed',3,3,'2026-09-30 12:18:15',NULL,NULL,NULL,NULL,NULL,'WhatsApp device is not connected.','2026-09-30 11:50:42','2026-09-30 12:17:30'),(142,1,NULL,'28343109968029@lid','28343109968029','text','Haa','{\"source\":\"chatbot\",\"rule_id\":1,\"type\":\"text\"}','failed',3,3,'2026-09-30 12:18:53',NULL,NULL,NULL,NULL,NULL,'WhatsApp device is not connected.','2026-09-30 12:06:03','2026-09-30 12:18:08'),(143,9,NULL,'95072 6092','950726092','text','Hello RAND','{\"humanize\":true}','cancelled',2,3,'2026-10-13 09:30:20',NULL,NULL,NULL,NULL,NULL,'The number 950726092 is not registered on WhatsApp.','2026-10-13 09:29:28','2026-10-13 09:30:18'),(144,9,NULL,'95072 86092','9507286092','text','gello rand','{\"humanize\":true}','sent',1,3,'2026-10-13 09:30:48',NULL,NULL,'2026-10-13 09:31:00','3EB08CAEFEE0DAFA1A2300','{\"ok\":true,\"messageId\":\"3EB08CAEFEE0DAFA1A2300\",\"normalizedTo\":\"9507286092\"}',NULL,'2026-10-13 09:30:48','2026-10-13 09:31:00'),(145,9,NULL,'95072 86092','9507286092','text','Hii','{\"humanize\":true}','sent',1,3,'2026-10-13 09:31:58',NULL,NULL,'2026-10-13 09:32:08','3EB0F118C19044BFBD1F43','{\"ok\":true,\"messageId\":\"3EB0F118C19044BFBD1F43\",\"normalizedTo\":\"9507286092\"}',NULL,'2026-10-13 09:31:58','2026-10-13 09:32:08'),(146,9,18,'+919507286092','919507286092','text','Hello Navneet! This is a test message via your WhatsApp API Key.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-10-13 09:48:59',NULL,NULL,'2026-10-13 09:49:03','3EB04A4FBDCB5098006D14','{\"ok\":true,\"messageId\":\"3EB04A4FBDCB5098006D14\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-10-13 09:48:59','2026-10-13 09:49:03'),(147,9,18,'+919507286092','919507286092','text','Testing via test page interface 3','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-10-13 09:51:55',NULL,NULL,'2026-10-13 09:52:00','3EB0EF285BF22438AE5697','{\"ok\":true,\"messageId\":\"3EB0EF285BF22438AE5697\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-10-13 09:51:55','2026-10-13 09:52:00'),(148,9,18,'+919507286092','919507286092','text','Hello Navneet! This is a test message via your WhatsApp API Key.','{\"source\":\"api\",\"type\":\"text\"}','sent',1,3,'2026-10-13 09:54:24',NULL,NULL,'2026-10-13 09:54:28','3EB099EAF8B475168F07CD','{\"ok\":true,\"messageId\":\"3EB099EAF8B475168F07CD\",\"normalizedTo\":\"919507286092\"}',NULL,'2026-10-13 09:54:24','2026-10-13 09:54:28');
/*!40000 ALTER TABLE `message_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_contacts`
--

DROP TABLE IF EXISTS `user_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `country_code` varchar(10) NOT NULL DEFAULT '+91',
  `display_name` varchar(150) NOT NULL,
  `contact_unique_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_contact_unique` (`user_id`,`contact_phone`),
  KEY `idx_contact_unique_id` (`contact_unique_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_contact_phone` (`contact_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_contacts`
--

LOCK TABLES `user_contacts` WRITE;
/*!40000 ALTER TABLE `user_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `api_key_hash` char(64) DEFAULT NULL,
  `api_key_prefix` varchar(32) DEFAULT NULL,
  `api_secret_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Navneet Kumar Yadav','navneetyadav982008@gmail.com','9507286092','$2y$10$YFw8Dduqg72lMevXa2t2Nu/15VrBXHTBV9IH4EoYBIWkYqxN2.Z4O','58901c3bfb7c80d3923f6a2f9705de7a80b751a4e63513ea060eb2768f91163d','wa_live_f89b0312c06de1','$2y$10$9m3OyoMuxsOOnWLNgx9aMuYPtUOppNmHHNSI.G9IO9BIsBT3W1xGK','2026-06-02 07:16:18','2026-09-29 17:52:41'),(9,'Unknown Kumar','unknownkumar1113@gmail.com','','$2y$10$jlcGR.5IU6sOQIA82aaapuKuL/B0n3Fn6H6EtDfghIctY6p9W8cXy',NULL,NULL,NULL,'2026-10-13 08:57:12','2026-10-13 08:57:12');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_deliveries`
--

DROP TABLE IF EXISTS `webhook_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhook_deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `webhook_id` bigint(20) unsigned DEFAULT NULL,
  `message_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(80) NOT NULL,
  `target_url` varchar(500) NOT NULL,
  `status_code` int(10) unsigned DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `attempt` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_webhook_deliveries_webhook` (`webhook_id`),
  KEY `idx_webhook_deliveries_user_created` (`user_id`,`created_at`),
  KEY `idx_webhook_deliveries_message` (`message_id`),
  CONSTRAINT `fk_webhook_deliveries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_webhook_deliveries_webhook` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_deliveries`
--

LOCK TABLES `webhook_deliveries` WRITE;
/*!40000 ALTER TABLE `webhook_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `secret` varchar(120) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_webhooks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooks`
--

LOCK TABLES `webhooks` WRITE;
/*!40000 ALTER TABLE `webhooks` DISABLE KEYS */;
INSERT INTO `webhooks` VALUES (2,1,NULL,NULL,0,'2026-06-02 10:09:26','2026-06-02 10:09:26'),(9,9,NULL,NULL,0,'2026-10-13 08:57:12','2026-10-13 08:57:12');
/*!40000 ALTER TABLE `webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_sessions`
--

DROP TABLE IF EXISTS `whatsapp_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` enum('idle','connecting','qr','connected','disconnected','error') NOT NULL DEFAULT 'idle',
  `sleeping` tinyint(1) NOT NULL DEFAULT 0,
  `phone` varchar(80) DEFAULT NULL,
  `push_name` varchar(120) DEFAULT NULL,
  `qr_updated_at` timestamp NULL DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_whatsapp_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_sessions`
--

LOCK TABLES `whatsapp_sessions` WRITE;
/*!40000 ALTER TABLE `whatsapp_sessions` DISABLE KEYS */;
INSERT INTO `whatsapp_sessions` VALUES (1,1,'disconnected',0,NULL,NULL,'2026-10-13 05:44:52',NULL,'2026-10-13 09:21:02','QR refs attempts ended','2026-06-02 07:16:18','2026-10-13 09:21:02'),(8,9,'connected',0,'917491954139','Assist Group',NULL,'2026-10-13 07:08:58',NULL,NULL,'2026-10-13 08:57:12','2026-10-13 10:38:58');
/*!40000 ALTER TABLE `whatsapp_sessions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13 16:09:00
