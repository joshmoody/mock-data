-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 06, 2013 at 09:57 PM
-- Server version: 5.5.25a
-- PHP Version: 5.3.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `mock_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `firstnames`
--

CREATE TABLE IF NOT EXISTS `firstnames` (
  `name` varchar(15) NOT NULL,
  `gender` varchar(1) NOT NULL,
  `rank` int(11) NOT NULL,
  KEY `gender` (`gender`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Common First Names By Gender';

-- --------------------------------------------------------

--
-- Table structure for table `lastnames`
--

CREATE TABLE IF NOT EXISTS `lastnames` (
  `name` varchar(15) NOT NULL,
  `rank` int(11) NOT NULL,
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Common Last Names';

-- --------------------------------------------------------

--
-- Table structure for table `streets`
--

CREATE TABLE IF NOT EXISTS `streets` (
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Common Street Names';

-- --------------------------------------------------------

--
-- Table structure for table `zipcodes`
--

CREATE TABLE IF NOT EXISTS `zipcodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zip` varchar(10) NOT NULL,
  `type` varchar(20) NOT NULL,
  `city` varchar(50) NOT NULL,
  `acceptable_cities` text NOT NULL,
  `unacceptable_cities` text NOT NULL,
  `state_code` varchar(2) NOT NULL,
  `state` varchar(50) NOT NULL,
  `county` varchar(50) NOT NULL,
  `timezone` varchar(50) NOT NULL,
  `area_codes` varchar(50) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `world_region` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `decomissioned` tinyint(4) NOT NULL,
  `estimated_population` bigint(20) NOT NULL,
  `notes` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state_code` (`state_code`),
  KEY `state` (`state`),
  KEY `zip` (`zip`),
  KEY `county` (`county`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
