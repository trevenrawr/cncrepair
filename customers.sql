CREATE DATABASE cncrepair CHARACTER SET utf8 COLLATE utf8_unicode_ci;

USE cncrepair;

CREATE TABLE IF NOT EXISTS users (
	id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user VARCHAR(63) UNIQUE,
	password CHAR(127),
	position VARCHAR(31) DEFAULT '',
	name VARCHAR(31),
	locked TINYINT(1) DEFAULT 0
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS ci_sessions (
	session_id VARCHAR(40) DEFAULT '0' NOT NULL,
	ip_address VARCHAR(16) DEFAULT '0' NOT NULL,
	user_agent VARCHAR(50) NOT NULL,
	last_activity INT(10) UNSIGNED DEFAULT 0 NOT NULL,
	user_data TEXT NOT NULL,
	PRIMARY KEY (session_id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS messages (
	view VARCHAR(31) PRIMARY KEY,
	name VARCHAR(63),
	message TEXT
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS customers (
	id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(127) NOT NULL UNIQUE,
	address VARCHAR(127) NOT NULL DEFAULT '',
	address1 VARCHAR(127) NOT NULL DEFAULT '',
	city VARCHAR(63) NOT NULL DEFAULT '',
	state VARCHAR(31) NOT NULL DEFAULT '',
	zip VARCHAR(15) NOT NULL DEFAULT '',
	country VARCHAR(63) NOT NULL DEFAULT '',
	terms ENUM('credit', 'net30', 'wire', 'cod'),
	currency ENUM('United States of America Dollar', 'Canada Dollar'),
	tax VARCHAR(30) NOT NULL DEFAULT '',
	taxid VARCHAR(31) NOT NULL DEFAULT '',
	creditlimit DECIMAL(10, 2),
	created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	createdby smallint(5) unsigned DEFAULT NULL,
	editedby smallint(5) unsigned DEFAULT NULL,
	lastedited TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	qbrefnum VARCHAR(63),
	qbeditsequence VARCHAR(31) DEFAULT '',
	qbqueued TINYINT(1),
	qblastupdate TIMESTAMP DEFAULT 0,
	qblastactivity TIMESTAMP DEFAULT 0,
	balance DECIMAL(10, 2),
	shipname VARCHAR(63) NOT NULL DEFAULT '',
	shipaddress VARCHAR(127) NOT NULL DEFAULT '',
	shipaddress1 VARCHAR(127) NOT NULL DEFAULT '',
	shipcity VARCHAR(63) NOT NULL DEFAULT '',
	shipstate VARCHAR(31) NOT NULL DEFAULT '',
	shipzip VARCHAR(15) NOT NULL DEFAULT '',
	shipcountry VARCHAR(63) NOT NULL DEFAULT '',
	carrier VARCHAR(31) NOT NULL DEFAULT '',
	notes TEXT
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS salestax (
	province VARCHAR(10) NOT NULL,
	tax DECIMAL(5, 4) NOT NULL,
	name VARCHAR(20) NOT NULL
) ENGINE=INNODB;

INSERT INTO salestax (province, tax, name) VALUES
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

INSERT INTO customers (id, name, address, address1, city, state, zip, country, shipname, shipaddress, shipaddress1, shipcity, shipstate, shipzip, shipcountry, currency)
VALUES (0, 'CNC Repair', '1770 Front St. #142', '', 'Lynden', 'WA', '98264', 'United States', 'CNC Repair', '1770 Front St. #142', '', 'Lynden', 'WA', '98624', 'United States', 'United States of America Dollar');
UPDATE customers SET id=0 WHERE name='CNC Repair';

CREATE TABLE IF NOT EXISTS phones (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	cust_id MEDIUMINT UNSIGNED NOT NULL,
	type ENUM('primary', 'fax', 'cell', 'technical', 'office', 'other'),
	num VARCHAR(63),
	contact VARCHAR(31) NOT NULL DEFAULT '',
	FOREIGN KEY (cust_id) REFERENCES customers(id)
	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS emails (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	cust_id MEDIUMINT UNSIGNED NOT NULL,
	name VARCHAR(63) NOT NULL DEFAULT '',
	email VARCHAR(127),
	FOREIGN KEY (cust_id) REFERENCES customers(id)
	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB;