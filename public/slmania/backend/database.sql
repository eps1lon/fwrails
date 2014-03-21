--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `items_npcs`
--

CREATE TABLE `items_npcs` (
  `item_id` int(11) NOT NULL,
  `npc_id` int(11) NOT NULL,
  `count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `action` enum('kill','chase') NOT NULL DEFAULT 'kill',
  PRIMARY KEY (`item_id`,`npc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `items_places`
--

CREATE TABLE `items_places` (
  `item_id` int(11) NOT NULL,
  `pos_x` int(11) NOT NULL DEFAULT '-10',
  `pos_y` int(11) NOT NULL DEFAULT '-9',
  PRIMARY KEY (`item_id`,`pos_x`,`pos_y`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `npcs`
--

CREATE TABLE `npcs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `live` int(11) unsigned NOT NULL DEFAULT '1',
  `strength` int(11) unsigned NOT NULL DEFAULT '1',
  `unique_npc` smallint(5) unsigned NOT NULL DEFAULT '0',
  `aggressive` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pos_x` int(11) NOT NULL DEFAULT '-10',
  `pos_y` int(11) NOT NULL DEFAULT '-9',
  `killcount` bigint(20) NOT NULL DEFAULT '0',
  `chasecount` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `authenticity_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `authenticity_token` (`authenticity_token`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;