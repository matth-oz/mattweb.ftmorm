CREATE TABLE IF NOT EXISTS `ot_games` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TEAM_ID` int(11) NOT NULL,
  `CITY` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `GOALS` tinyint(4) DEFAULT NULL,
  `GAME_DATE` datetime NOT NULL,
  `OWN` tinyint(4) DEFAULT NULL,
  `PLAYERS_ADDED` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `ot_lineups` (
  `START` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `GAME_ID` int(11) NOT NULL,
  `PLAYER_ID` int(11) NOT NULL,
  `TIME_IN` int(11) DEFAULT NULL,
  `GOALS` tinyint(4) DEFAULT NULL,
  `CARDS` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`GAME_ID`,`PLAYER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `ot_players` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FIRST_NAME` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `LAST_NAME` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `NICKNAME` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CITIZENSHIP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DOB` date NOT NULL,
  `ROLE` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `ot_teams` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `FOUND_YEAR` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
