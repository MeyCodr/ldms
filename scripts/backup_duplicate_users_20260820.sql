-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: phnportalenterto_trms
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `user`
--
-- WHERE:  id IN (5204,4649,4450,3357,3629)

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `password`, `staffno`, `staffname`, `email`, `gender`, `designation`, `department`, `department_id`, `section`, `section_id`, `division`, `division_id`, `plant`, `roletype`, `usertype`, `grade`, `hodid`, `status`, `dateresign`, `date_join`) VALUES (3357,'9e9bbdcf1c723753f617a0f2be7c9bfb','WB119','MOHAN A/L ASOKAH',NULL,'MALE','CONTRACT','MANUFACTURING & SCM BB',15,'MFG - BUKIT BERUNTUNG',50,'OPERATION MANAGEMENT',5,NULL,'','',0,0,'RESIGN',NULL,NULL),(3629,'9e9bbdcf1c723753f617a0f2be7c9bfb','YS081','MUHAMMAD AMIRUL ABDILLAH ',NULL,'MALE','CONTRACT','MANUFACTURING & SCM PEGOH',16,'ASSEMBLY - PEGOH',53,'OPERATION MANAGEMENT',5,NULL,'','',0,143,'',NULL,NULL),(4450,'9e9bbdcf1c723753f617a0f2be7c9bfb','WA170','MOHD AZHAARI BIN HJ KUNTOM',NULL,'MALE','CONTRACT','MANUFACTURING & SCM SA1',18,'SUPPLY CHAIN MANAGEMENT SA1',60,'OPERATION MANAGEMENT',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4649,'9e9bbdcf1c723753f617a0f2be7c9bfb','SPMT010','MOHAMAD AMIRUL HAKIM BIN ISMAIL',NULL,'MALE','CONTRACT','',NULL,'',NULL,'OPERATION MANAGEMENT',5,NULL,NULL,NULL,NULL,NULL,'RESIGN',NULL,NULL),(5204,'9e9bbdcf1c723753f617a0f2be7c9bfb','KA275','NORIKHWAN BIN NORSAMSOR',NULL,'MALE','CONTRACT','MANUFACTURING & SCM PEGOH',16,'ASSEMBLY - PEGOH',53,'OPERATION MANAGEMENT',5,NULL,NULL,NULL,NULL,143,'',NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER `sync_hod_to_pme` AFTER UPDATE ON `user` FOR EACH ROW BEGIN
    IF NEW.hodid <> OLD.hodid THEN
        UPDATE pme
        SET hodid = NEW.hodid
        WHERE userid = NEW.id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-20 15:58:27
