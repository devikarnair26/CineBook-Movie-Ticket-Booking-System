-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: movie_booking
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `seat`
--

DROP TABLE IF EXISTS `seat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seat` (
  `seat_id` int NOT NULL AUTO_INCREMENT,
  `screen_id` int NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `seat_type` enum('Regular','Premium') DEFAULT 'Regular',
  PRIMARY KEY (`seat_id`),
  UNIQUE KEY `screen_id` (`screen_id`,`seat_number`),
  CONSTRAINT `seat_ibfk_1` FOREIGN KEY (`screen_id`) REFERENCES `screen` (`screen_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seat`
--

LOCK TABLES `seat` WRITE;
/*!40000 ALTER TABLE `seat` DISABLE KEYS */;
INSERT INTO `seat` VALUES (1,1,'A1','Regular'),(2,1,'A2','Regular'),(3,1,'A3','Regular'),(4,1,'B1','Premium'),(5,1,'B2','Premium'),(6,2,'A1','Regular'),(7,2,'A2','Regular'),(8,3,'A1','Regular'),(9,3,'A2','Premium'),(10,3,'A3','Regular'),(11,2,'A3','Regular'),(12,3,'A4','Regular'),(13,2,'A4','Regular'),(14,1,'A4','Regular'),(15,3,'A5','Regular'),(16,2,'A5','Regular'),(17,1,'A5','Regular'),(18,3,'A6','Regular'),(19,2,'A6','Regular'),(20,1,'A6','Regular'),(21,3,'A7','Regular'),(22,2,'A7','Regular'),(23,1,'A7','Regular'),(24,3,'A8','Regular'),(25,2,'A8','Regular'),(26,1,'A8','Regular'),(27,3,'A9','Regular'),(28,2,'A9','Regular'),(29,1,'A9','Regular'),(30,3,'A10','Regular'),(31,2,'A10','Regular'),(32,1,'A10','Regular'),(33,3,'B1','Regular'),(34,2,'B1','Regular'),(35,3,'B2','Regular'),(36,2,'B2','Regular'),(37,3,'B3','Regular'),(38,2,'B3','Regular'),(39,1,'B3','Regular'),(40,3,'B4','Regular'),(41,2,'B4','Regular'),(42,1,'B4','Regular'),(43,3,'B5','Regular'),(44,2,'B5','Regular'),(45,1,'B5','Regular'),(46,3,'B6','Regular'),(47,2,'B6','Regular'),(48,1,'B6','Regular'),(49,3,'B7','Regular'),(50,2,'B7','Regular'),(51,1,'B7','Regular'),(52,3,'B8','Regular'),(53,2,'B8','Regular'),(54,1,'B8','Regular'),(55,3,'B9','Regular'),(56,2,'B9','Regular'),(57,1,'B9','Regular'),(58,3,'B10','Regular'),(59,2,'B10','Regular'),(60,1,'B10','Regular'),(61,3,'C1','Regular'),(62,2,'C1','Regular'),(63,1,'C1','Regular'),(64,3,'C2','Regular'),(65,2,'C2','Regular'),(66,1,'C2','Regular'),(67,3,'C3','Regular'),(68,2,'C3','Regular'),(69,1,'C3','Regular'),(70,3,'C4','Regular'),(71,2,'C4','Regular'),(72,1,'C4','Regular'),(73,3,'C5','Regular'),(74,2,'C5','Regular'),(75,1,'C5','Regular'),(76,3,'C6','Regular'),(77,2,'C6','Regular'),(78,1,'C6','Regular'),(79,3,'C7','Regular'),(80,2,'C7','Regular'),(81,1,'C7','Regular'),(82,3,'C8','Regular'),(83,2,'C8','Regular'),(84,1,'C8','Regular'),(85,3,'C9','Regular'),(86,2,'C9','Regular'),(87,1,'C9','Regular'),(88,3,'C10','Regular'),(89,2,'C10','Regular'),(90,1,'C10','Regular');
/*!40000 ALTER TABLE `seat` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-25  8:49:06
