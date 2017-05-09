-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 06 2017 г., 21:13
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
(14, '2017-05-01 10:40:37', 'http://localhost/wpa2auditor/web/dicts/old_gold.txt.gz', 0xf0bd087bf8235b62159123e9b8ba3e16dd6b590db5ec39b643d1fbc7c8c9a683, 'old_good', 7489767),
(15, '2017-05-03 16:37:27', 'http://localhost/wpa2auditor/web/dicts/insidepro.txt.gz', 0xd15e890980652653226be071450d154946c4a4eaa857afabc73d61f8fa7a8bb2, 'inside', 36924775),
(16, '2017-05-03 16:38:29', 'http://localhost/wpa2auditor/web/dicts/used.txt.gz', 0x83b309d08585fe0666f6dabbf48cc9e189394e1f09d094575ec7e6b031871e9c, 'used', 41715893),
(17, '2017-05-03 16:39:29', 'http://localhost/wpa2auditor/web/dicts/os.txt.gz', 0x100ecc1ee13053dd3a6e4bb1cea44a8c3dd4635f2b00eb52e1cdf94a9cd406d7, 'werwerw', 94918295),
(18, '2017-05-03 16:40:19', 'http://localhost/wpa2auditor/web/dicts/wp_ru.txt.gz', 0x58889d451b7bdaace99c78cfb87183fd3fe9d2ff80e50650aae8b26d8b8f23ff, 'sdfsdf', 9367430),
(19, '2017-05-03 16:41:36', 'http://localhost/wpa2auditor/web/dicts/wp.txt.gz', 0xcd6b1a87648d3abe8c2227acfd8717bdc3277586810eef80752ec2ed8c6c78e0, 'sdfsdf', 29437944);

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL,
  `name` varchar(65) NOT NULL,
  `filename` varchar(60) DEFAULT NULL,
  `server_path` varchar(120) DEFAULT NULL,
  `site_path` varchar(120) NOT NULL,
  `essid` varchar(60) DEFAULT NULL,
  `station_mac` varchar(12) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `thash` binary(32) DEFAULT NULL,
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

