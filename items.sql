USE cncrepair;

CREATE TABLE IF NOT EXISTS itemtypes (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	make VARCHAR(63) NOT NULL,
	modelnum VARCHAR(63) NOT NULL,
	description VARCHAR(1023) NOT NULL DEFAULT '',
	details TEXT,
	packing TEXT,
	type VARCHAR(63) NOT NULL DEFAULT '',
	exch TINYINT(1) DEFAULT 0,
	repair TINYINT(1) DEFAULT 0,
	sale TINYINT(1) DEFAULT 0,
	exchrate DECIMAL(10, 2),
	repairrate DECIMAL(10, 2),
	salerate DECIMAL(10, 2),
	value DECIMAL(10, 2),
	hts INT(10) UNSIGNED DEFAULT NULL,
	madein varchar(63) not null default '',
	onhand SMALLINT NOT NULL DEFAULT 0,
	onhold SMALLINT NOT NULL DEFAULT 0,
	inuse SMALLINT NOT NULL DEFAULT 0,
	weight DECIMAL(10, 2),
	dimensions VARCHAR(31),
	assembly TINYINT(1),
	cleaningprocs TEXT,
	repairprocs TEXT,
	testingprocs TEXT,
	qbtype ENUM('service', 'inventory', 'noninventory') NOT NULL DEFAULT 'service',
	qbrefnum VARCHAR(63),
	qbaccountref VARCHAR(63),
	qbeditsequence VARCHAR(63),
	created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	createdby smallint(5) unsigned DEFAULT NULL,
	editedby smallint(5) unsigned DEFAULT NULL,
	lastedited TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	foreign key (createdby) references users(id)
	on update cascade on delete set null,
	foreign key (editedby) references users(id)
	on update cascade on delete set null,
	UNIQUE (modelnum, make)
) ENGINE = INNODB;

--  items used to send information to quickbooks by default
INSERT INTO itemtypes (make, modelnum)
VALUES ('ZZ OFFICE USE', 'TAXAMNT');
INSERT INTO itemtypes (make, modelnum)
VALUES ('ZZ OFFICE USE', 'INFO');
INSERT INTO itemtypes (make, modelnum)
VALUES ('ZZ OFFICE USE', 'GENERAL PART');




