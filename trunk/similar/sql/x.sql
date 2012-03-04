CREATE TABLE `article_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL DEFAULT '0',
  `word` char(10) NOT NULL,
  `times` int(10) unsigned NOT NULL DEFAULT '0',
  `in_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aw` (`article_id`,`word`),
  KEY `word` (`word`)
) ENGINE=MyISAM AUTO_INCREMENT=921549 DEFAULT CHARSET=utf8