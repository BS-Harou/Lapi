# Mobilní Lapiduch

Ke zprovoznění na vlastním serveru je třeba nastavit několik věcí:

* Nastavit přihlašovací údaje k databázi v souboru db_login.php
* Pokud nejsou data přímo v rootu domény/subdomény, ale v nějaké složce tak upravit "RewriteBase /" v souboru .htaccess na "RewriteBase /subfolder"
* Vytvořit potřebné MySQL tabulky

```sql
CREATE TABLE IF NOT EXISTS `uschovna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(100) COLLATE utf8_bin NOT NULL,
  `title` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `text` text COLLATE utf8_bin,
  `time` varchar(30) COLLATE utf8_bin NOT NULL,
  `club` varchar(100) COLLATE utf8_bin NOT NULL,
  `avatar_url` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `owner` varchar(100) COLLATE utf8_bin NOT NULL,
  `post_id` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=61 ;



CREATE TABLE IF NOT EXISTS `user_settings` (
  `nick` varchar(100) COLLATE utf8_bin NOT NULL,
  `start_page` varchar(30) COLLATE utf8_bin NOT NULL,
  `right_corner` tinyint(1) NOT NULL,
  `show_spoilers` tinyint(1) NOT NULL,
  `hide_avatars` tinyint(1) NOT NULL,
  `old_style` tinyint(1) NOT NULL,
  `hide_old_images` tinyint(1) NOT NULL,
  `new_post_color` varchar(100) COLLATE utf8_bin NOT NULL,
  `linkify` tinyint(1) NOT NULL,
  PRIMARY KEY (`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

Ke správnému fungování je třeba PHP 5.3 a vyšší!