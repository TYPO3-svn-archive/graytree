<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (!defined ('GRAYTREE_EXTkey')) {
	define('GRAYTREE_EXTkey',$_EXTKEY);
}

if (!defined ('PATH_txgraytree')) {
	define('PATH_txgraytree', t3lib_extMgm::extPath(GRAYTREE_EXTkey));
}

if (!defined ('PATH_txgraytree_rel')) {
	define('PATH_txgraytree_rel', t3lib_extMgm::extRelPath(GRAYTREE_EXTkey));
}


$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/class.alt_menu_functions.inc']=t3lib_extMgm::extPath(GRAYTREE_EXTkey).'class.ux_alt_menu_functions.php';
?>
