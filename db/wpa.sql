-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 27 2017 г., 19:55
-- Версия сервера: 5.7.14
-- Версия PHP: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `wpa`
--

DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_2_3_status` ()  BEGIN
	DELETE FROM tasks_dicts WHERE net_id IN(SELECT id FROM tasks WHERE status IN('3') AND forDelete='1' OR status IN('2'));
	UPDATE tasks SET forDelete='0' WHERE forDelete='1';
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `dicts`
--

CREATE TABLE `dicts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `server_path` varchar(256) NOT NULL,
  `site_path` varchar(256) NOT NULL,
  `hash` binary(32) DEFAULT NULL,
  `dict_name` varchar(128) NOT NULL,
  `file_name` varchar(60) NOT NULL,
  `size` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `dicts`
--

INSERT INTO `dicts` (`id`, `ts`, `server_path`, `site_path`, `hash`, `dict_name`, `file_name`, `size`) VALUES
(31, '2017-05-27 17:21:08', 'C:/wamp64/www/wpa2auditor-dev/web/dicts/5y2jqhgGwdSU9KDI.gz', 'http://localhost/wpa2auditor-dev/web/dicts/5y2jqhgGwdSU9KDI.gz', 0x82a330fca8b089c228225dcc7059ba43e618cba72cf10ee97ce56addaa61f6cd, 'cracked', '5y2jqhgGwdSU9KDI', 122545);

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL,
  `task_name` varchar(65) NOT NULL,
  `user_id` int(11) NOT NULL,
  `server_path` varchar(120) DEFAULT NULL,
  `site_path` varchar(120) NOT NULL,
  `essid` varchar(60) DEFAULT NULL,
  `station_mac` varchar(12) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `task_hash` binary(32) DEFAULT NULL,
  `uniq_hash` binary(16) NOT NULL,
  `net_key` varchar(64) DEFAULT '0',
  `username` varchar(60) DEFAULT NULL,
  `challenge` varchar(60) DEFAULT NULL,
  `response` varchar(60) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agents` int(11) NOT NULL DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `forDelete` int(5) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `tasks`
--

INSERT INTO `tasks` (`id`, `task_name`, `user_id`, `server_path`, `site_path`, `essid`, `station_mac`, `type`, `task_hash`, `uniq_hash`, `net_key`, `username`, `challenge`, `response`, `priority`, `ts`, `agents`, `status`, `forDelete`) VALUES
(383, 'my_net', 2, 'C:/wamp64/www/wpa2auditor-dev/web/tasks/sK7BJV6gbIeq0Wfz.hccapx', 'http://localhost/wpa2auditor-dev/web/tasks/sK7BJV6gbIeq0Wfz.hccapx', 'MTSRouter-EAA450', 'a0ab1beaa450', 0, 0x30098a4c172b28fbd241e094d8f79eb4ba2a67b08314ffb36c64467c237e732f, 0xdec655b5614fe024aeffeba275cdf46b, '0', NULL, NULL, NULL, 0, '2017-05-27 19:04:42', 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `tasks_dicts`
--

CREATE TABLE `tasks_dicts` (
  `id` bigint(20) NOT NULL,
  `net_id` bigint(20) NOT NULL,
  `dict_id` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `tasks_dicts`
--

INSERT INTO `tasks_dicts` (`id`, `net_id`, `dict_id`, `status`) VALUES
(1946, 383, 31, 0);

--
-- Триггеры `tasks_dicts`
--
DELIMITER $$
CREATE TRIGGER `change_status_to_failed` AFTER UPDATE ON `tasks_dicts` FOR EACH ROW BEGIN
   	DECLARE dicts_count INT;
	DECLARE stat INT;

	SELECT COUNT(*) INTO dicts_count FROM (SELECT * FROM tasks_dicts WHERE status NOT IN ('1') AND net_id=NEW.net_id) as alias;
	IF (dicts_count=0) THEN
		UPDATE tasks SET status='3' WHERE id=NEW.net_id;
		UPDATE tasks SET forDelete='1' WHERE id=NEW.net_id;
	END IF;
    SELECT COUNT(*) INTO dicts_count FROM (SELECT * FROM tasks_dicts WHERE status NOT IN ('1') AND net_id=NEW.net_id) as alias;
	SELECT status INTO stat FROM tasks WHERE id=NEW.net_id;
	IF dicts_count!=0 AND stat=3 THEN
		UPDATE tasks SET status='0' WHERE id=NEW.net_id;
	END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `reset_failed` AFTER INSERT ON `tasks_dicts` FOR EACH ROW BEGIN
DECLARE dicts_count INT;
DECLARE stat INT;
SELECT COUNT(*) INTO dicts_count FROM (SELECT * FROM tasks_dicts WHERE status NOT IN ('1') AND net_id=NEW.net_id) as alias;
	SELECT status INTO stat FROM tasks WHERE id=NEW.net_id;
	IF dicts_count!=0 AND stat=3 THEN
		UPDATE tasks SET status='0' WHERE id=NEW.net_id;
	END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `rang` text NOT NULL,
  `nick` text NOT NULL,
  `email` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `userkey` binary(16) NOT NULL,
  `invite` binary(16) NOT NULL,
  `invited_c` int(11) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `rang`, `nick`, `email`, `userkey`, `invite`, `invited_c`, `ts`) VALUES
(2, 'admin', 'AtomnijPchelovek', 'admin@admin.ru', 0xb9d17e7dd4c28bf17ff87a8fea3213fb, 0xb9d17e7dd4c28bf17ff87a8fea3213fb, 1, '2017-04-13 20:00:16'),
(3, 'admin', 'nickgant', 'admin_2@admin.ru', 0x2d536641fb64ac6ba11865e55406af30, 0x00000000000000000000000000000000, 0, '2017-04-24 19:35:43'),
(4, 'user', 'sdffsgdfg', 'dfgdf@sdfsdf', 0x1612e74c4724e9b0d651cdbcfdfce86d, 0x00000000000000000000000000000000, 0, '2017-05-10 18:03:29'),
(5, 'user', 'sdfsdfsdf', 'sdfsdfs@wdwfs', 0xb6dc23d20827338bd6f57f8e072181ac, 0x00000000000000000000000000000000, 0, '2017-05-10 18:07:03'),
(6, 'user', 'dfgdfg', 'dfgdfgd@dsffdsgsd', 0xa7479b6d56f9cf4eef73e6e958047a80, 0x00000000000000000000000000000000, 0, '2017-05-10 18:10:04'),
(7, 'admin', 'ssssssssssssss', 'asdasdsd@sdsdsd', 0x4cbb2b8083833b31e4331f97a2184464, 0x94cdbdb84e8e1de80000000000000000, 0, '2017-05-10 18:51:28'),
(8, 'user', 'sfgfdfsd', 'sdfsdfs@sdfsfdsfd', 0x3c1e3a85d8ac4535b1628693246d084a, 0x74b72ae2ee96f9be0000000000000000, 0, '2017-05-10 18:52:54'),
(9, 'user', 'cbvcvn vbn', 'cdth@fghfgh', 0xd5c12f8136701fd416532f2ac7d55309, 0xcfa3a0bc94975cb9c346a585ccb3ad9e, 0, '2017-05-13 20:59:49'),
(10, 'user', 'sdfsdf', 'sdfsdfsdf@sdfsdf', 0xa2d27186c0c79d8a971b23819ae0ebbe, 0x7a1e01c1f482effc90f8e7d0e2581aff, 0, '2017-05-21 10:41:00');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `dicts`
--
ALTER TABLE `dicts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tasks_dicts`
--
ALTER TABLE `tasks_dicts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `IDX_users_userkey` (`userkey`),
  ADD UNIQUE KEY `IDX_users_mail` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `dicts`
--
ALTER TABLE `dicts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=384;
--
-- AUTO_INCREMENT для таблицы `tasks_dicts`
--
ALTER TABLE `tasks_dicts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1947;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
