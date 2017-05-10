-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 10 2017 г., 21:34
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
  `filename` varchar(60) NOT NULL,
  `size` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `dicts`
--

INSERT INTO `dicts` (`id`, `ts`, `dpath`, `dhash`, `dname`, `filename`, `size`) VALUES
(14, '2017-05-01 10:40:37', 'http://localhost/wpa2auditor/web/dicts/old_gold.txt.gz', 0xf0bd087bf8235b62159123e9b8ba3e16dd6b590db5ec39b643d1fbc7c8c9a683, 'old_good', '', 7489767),
(15, '2017-05-03 16:37:27', 'http://localhost/wpa2auditor/web/dicts/insidepro.txt.gz', 0xd15e890980652653226be071450d154946c4a4eaa857afabc73d61f8fa7a8bb2, 'inside', '', 36924775),
(16, '2017-05-03 16:38:29', 'http://localhost/wpa2auditor/web/dicts/used.txt.gz', 0x83b309d08585fe0666f6dabbf48cc9e189394e1f09d094575ec7e6b031871e9c, 'used', '', 41715893),
(17, '2017-05-03 16:39:29', 'http://localhost/wpa2auditor/web/dicts/os.txt.gz', 0x100ecc1ee13053dd3a6e4bb1cea44a8c3dd4635f2b00eb52e1cdf94a9cd406d7, 'werwerw', '', 94918295),
(19, '2017-05-10 20:10:06', 'http://localhost/wpa2auditor/web/dicts/openwall.txt.gz', 0xe37b9d2edd2b78dab3db6d6c471ac134a92d19c9fbcd43b2e0766f0ba309ffe6, 'gvvbvbvb', 'openwall.txt.gz', 5503980);

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL,
  `name` varchar(65) NOT NULL,
  `filename` varchar(60) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `server_path` varchar(120) DEFAULT NULL,
  `site_path` varchar(120) NOT NULL,
  `essid` varchar(60) DEFAULT NULL,
  `station_mac` varchar(12) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `ext` varchar(10) NOT NULL,
  `thash` binary(32) DEFAULT NULL,
  `uniq_hash` binary(16) NOT NULL,
  `net_key` varchar(64) DEFAULT '0',
  `username` varchar(60) DEFAULT NULL,
  `challenge` varchar(60) DEFAULT NULL,
  `response` varchar(60) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agents` int(11) NOT NULL DEFAULT '0',
  `status` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `filename`, `user_id`, `server_path`, `site_path`, `essid`, `station_mac`, `type`, `ext`, `thash`, `uniq_hash`, `net_key`, `username`, `challenge`, `response`, `priority`, `ts`, `agents`, `status`) VALUES
(297, 'sdfsdfsdf', 'handshake-01.cap.hccapx_1.hccapx', 2, 'C:/wamp64/www/wpa2auditor/web/tasks/handshake-01.cap.hccapx_1.hccapx', 'http://localhost/wpa2auditor/web/tasks/handshake-01.cap.hccapx_1.hccapx', 'MTSRouter-EAA450', 'a0ab1beaa450', 0, 'hccapx', 0x30098a4c172b28fbd241e094d8f79eb4ba2a67b08314ffb36c64467c237e732f, 0x6a8b1147b19b9aff1a06f2774e5b3122, '0', NULL, NULL, NULL, 0, '2017-05-10 20:08:47', 0, 0);

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
(1530, 297, 14, 0),
(1531, 297, 15, 0),
(1532, 297, 16, 0),
(1533, 297, 17, 0),
(1534, 297, 19, 0),
(1535, 297, 14, 0),
(1536, 297, 15, 0),
(1537, 297, 16, 0),
(1538, 297, 17, 0),
(1539, 297, 19, 0),
(1540, 297, 14, 0),
(1541, 297, 15, 0),
(1542, 297, 16, 0),
(1543, 297, 17, 0),
(1544, 297, 19, 0);

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
  `invite` binary(16) NOT NULL,
  `invited_c` int(11) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`u_id`, `rang`, `nick`, `email`, `userkey`, `invite`, `invited_c`, `ts`) VALUES
(2, 'admin', 'AtomnijPchelovek', 'admin@admin.ru', 0xb9d17e7dd4c28bf17ff87a8fea3213fb, 0xb9d17e7dd4c28bf17ff87a8fea3213fb, 1, '2017-04-13 20:00:16'),
(3, 'admin', 'nickgant', 'admin_2@admin.ru', 0x2d536641fb64ac6ba11865e55406af30, 0x00000000000000000000000000000000, 0, '2017-04-24 19:35:43'),
(4, 'user', 'sdffsgdfg', 'dfgdf@sdfsdf', 0x1612e74c4724e9b0d651cdbcfdfce86d, 0x00000000000000000000000000000000, 0, '2017-05-10 18:03:29'),
(5, 'user', 'sdfsdfsdf', 'sdfsdfs@wdwfs', 0xb6dc23d20827338bd6f57f8e072181ac, 0x00000000000000000000000000000000, 0, '2017-05-10 18:07:03'),
(6, 'user', 'dfgdfg', 'dfgdfgd@dsffdsgsd', 0xa7479b6d56f9cf4eef73e6e958047a80, 0x00000000000000000000000000000000, 0, '2017-05-10 18:10:04'),
(7, 'admin', 'ssssssssssssss', 'asdasdsd@sdsdsd', 0x4cbb2b8083833b31e4331f97a2184464, 0x94cdbdb84e8e1de80000000000000000, 0, '2017-05-10 18:51:28'),
(8, 'user', 'sfgfdfsd', 'sdfsdfs@sdfsfdsfd', 0x3c1e3a85d8ac4535b1628693246d084a, 0x74b72ae2ee96f9be0000000000000000, 0, '2017-05-10 18:52:54');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=298;
--
-- AUTO_INCREMENT для таблицы `tasks_dicts`
--
ALTER TABLE `tasks_dicts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1545;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
