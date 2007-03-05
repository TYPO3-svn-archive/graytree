#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_graytree_foldername varchar(30) DEFAULT '' NOT NULL,
	KEY tx_gray_folder (tx_graytree_foldername),
);