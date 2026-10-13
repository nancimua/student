-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-06-19 21:25:00
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `2026student`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `salt` char(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `salt`) VALUES
(2, 'admin', '$2y$10$Kq90GatOcU1e5dONHG5F7.BDM3ScWJx4D7xnGy.DqdX7UF.WaIeqC', '');

-- --------------------------------------------------------

--
-- 表的结构 `score`
--

CREATE TABLE `score` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `subject_id` varchar(50) NOT NULL,
  `score` float DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `score`
--

INSERT INTO `score` (`id`, `student_id`, `subject_id`, `score`) VALUES
(5, '24103120101', '4', 100),
(6, '24103120101', '5', 100),
(61, '24103120102', '2', 20),
(60, '24103120102', '1', 10),
(18, '24103120151', '1', 100),
(19, '24103120151', '2', 100),
(20, '24103120151', '3', 100),
(62, '24103120102', '3', 30),
(54, '24103120102', '1', 10),
(53, '24103120026', '3', 100),
(52, '24103120026', '2', 99),
(51, '24103120026', '1', 88),
(56, '24103120102', '3', 30),
(55, '24103120102', '2', 20),
(48, '24103120101', '1', 40),
(49, '24103120101', '2', 50),
(50, '24103120101', '3', 60);

-- --------------------------------------------------------

--
-- 表的结构 `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '学号',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '姓名',
  `gender` enum('男','女') COLLATE utf8_unicode_ci DEFAULT '男' COMMENT '性别',
  `class` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '班级',
  `chinese` decimal(5,1) DEFAULT '0.0' COMMENT '语文',
  `math` decimal(5,1) DEFAULT '0.0' COMMENT '数学',
  `english` decimal(5,1) DEFAULT '0.0' COMMENT '英语',
  `total` decimal(6,1) DEFAULT '0.0' COMMENT '总分',
  `average` decimal(5,1) DEFAULT '0.0' COMMENT '平均分',
  `level` varchar(20) COLLATE utf8_unicode_ci DEFAULT '待提高' COMMENT '等级',
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '登录密码（哈希）'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `student`
--

INSERT INTO `student` (`id`, `student_id`, `name`, `gender`, `class`, `chinese`, `math`, `english`, `total`, `average`, `level`, `avatar`, `password`) VALUES
(5, '24103120101', '黄华冠', '男', '324计算机应用技术1班', '40.0', '50.0', '60.0', '150.0', '50.0', '待提高', NULL, '$2y$10$x0tiu21wuhQYHTK9U91n9uefz.fmMlGxeOYrpUOjQNk6IIyi6wHBC'),
(4, '24103120151', '曾浩铭', '男', '324计算机应用技术1班', '100.0', '100.0', '100.0', '300.0', '100.0', '优秀', NULL, '$2y$10$p2KhIWHP90pAt/UZrKxaYeWIBaO7EYlwuJb2p9vBjRIBOb9XicfHe'),
(6, '24103120102', '邹乐涛', '男', '324计算机应用技术1班', '10.0', '20.0', '30.0', '60.0', '20.0', '待提高', NULL, '$2y$10$CrPQDzX1ggGf8NB5hfj7G.lBWZOVhR63QF8M1minRZJatxKEtK2CS'),
(16, '24103120026', '小许', '女', '324计算机应用技术1班', '88.0', '99.0', '100.0', '287.0', '95.7', '优秀', 'uploads/avatars/avatar_24103120026_1781782976.jpg', '$2y$10$gmSCg2f/RDuI7H6D4xYeq.TUy6ahUpFYEMDgb8JGrwCCDinRgVE8e');

-- --------------------------------------------------------

--
-- 表的结构 `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`) VALUES
(1, '语文'),
(2, '数学'),
(3, '英语');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$0n.E.d6IGx/QzVlEHb08Du5PASQ7Lb70UDE8Fa9wv74Y/uViHmocm', '2026-06-07 06:52:28'),
(3, 'test', '$2y$10$sueYNvomO44SHV0rrCEd9eiuxMfycKalp0uliA5NzO.3TeFhMUZfC', '2026-06-07 07:10:22');

--
-- 转储表的索引
--

--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 表的索引 `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- 表的索引 `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- 表的索引 `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `score`
--
ALTER TABLE `score`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- 使用表AUTO_INCREMENT `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
