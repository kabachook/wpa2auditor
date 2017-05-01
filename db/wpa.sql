-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 30 2017 г., 19:50
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

-- --------------------------------------------------------

--
-- Структура таблицы `dicts`
--

CREATE TABLE `dicts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dpath` varchar(256) NOT NULL,
  `dhash` binary(16) DEFAULT NULL,
  `dname` varchar(128) NOT NULL,
  `size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `dicts`
--

INSERT INTO `dicts` (`id`, `ts`, `dpath`, `dhash`, `dname`, `size`) VALUES
(6, '2017-04-27 13:15:13', 'http://localhost/wpa2auditor/web/dicts/cow.txt.gz', 0xa6d75d09082cb4e9080e3d2cb68dc43a, 'cow', 3073584),
(7, '2017-04-28 07:37:57', 'http://localhost/wpa2auditor/web/dicts/old_gold.txt.gz', 0xd4fdfe9e14af2c3884dfdc79ebbbff40, 'SOME NAME', 7489767),
(8, '2017-04-28 08:17:23', 'http://localhost/wpa2auditor/web/dicts/cracked.txt.gz', 0x95fb70f5b3d0f68828c6967fe8b01d26, 'OLOLOL', 122545),
(12, '2017-04-30 14:09:34', 'http://localhost/wpa2auditor/web/dicts/wp_fr.txt.gz', 0xa9d92bd24a7ca0aaf58e2460e4588eb9, 'sdfsdfsdfsfdsdfsdf', 5968469);

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL,
  `name` varchar(65) NOT NULL,
  `filename` varchar(60) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `net_key` varchar(64) DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agents` int(11) NOT NULL DEFAULT '0',
  `status` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `filename`, `type`, `net_key`, `priority`, `ts`, `agents`, `status`) VALUES
(1, 'kek', 'DIR-300NRU.hccap', 0, '0', 0, '2017-04-30 14:00:08', 5, 0),
(2, 'dtyt', 'ASUS8888.hccap', 0, '0', 0, '2017-04-30 14:00:50', 0, 1),
(3, 'gfhfgh', 'TP-LINK_3362EE.hccap', 0, '0', 0, '2017-04-30 14:39:17', 0, 2),
(4, 'sdfsdfsdf', 'TP-LINK_5CB1E0.hccap', 0, '0', 0, '2017-04-30 14:40:49', 0, 3),
(5, 'dgxhgdhgf', 'TP-LINK_878CBA.hccap', 0, '0', 0, '2017-04-30 14:41:50', 0, 0),
(6, 'shfghfgh', 'TP-LINK_C48632.hccap', 0, '0', 0, '2017-04-30 14:42:36', 0, 0),
(7, 'sdfsfsdf', 'TP-LINK_7D8358.hccap', 0, '0', 0, '2017-04-30 14:43:21', 0, 0),
(8, 'sdgfhgfh', 'MTSRouter-036012.hccap', 0, '0', 0, '2017-04-30 14:44:11', 0, 0),
(9, 'gdhfgh', 'MTSRouter-CF0405.hccap', 0, '0', 0, '2017-04-30 14:45:27', 0, 0),
(10, 'erwretert', 'MTSRouter-004121.hccap', 0, '0', 0, '2017-04-30 14:46:18', 0, 0),
(11, 'werwerwerewrwe', 'DIR320NRU.hccap', 0, '0', 0, '2017-04-30 14:46:42', 0, 0);

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
(1, 11, 6, 0),
(2, 11, 7, 0),
(3, 11, 8, 0),
(4, 11, 12, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `u_id` bigint(20) NOT NULL,
  `rang` text NOT NULL,
  `nick` text NOT NULL,
  `email` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `userkey` binary(16) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`u_id`, `rang`, `nick`, `email`, `userkey`, `ts`) VALUES
(2, 'admin', 'AtomnijPchelovek', 'admin@admin.ru', 0xb9d17e7dd4c28bf17ff87a8fea3213fb, '2017-04-13 20:00:16'),
(3, 'admin', 'nickgant', 'admin_2@admin.ru', 0x2d536641fb64ac6ba11865e55406af30, '2017-04-24 19:35:43');

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
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `IDX_users_userkey` (`userkey`),
  ADD UNIQUE KEY `IDX_users_mail` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `dicts`
--
ALTER TABLE `dicts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT для таблицы `tasks_dicts`
--
ALTER TABLE `tasks_dicts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
DELIMITER $$
--
-- События
--
CREATE DEFINER=`root`@`localhost` EVENT `e_stats` ON SCHEDULE EVERY '0 2' DAY_HOUR STARTS '2011-09-18 17:31:07' ON COMPLETION NOT PRESERVE ENABLE COMMENT 'Computes last day stats every 1h am' DO BEGIN
        UPDATE stats SET pvalue=(SELECT count(*) FROM n2d WHERE date(ts) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) WHERE pname='24getwork';
        UPDATE stats SET pvalue=(SELECT sum(wcount) FROM n2d, dicts WHERE date(ts) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND n2d.d_id=dicts.d_id) WHERE pname='24psk';
        UPDATE stats SET pvalue=(SELECT count(*) FROM nets WHERE date( ts ) = DATE_SUB( CURDATE() , INTERVAL 1 DAY)) WHERE pname='24sub';
        UPDATE stats SET pvalue=(SELECT sum(dicts.wcount) FROM nets, dicts WHERE nets.n_state=0) WHERE pname='words';
        UPDATE stats SET pvalue=(SELECT sum(dicts.wcount) FROM nets, n2d, dicts WHERE nets.n_state=0 AND nets.net_id = n2d.net_id AND dicts.d_id = n2d.d_id) WHERE pname='triedwords';
      END$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
