-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 01 2017 г., 10:44
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
  `dhash` binary(32) DEFAULT NULL,
  `dname` varchar(128) NOT NULL,
  `size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `dicts`
--

INSERT INTO `dicts` (`id`, `ts`, `dpath`, `dhash`, `dname`, `size`) VALUES
(14, '2017-05-01 10:40:37', 'http://localhost/wpa2auditor/web/dicts/old_gold.txt.gz', 0xf0bd087bf8235b62159123e9b8ba3e16dd6b590db5ec39b643d1fbc7c8c9a683, 'old_good', 7489767);

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
(11, 'werwerwerewrwe', 'DIR320NRU.hccap', 0, '0', 0, '2017-04-30 14:46:42', 0, 0),
(12, 'Task', 'DIR-300.hccap', 0, '0', 0, '2017-04-30 19:53:49', 0, 0);

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
(4, 11, 12, 0),
(5, 12, 6, 0),
(6, 12, 7, 0),
(7, 12, 8, 0),
(8, 12, 12, 0);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT для таблицы `tasks_dicts`
--
ALTER TABLE `tasks_dicts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
