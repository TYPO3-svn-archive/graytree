<?php

########################################################################
# Extension Manager/Repository config file for ext: "graytree"
#
# Auto generated 02-07-2007 20:29
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Graytree Library',
	'description' => 'Library and module for managing records trees like categories and so on.',
	'category' => 'misc',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod_clickmenu,mod_cmd',
	'state' => 'alpha',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Franz Holzinger',
	'author_email' => 'contact@fholzinger.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.1.1',
	'_md5_values_when_last_written' => 'a:33:{s:9:"ChangeLog";s:4:"ce99";s:25:"class.tx_graytree_cm1.php";s:4:"fb31";s:31:"class.ux_alt_menu_functions.php";s:4:"6348";s:12:"ext_icon.gif";s:4:"5ca5";s:17:"ext_localconf.php";s:4:"dc3b";s:14:"ext_tables.php";s:4:"3ea8";s:14:"ext_tables.sql";s:4:"2382";s:16:"locallang_cm.xml";s:4:"dcf9";s:14:"doc/manual.sxw";s:4:"385a";s:36:"lib/class.tx_graytree_browsetree.php";s:4:"313b";s:35:"lib/class.tx_graytree_clickmenu.php";s:4:"5c76";s:28:"lib/class.tx_graytree_db.php";s:4:"0a6d";s:29:"lib/class.tx_graytree_div.php";s:4:"8c98";s:35:"lib/class.tx_graytree_folder_db.php";s:4:"55d7";s:34:"lib/class.tx_graytree_leafdata.php";s:4:"007c";s:34:"lib/class.tx_graytree_leafview.php";s:4:"e1d9";s:33:"lib/class.tx_graytree_tcefunc.php";s:4:"1128";s:30:"lib/class.tx_graytree_view.php";s:4:"1a9d";s:22:"mod_clickmenu/conf.php";s:4:"c8f4";s:23:"mod_clickmenu/index.php";s:4:"6ee2";s:31:"mod_clickmenu/locallang_mod.xml";s:4:"c85f";s:28:"mod_clickmenu/moduleicon.gif";s:4:"adc5";s:37:"mod_cmd/class.tx_graytree_cmd_new.php";s:4:"ae52";s:41:"mod_cmd/class.tx_graytree_cmd_nothing.php";s:4:"dc28";s:40:"mod_cmd/class.tx_graytree_cmd_wizard.php";s:4:"c91d";s:16:"mod_cmd/conf.php";s:4:"c935";s:17:"mod_cmd/index.php";s:4:"4b35";s:21:"mod_cmd/locallang.xml";s:4:"84bb";s:25:"mod_cmd/locallang_mod.xml";s:4:"2faf";s:22:"mod_cmd/moduleicon.gif";s:4:"adc5";s:47:"modfunc_list_list/class.tx_graytree_db_list.php";s:4:"f8c4";s:53:"modfunc_list_list/class.tx_graytree_db_list_extra.inc";s:4:"48e6";s:31:"modfunc_list_list/locallang.xml";s:4:"d190";}',
	'constraints' => array(
		'depends' => array(
			'php' => '4.0.0-0.0.0',
			'typo3' => '3.8.0-4.1.50',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>