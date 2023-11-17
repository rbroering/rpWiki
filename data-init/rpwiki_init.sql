-- MySQL dump 10.19  Distrib 10.3.38-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ruvenproductions
-- ------------------------------------------------------
-- Server version	10.3.38-MariaDB-0ubuntu0.20.04.1

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

CREATE DATABASE IF NOT EXISTS rpwiki;

USE rpwiki;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `page` text NOT NULL,
  `pagetype` text NOT NULL,
  `title` text NOT NULL,
  `content` longtext NOT NULL,
  `tags` text NOT NULL,
  `timestamp` text NOT NULL,
  `timezone` text NOT NULL,
  `writer` text NOT NULL,
  `edited` text NOT NULL,
  `type` text NOT NULL,
  `hidden` text NOT NULL,
  `toId` text NOT NULL,
  `toRid` text NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `user` text DEFAULT NULL,
  `username` text DEFAULT NULL,
  `page` text DEFAULT NULL,
  `page2` text DEFAULT NULL,
  `pageURL` text DEFAULT NULL,
  `old` text DEFAULT NULL,
  `new` text DEFAULT NULL,
  `type` text NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `autonote` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `notice` text DEFAULT NULL,
  `timestamp` text NOT NULL,
  `timezone` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `access` text NOT NULL,
  `url` text NOT NULL,
  `type` text NOT NULL,
  `name` text NOT NULL,
  `file` longblob NOT NULL,
  `data` text NOT NULL,
  `timestamp` text NOT NULL,
  `timezone` text NOT NULL,
  `user` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `content` longtext NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `url` text NOT NULL,
  `pagetitle` text DEFAULT NULL,
  `disptitle` text DEFAULT NULL,
  `type` text DEFAULT NULL,
  `protect` text DEFAULT NULL,
  `allowcomments` text DEFAULT NULL,
  `creator` text DEFAULT NULL,
  `hidden` text DEFAULT NULL,
  `properties` text DEFAULT NULL,
  `data1` text DEFAULT NULL,
  `data2` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission` mediumtext NOT NULL,
  `groups` mediumtext NOT NULL,
  `users` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'reg','*',''),(2,'editpage','-',''),(3,'editusergroups','staff,helper,admin',''),(4,'blockuser','staff,helper,admin',''),(5,'hidecomments','staff,helper,admin',''),(6,'editcomments','staff,helper,admin',''),(7,'writecomments','staff,support,helper,admin',''),(8,'writereplies','staff,support,helper,admin',''),(9,'deletecomments','staff,helper',''),(10,'editreplies','staff,helper,admin,own',''),(11,'hidereplies','staff,helper,admin',''),(12,'deletereplies','staff,helper',''),(13,'hideuser','staff,helper,admin',''),(14,'p-edit','users',''),(15,'edit-force','staff,helper',''),(16,'edit-ns-sys','staff',''),(17,'edit-ns-user','staff,helper,admin,own',''),(18,'edit-ns-help','support,staff',''),(19,'edit-ns-blog','staff,helper,admin,own',''),(20,'p-rename','staff,helper,admin',''),(21,'p-protect','staff,helper,admin',''),(22,'delete-user','staff',''),(23,'p-hide','staff,helper,admin',''),(24,'globalnotif','staff,helper',''),(25,'set-usericon','staff,helper,own',''),(26,'control-edit-groups','staff',''),(27,'control-view','users',''),(28,'control-edit','staff',''),(29,'edit-strange-css','staff,helper',''),(30,'edit-strange-js','staff,helper',''),(31,'test','-',''),(32,'edit-ns-system','staff',''),(33,'create-ns-blog','users',''),(34,'create-ns-sys','staff',''),(35,'create-ns-system','staff',''),(36,'create-ns-user','staff',''),(37,'create-ns-help','staff,helper,admin',''),(38,'view-hidden-user','staff,helper,admin',''),(39,'view-versions','*',''),(40,'p-protect-advanced','staff,helper,admin',''),(41,'log-view','*',''),(42,'log-view-hidden','staff,helper,admin',''),(43,'create-ns-guide','staff,helper',''),(44,'edit-ns-guide','staff,helper',''),(45,'create-ns-template','staff,helper,admin',''),(46,'edit-ns-template','staff,helper,admin',''),(47,'log-hide-entry','staff,helper',''),(48,'e-suppresslog','staff,helper,test_group',''),(49,'e-nolog','-',''),(50,'beta-access','test_group',''),(51,'feedback-priority','helper,admin,test_group,verified',''),(52,'comments-view-hidden','staff,helper,admin',''),(53,'reg-while-loggedin','staff',''),(54,'suppress-protection','staff',''),(55,'search-show-hidden','staff,helper',''),(56,'requests','staff',''),(57,'create-ns-msg','staff',''),(58,'edit-ns-msg','staff','');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pref`
--

DROP TABLE IF EXISTS `pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `username` text DEFAULT NULL,
  `lang` text DEFAULT NULL,
  `skin` text DEFAULT NULL,
  `bgfx_heavy` tinyint(1) DEFAULT NULL,
  `color_theme` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `username` text NOT NULL,
  `timestamp` text NOT NULL,
  `timezone` text NOT NULL,
  `type` text NOT NULL,
  `content` text NOT NULL,
  `filelocation` text NOT NULL,
  `file` longblob NOT NULL,
  `note` text NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rights`
--

DROP TABLE IF EXISTS `rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rights` (
  `username` text NOT NULL,
  `staff` text DEFAULT NULL,
  `helper` text DEFAULT NULL,
  `sysop` text DEFAULT NULL,
  `rightsuser` text DEFAULT NULL,
  `deleteuser` text DEFAULT NULL,
  `dbdelete` text DEFAULT NULL,
  `hideuser` text DEFAULT NULL,
  `trusted` text DEFAULT NULL,
  `blocked` text DEFAULT NULL,
  `thidden` text DEFAULT NULL,
  `ttesting` text DEFAULT NULL,
  `tnomsg` text DEFAULT NULL,
  `rights` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `email` text DEFAULT NULL,
  `rights` text DEFAULT NULL,
  `types` text DEFAULT NULL,
  `usericon` text DEFAULT NULL,
  `css` text DEFAULT NULL,
  `js` text DEFAULT NULL,
  `birthdate` text DEFAULT NULL,
  `country` text DEFAULT NULL,
  `msgcount` text DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `passwordU` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-11-17  2:42:00
