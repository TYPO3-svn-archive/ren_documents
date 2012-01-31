#
# Table structure for table 'tx_rendocuments_docs'
#
CREATE TABLE tx_rendocuments_docs (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '1' NOT NULL,
	fe_group text,
	title varchar(255) DEFAULT '' NOT NULL,
	file text,
	description text,
	published int(11) DEFAULT '0' NOT NULL,
	author varchar(255) DEFAULT '' NOT NULL,
	service int(11) DEFAULT '0' NOT NULL,
	themes text,
	keywords varchar(255) DEFAULT '' NOT NULL,
	changedby int(11) DEFAULT '0' NOT NULL,
	workspace int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_rendocuments_workspaces'
#
CREATE TABLE tx_rendocuments_workspaces (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	groups text,
	access text,
	parent int(11) DEFAULT '0' NOT NULL,
	shy tinyint(4) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_rendocuments_subscriptions'
#
CREATE TABLE tx_rendocuments_subscriptions (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    user int(11) DEFAULT '0' NOT NULL,
    workspace int(11) DEFAULT '0' NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);