CREATE TABLE IF NOT EXISTS make_map (
	make VARCHAR(63) PRIMARY KEY,
	qbparentref VARCHAR(63)
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS items (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	itemtype_id INT UNSIGNED NOT NULL,
	serial VARCHAR(63),
	barcode VARCHAR(31),
	atcustomer MEDIUMINT UNSIGNED DEFAULT 0,
	rack VARCHAR(15) DEFAULT '000',
	shelf VARCHAR(15) DEFAULT '000',
	lastseen TIMESTAMP,
	status ENUM('scrap', 'salvageable', 'needs work', 'ready today', 'ready NOW') DEFAULT 'needs work',
	onhold TINYINT(1) NOT NULL DEFAULT 0,
	owner MEDIUMINT UNSIGNED DEFAULT 0,
	readyfor ENUM('unpacking', 'receiving', 'cleaning', 'repair', 'testing', 'shipping'),
	stock TINYINT(1) DEFAULT 0,
	priority ENUM('red', 'blue', 'orange', 'brown') NOT NULL DEFAULT 'brown',
	UNIQUE (barcode),
	UNIQUE (serial, itemtype_id),
	FOREIGN KEY (atcustomer) REFERENCES customers(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (owner) REFERENCES customers(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (itemtype_id) REFERENCES itemtypes(id)
	ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS quotes (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	billto MEDIUMINT UNSIGNED,
	caller VARCHAR(63) NOT NULL DEFAULT '',
	phone_id INT UNSIGNED,
	fax_id INT UNSIGNED,
	email_id INT UNSIGNED,
	purchaseorder VARCHAR(31) NOT NULL DEFAULT '',
	total DECIMAL(10,2),
	taxrate DECIMAL(5,4) NOT NULL DEFAULT 0,
	taxtype VARCHAR(20) NOT NULL DEFAULT '',
	taxamnt decimal(10,2) not null,
	taxid VARCHAR(31) NOT NULL DEFAULT '',
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	createdby smallint(5) unsigned DEFAULT NULL,
	editedby smallint(5) unsigned DEFAULT NULL,
	lastedited timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	itemtotal SMALLINT,
	terms ENUM('credit', 'net30', 'wire', 'cod'),
	sent TIMESTAMP DEFAULT 0,
	notes TEXT,
	publicnotes TEXT,
	shipname VARCHAR(127) NOT NULL DEFAULT '',
	shipaddress VARCHAR(127) NOT NULL DEFAULT '',
	shipaddress1 VARCHAR(127) NOT NULL DEFAULT '',
	shipcity VARCHAR(63) NOT NULL DEFAULT '',
	shipstate VARCHAR(31) NOT NULL DEFAULT '',
	shipzip VARCHAR(15) NOT NULL DEFAULT '',
	shipcountry VARCHAR(63) NOT NULL DEFAULT '',
	FOREIGN KEY (billto) REFERENCES customers(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (phone_id) REFERENCES phones(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (email_id) REFERENCES emails(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (fax_id) REFERENCES phones(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	foreign key (createdby) references users(id)
	on update cascade on delete set null,
	foreign key (editedby) references users(id)
	on update cascade on delete set null

) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS invoices (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	billto MEDIUMINT UNSIGNED,
	caller VARCHAR(63) NOT NULL DEFAULT '',
	phone_id INT UNSIGNED,
	fax_id INT UNSIGNED,
	email_id INT UNSIGNED,
	purchaseorder VARCHAR(31) NOT NULL DEFAULT '',
	total DECIMAL(10,2),
	taxrate DECIMAL(5,4) NOT NULL DEFAULT 0,
	taxtype VARCHAR(20) NOT NULL DEFAULT '',
	taxamnt decimal(10,2) not null,
	taxid VARCHAR(31) NOT NULL DEFAULT '',
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	createdby smallint(5) unsigned DEFAULT NULL,
	editedby smallint(5) unsigned DEFAULT NULL,
	lastedited timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	itemtotal SMALLINT,
	terms ENUM('credit', 'net30', 'wire', 'cod'),
	sent TIMESTAMP DEFAULT 0,
	notes TEXT,
	publicnotes TEXT,
	shipname VARCHAR(127) NOT NULL DEFAULT '',
	shipaddress VARCHAR(127) NOT NULL DEFAULT '',
	shipaddress1 VARCHAR(127) NOT NULL DEFAULT '',
	shipcity VARCHAR(63) NOT NULL DEFAULT '',
	shipstate VARCHAR(31) NOT NULL DEFAULT '',
	shipzip VARCHAR(15) NOT NULL DEFAULT '',
	shipcountry VARCHAR(63) NOT NULL DEFAULT '',
	qbrefnum VARCHAR(63),
	qbeditsequence VARCHAR(63),
	FOREIGN KEY (billto) REFERENCES customers(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (phone_id) REFERENCES phones(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (email_id) REFERENCES emails(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (fax_id) REFERENCES phones(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	foreign key (createdby) references users(id)
	on update cascade on delete set null,
	foreign key (editedby) references users(id)
	on update cascade on delete set null
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS quoteitems (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quote_id INT UNSIGNED,
	itemtype_id INT UNSIGNED NOT NULL,
	type ENUM('exch', 'repair', 'sale'),
	quantity SMALLINT NOT NULL DEFAULT 1,
	rate DECIMAL(10, 2),
	print ENUM('regular', 'assembly', 'subitem'),
	description VARCHAR(1023) NOT NULL DEFAULT '',
	officenotes VARCHAR(1023) NOT NULL DEFAULT '',
	FOREIGN KEY (quote_id) REFERENCES quotes(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (itemtype_id) REFERENCES itemtypes(id)
	ON UPDATE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS invoiceitems (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	inv_id INT UNSIGNED,
	itemtype_id INT UNSIGNED NOT NULL,
	type ENUM('exch', 'repair', 'sale'),
	quantity SMALLINT NOT NULL DEFAULT 1,
	qtyreceived SMALLINT DEFAULT 0,
	rate DECIMAL(10, 2),
	shipped SMALLINT DEFAULT 0,
	print ENUM('regular', 'assembly', 'subitem'),
	description VARCHAR(1023) NOT NULL DEFAULT '',
	officenotes VARCHAR(1023) NOT NULL DEFAULT '',
	FOREIGN KEY (inv_id) REFERENCES invoices(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (itemtype_id) REFERENCES itemtypes(id)
	ON UPDATE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS histories (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	item_id INT UNSIGNED NOT NULL,
	invoiceitem_id BIGINT UNSIGNED,
	ship_invoiceitem_id BIGINT UNSIGNED,
	camefrom MEDIUMINT UNSIGNED,
	shippedto VARCHAR(127),
	unpacked TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	received TIMESTAMP DEFAULT 0,
	cleaned TIMESTAMP DEFAULT 0,
	repaired TIMESTAMP DEFAULT 0,
	tested TIMESTAMP DEFAULT 0,
	testedok TINYINT(1),
	shipped TIMESTAMP NOT NULL DEFAULT 0,
	carrier VARCHAR(31),
	trackingnum VARCHAR(63),
	FOREIGN KEY (item_id) REFERENCES items(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (invoiceitem_id) REFERENCES invoiceitems(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (ship_invoiceitem_id) REFERENCES invoiceitems(id)
	ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (camefrom) REFERENCES customers(id)
	ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS quoteitem_items (
	quoteitem_id BIGINT UNSIGNED NOT NULL,
	history_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (quoteitem_id) REFERENCES quoteitems(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (history_id) REFERENCES histories(id)
	ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS notes (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	type ENUM('ship', 'repair') DEFAULT 'repair',
	history_id INT UNSIGNED NOT NULL,
	user_id SMALLINT UNSIGNED NOT NULL,
	time_noted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	note VARCHAR(511),
	FOREIGN KEY (history_id) REFERENCES histories(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id)
	ON UPDATE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS assemblies (
	parent INT UNSIGNED NOT NULL,
	child INT UNSIGNED NOT NULL,
	quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
	PRIMARY KEY (parent, child),
	FOREIGN KEY (parent) REFERENCES itemtypes(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (child) REFERENCES itemtypes(id)
	ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS phantomitems (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	itemtype_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (itemtype_id) REFERENCES itemtypes(id)
	ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS oweditems (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	itemtype_id INT UNSIGNED NOT NULL,
	inv_id INT UNSIGNED NOT NULL,
	shipped TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	item_sent INT UNSIGNED,
	FOREIGN KEY (inv_id) REFERENCES invoices(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (itemtype_id) REFERENCES itemtypes(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	ADD FOREIGN KEY (item_sent) REFERENCES items(id)
	ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS specificassemblies (
	parent INT UNSIGNED NOT NULL,
	child INT UNSIGNED NOT NULL,
	PRIMARY KEY (parent, child),
	FOREIGN KEY (parent) REFERENCES phantomitems(id)
	ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (child) REFERENCES items(id)
	ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;