
--
-- Table structure for table `test_table`
--

DROP TABLE IF EXISTS `test_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `test_table` (
  `acol` int(11) DEFAULT NULL,
  `bcol` text DEFAULT NULL,
  `dcol` datetime DEFAULT NULL COMMENT '입력일'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='테스트 테이블';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_table`
--

LOCK TABLES `test_table` WRITE;
/*!40000 ALTER TABLE `test_table` DISABLE KEYS */;
INSERT INTO `test_table` VALUES (1,'테이브1','2023-03-12 13:36:12'),(2,'테이브2','2023-03-12 13:36:16'),(3,'테이브3','2023-03-12 13:36:20'),(5,'테이브5','2023-03-12 13:36:25'),(10,'테이브3','2023-03-12 17:12:02'),(10,'테이브3','2023-03-12 17:12:59'),(10,'테이브3','2023-03-12 17:13:32'),(10,'테이브3','2023-03-12 17:14:10'),(10,'테이브3','2023-03-12 17:46:11'),(10,'테이브3','2023-03-12 17:46:19');
/*!40000 ALTER TABLE `test_table` ENABLE KEYS */;
UNLOCK TABLES;
