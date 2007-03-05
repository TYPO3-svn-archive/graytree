<?php

########################################################################
# Extension Manager/Repository config file for ext: "graytree"
#
# Auto generated 03-08-2006 17:10
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
	'author_email' => 'kontakt@fholzinger.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.0.6',
	'_md5_values_when_last_written' => 'a:32:{s:9:"ChangeLog";s:4:"c063";s:25:"class.tx_graytree_cm1.php";s:4:"a681";s:31:"class.ux_alt_menu_functions.php";s:4:"f0fd";s:12:"ext_icon.gif";s:4:"5ca5";s:17:"ext_localconf.php";s:4:"dc3b";s:14:"ext_tables.php";s:4:"c39f";s:14:"ext_tables.sql";s:4:"2382";s:16:"locallang_cm.xml";s:4:"dcf9";s:36:"lib/class.tx_graytree_browsetree.php";s:4:"643a";s:35:"lib/class.tx_graytree_clickmenu.php";s:4:"47cd";s:28:"lib/class.tx_graytree_db.php";s:4:"fc6f";s:29:"lib/class.tx_graytree_div.php";s:4:"5998";s:35:"lib/class.tx_graytree_folder_db.php";s:4:"bd37";s:34:"lib/class.tx_graytree_leafdata.php";s:4:"e863";s:34:"lib/class.tx_graytree_leafview.php";s:4:"bfd3";s:33:"lib/class.tx_graytree_tcefunc.php";s:4:"470d";s:30:"lib/class.tx_graytree_view.php";s:4:"082c";s:22:"mod_clickmenu/conf.php";s:4:"2738";s:23:"mod_clickmenu/index.php";s:4:"fade";s:31:"mod_clickmenu/locallang_mod.xml";s:4:"c85f";s:28:"mod_clickmenu/moduleicon.gif";s:4:"adc5";s:37:"mod_cmd/class.tx_graytree_cmd_new.php";s:4:"35b3";s:41:"mod_cmd/class.tx_graytree_cmd_nothing.php";s:4:"4ef5";s:40:"mod_cmd/class.tx_graytree_cmd_wizard.php";s:4:"895c";s:16:"mod_cmd/conf.php";s:4:"d663";s:17:"mod_cmd/index.php";s:4:"fc79";s:21:"mod_cmd/locallang.xml";s:4:"84bb";s:25:"mod_cmd/locallang_mod.xml";s:4:"2faf";s:22:"mod_cmd/moduleicon.gif";s:4:"adc5";s:47:"modfunc_list_list/class.tx_graytree_db_list.php";s:4:"5157";s:53:"modfunc_list_list/class.tx_graytree_db_list_extra.inc";s:4:"3c67";s:31:"modfunc_list_list/locallang.xml";s:4:"d190";}',
	'constraints' => array(
		'depends' => array(
			'php' => '4.0.0-',
			'typo3' => '3.8.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>