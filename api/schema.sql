/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action` (
  `id` int(11) NOT NULL auto_increment,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `version` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `attrs` text collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docs` (
  `doc_id` varchar(40) collate latin1_general_ci NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_id` bigint(20) NOT NULL,
  `last_write` int(10) unsigned NOT NULL,
  `name` varchar(40) collate latin1_general_ci NOT NULL,
  `content` text collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`doc_id`),
  KEY `user_id` (`user_id`),
  KEY `last_write` (`last_write`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` bigint(20) NOT NULL auto_increment,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `identity` varchar(1000) collate latin1_general_ci NOT NULL,
  `email` varchar(200) collate latin1_general_ci NOT NULL,
  `last_login` timestamp NOT NULL default '0000-00-00 00:00:00',
  `last_write` int(10) unsigned NOT NULL,
  `attrs` varchar(10000) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `identity` (`identity`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_instance` (
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_id` bigint(20) NOT NULL,
  `instance` varchar(40) collate latin1_general_ci NOT NULL,
  UNIQUE KEY `instance` (`instance`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
