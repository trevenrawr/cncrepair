-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 14, 2011 at 03:36 AM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cncrepair`
--

-- --------------------------------------------------------

--
-- Table structure for table `salestax`
--

DROP TABLE IF EXISTS `salestax`;
CREATE TABLE IF NOT EXISTS `salestax` (
  `province` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `tax` decimal(5,4) unsigned NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `salestax`
--

INSERT INTO `salestax` (`province`, `tax`, `name`) VALUES
('BC', 0.1200, 'HST'),
('MB', 0.0700, 'PST'),
('NB', 0.1300, 'HST'),
('NL', 0.1300, 'HST'),
('NS', 0.1500, 'HST'),
('ON', 0.1300, 'HST'),
('PE', 0.1050, 'PST'),
('QC', 0.0892, 'PST'),
('SK', 0.0500, 'PST'),
('Canada', 0.0500, 'GST');