INSERT INTO `tasks` (`id`, `name`, `filename`, `server_path`, `site_path`, `essid`, `station_mac`, `type`, `thash`, `net_key`, `username`, `challenge`, `response`, `priority`, `ts`, `agents`, `status`) VALUES
(72, 'Test1', 'SPARK-HOME.hccap', NULL, '', 'SPARK-HOME', '7062b8c415b4', 0, 0x7f7a8812fbd11e2a3fc79925d2f22717d777fe9e33347b7fba6f97d0dc3e42e1, 'N5FAD3XE', NULL, NULL, NULL, 0, '2017-05-06 16:36:34', 0, 2),
(73, 'Test2', 'MTSRouter-9945F2.hccap', NULL, '', 'MTSRouter-9945F2', '1c5f2b9945f2', 0, 0x0229548114fe7b452237b8b58fb063365ecf39e88e53baf689d436a143bff093, '02470412', NULL, NULL, NULL, 0, '2017-05-06 16:36:46', 0, 2),
(75, '', '', 'C:wamp64wwwwpa2auditorweb	asksASUS.hccap', 'http://localhost/wpa2auditor/web/tasks/', 'ASUS', 'f46d048d3400', 0, 0x0000000000000000000000000000000000000000000000000000000000000000, '0', NULL, NULL, NULL, 0, '2017-05-06 20:18:38', 0, 0),
(76, '', '', 'C:wamp64wwwwpa2auditorweb	asksDIR-320NRU.hccap', 'http://localhost/wpa2auditor/web/tasks/', 'DIR-320NRU', '84c9b2840180', 0, 0x0000000000000000000000000000000000000000000000000000000000000000, '0', NULL, NULL, NULL, 0, '2017-05-06 20:19:10', 0, 0),
(77, '', 'TP-LINK_C48632.hccap', 'C:wamp64wwwwpa2auditorweb	asksTP-LINK_C48632.hccap', 'http://localhost/wpa2auditor/web/tasks/', 'TP-LINK_C48632', '6466b3c48632', 0, 0x0000000000000000000000000000000000000000000000000000000000000000, '0', NULL, NULL, NULL, 0, '2017-05-06 20:21:28', 0, 0),
(78, '', 'TP-LINK_878CBA.hccap', 'C:wamp64wwwwpa2auditorweb	asksTP-LINK_878CBA.hccap', 'http://localhost/wpa2auditor/web/tasks/', 'TP-LINK_878CBA', 'e8de27878cba', 0, 0x0000000000000000000000000000000000000000000000000000000000000000, '0', NULL, NULL, NULL, 0, '2017-05-06 20:22:21', 0, 0),
(79, '', 'TP-LINK_336E10.hccap', 'C:wamp64wwwwpa2auditorweb	asksTP-LINK_336E10.hccap', 'http://localhost/wpa2auditor/web/tasks/', 'TP-LINK_336E10', '6466b3336e10', 0, 0x0000000000000000000000000000000000000000000000000000000000000000, '0', NULL, NULL, NULL, 0, '2017-05-06 20:22:50', 0, 0),
(80, 'sdfsdfsdfsdf', 'TP-LINK_7FE7F2.hccap', 'C:wamp64wwwwpa2auditorweb	asksTP-LINK_7FE7F2.hccap', 'http://localhost/wpa2auditor/web/tasks/', 'TP-LINK_7FE7F2', 'c46e1f7fe7f2', 0, 0x0000000000000000000000000000000000000000000000000000000000000000, '0', NULL, NULL, NULL, 0, '2017-05-06 20:23:26', 0, 0),
(81, 'sdfsdfsdfdsfdsf', 'TP-LINK_7D8358.hccap', 'C:wamp64wwwwpa2auditorweb	asksTP-LINK_7D8358.hccap', 'http://localhost/wpa2auditor/web/tasks/sdfsdfsdfdsfdsf', 'TP-LINK_7D8358', 'f81a677d8358', 0, 0x9035f952d8ad3d53267e40ee4441733f0d7db966f350c406e8429cfc34664f6b, '0', NULL, NULL, NULL, 0, '2017-05-06 20:24:25', 0, 0),
(82, 'sdfsdfsdfsdf', 'TP-LINK_5CB1E0.hccap', 'C:wamp64wwwwpa2auditorweb	asksTP-LINK_5CB1E0.hccap', 'http://localhost/wpa2auditor/web/tasks/TP-LINK_5CB1E0.hccap', 'TP-LINK_5CB1E0', '10feed5cb1e0', 0, 0xb4ffcb4d2801587163b7b8f9bd006a74b68e12e25557342e975c51a1b243ce37, '0', NULL, NULL, NULL, 0, '2017-05-06 20:25:06', 0, 0),
(83, 'sdfsdfsdf', 'Tenda_004E70.hccap', 'C:/wamp64/www/wpa2auditor/web/tasks/Tenda_004E70.hccap', 'http://localhost/wpa2auditor/web/tasks/Tenda_004E70.hccap', 'Tenda_004E70', 'c83a35004e70', 0, 0xc10756f98ac9da03ed862ee33c85d0e3895d5f3588ccf8105bd40247c2883e4e, '0', NULL, NULL, NULL, 0, '2017-05-06 20:27:09', 0, 0),
(84, 'et', 'TTK-HOME.hccap', 'C:/wamp64/www/wpa2auditor/web/tasks/TTK-HOME.hccap', 'http://localhost/wpa2auditor/web/tasks/TTK-HOME.hccap', 'TTK-HOME', 'c4a81de8a15c', 0, 0x16a755efb1d5b87609c47edb40b588e55f03233b56504a89c957fa7de5112927, '5QE9AKYJ', NULL, NULL, NULL, 0, '2017-05-06 20:34:37', 0, 2);

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
(273, 61, 14, 0),
(274, 61, 15, 0),
(275, 61, 16, 0),
(276, 61, 17, 0),
(277, 61, 18, 0),
(278, 61, 19, 0),
(279, 62, 14, 0),
(280, 62, 15, 0),
(281, 62, 16, 0),
(282, 62, 17, 0),
(283, 62, 18, 0),
(284, 62, 19, 0),
(285, 63, 14, 0),
(286, 63, 15, 0),
(287, 63, 16, 0),
(288, 63, 17, 0),
(289, 63, 18, 0),
(290, 63, 19, 0),
(291, 64, 14, 0),
(292, 64, 15, 0),
(293, 64, 16, 0),
(294, 64, 17, 0),
(295, 64, 18, 0),
(296, 64, 19, 0),
(297, 65, 14, 0),
(298, 65, 15, 0),
(299, 65, 16, 0),
(300, 65, 17, 0),
(301, 65, 18, 0),
(302, 65, 19, 0),
(303, 66, 14, 0),
(304, 66, 15, 0),
(305, 66, 16, 0),
(306, 66, 17, 0),
(307, 66, 18, 0),
(308, 66, 19, 0),
(309, 67, 14, 0),
(310, 67, 15, 0),
(311, 67, 16, 0),
(312, 67, 17, 0),
(313, 67, 18, 0),
(314, 67, 19, 0),
(315, 68, 14, 0),
(316, 68, 15, 0),
(317, 68, 16, 0),
(318, 68, 17, 0),
(319, 68, 18, 0),
(320, 68, 19, 0),
(321, 69, 14, 0),
(322, 69, 15, 0),
(323, 69, 16, 0),
(324, 69, 17, 0),
(325, 69, 18, 0),
(326, 69, 19, 0),
(327, 70, 14, 0),
(328, 70, 15, 0),
(329, 70, 16, 0),
(330, 70, 17, 0),
(331, 70, 18, 0),
(332, 70, 19, 0),
(333, 71, 14, 0),
(334, 71, 15, 0),
(335, 71, 16, 0),
(336, 71, 17, 0),
(337, 71, 18, 0),
(338, 71, 19, 0),
(368, 76, 19, 0),
(367, 76, 18, 0),
(366, 76, 17, 0),
(365, 76, 16, 0),
(364, 76, 15, 0),
(363, 76, 14, 0),
(362, 75, 19, 0),
(361, 75, 18, 0),
(360, 75, 17, 0),
(359, 75, 16, 0),
(358, 75, 15, 0),
(357, 75, 14, 0),
(351, 74, 14, 0),
(352, 74, 15, 0),
(353, 74, 16, 0),
(354, 74, 17, 0),
(355, 74, 18, 0),
(356, 74, 19, 0),
(369, 77, 14, 0),
(370, 77, 15, 0),
(371, 77, 16, 0),
(372, 77, 17, 0),
(373, 77, 18, 0),
(374, 77, 19, 0),
(375, 78, 14, 0),
(376, 78, 15, 0),
(377, 78, 16, 0),
(378, 78, 17, 0),
(379, 78, 18, 0),
(380, 78, 19, 0),
(381, 79, 14, 0),
(382, 79, 15, 0),
(383, 79, 16, 0),
(384, 79, 17, 0),
(385, 79, 18, 0),
(386, 79, 19, 0),
(387, 80, 14, 0),
(388, 80, 15, 0),
(389, 80, 16, 0),
(390, 80, 17, 0),
(391, 80, 18, 0),
(392, 80, 19, 0),
(393, 81, 14, 0),
(394, 81, 15, 0),
(395, 81, 16, 0),
(396, 81, 17, 0),
(397, 81, 18, 0),
(398, 81, 19, 0),
(399, 82, 14, 0),
(400, 82, 15, 0),
(401, 82, 16, 0),
(402, 82, 17, 0),
(403, 82, 18, 0),
(404, 82, 19, 0),
(405, 83, 14, 0),
(406, 83, 15, 0),
(407, 83, 16, 0),
(408, 83, 17, 0),
(409, 83, 18, 0),
(410, 83, 19, 0),
(411, 84, 14, 0),
(412, 84, 15, 0),
(413, 84, 16, 0),
(414, 84, 17, 0),
(415, 84, 18, 0),
(416, 84, 19, 0);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;
--
-- AUTO_INCREMENT для таблицы `tasks_dicts`
--
ALTER TABLE `tasks_dicts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=417;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
