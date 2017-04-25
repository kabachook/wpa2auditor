-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 25 2017 г., 20:05
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
  `d_id` bigint(20) UNSIGNED NOT NULL,
  `dpath` varchar(256) NOT NULL,
  `dhash` binary(16) DEFAULT NULL,
  `dname` varchar(128) NOT NULL,
  `wcount` int(10) UNSIGNED NOT NULL,
  `hits` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  ADD PRIMARY KEY (`d_id`);

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
  MODIFY `d_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
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
