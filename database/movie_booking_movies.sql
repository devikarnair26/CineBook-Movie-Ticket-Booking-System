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
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies` (
  `movie_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `description` text,
  `poster_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`movie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

LOCK TABLES `movies` WRITE;
/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
INSERT INTO `movies` VALUES (1,'Leo','Action','Tamil',164,'2023-10-19','An action thriller','images/posters/leo.jpg'),(2,'Manjummel Boys','Adventure','Malayalam',135,'2024-02-22','A group of friends face a dangerous situation','images/posters/manjummel-boys.jpg'),(3,'Premalu','Romance','Malayalam',156,'2024-02-09','A romantic comedy','images/posters/premalu.jpg'),(4,'Bethlehem Kudumba Unit','Drama','Malayalam',150,'2026-01-01','Bethlehem Kudumba Unit (BKU)','images/posters/bethlehem-kudumba-unit.jpg'),(5,'Aavesham','Action Comedy','Malayalam',158,'2024-04-11','A comedy about college students and a local gangster','images/posters/aavesham.jpg'),(6,'2018','Drama','Malayalam',150,'2023-05-05','A survival drama inspired by the Kerala floods','images/posters/2018.jpg'),(7,'Aadujeevitham','Drama','Malayalam',172,'2024-03-28','A survival drama','images/posters/aadujeevitham.jpg'),(8,'Bangalore Days','Drama','Malayalam',172,'2014-05-30','Three cousins experience friendship, love and life in Bangalore.','images/posters/bangalore-days.jpg'),(9,'Drishyam','Thriller','Malayalam',160,'2013-12-19','A family man tries to protect his family from a criminal investigation.','images/posters/drishyam.jpg'),(10,'Premam','Romance','Malayalam',156,'2015-05-29','A young man experiences love at different stages of his life.','images/posters/premam.jpg');
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;
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
