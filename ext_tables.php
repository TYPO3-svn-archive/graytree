<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{

	// Command module
		
	t3lib_extMgm::addModule('web','txgraytreeCmd','',t3lib_extMgm::extPath($_EXTKEY).'mod_cmd/');
	
	t3lib_extMgm::insertModuleFunction(
		'web_txgraytreeCmd',
		'tx_graytree_cmd_nothing',
		t3lib_extMgm::extPath($_EXTKEY).'mod_cmd/class.tx_graytree_cmd_nothing.php',
		'LLL:EXT:graytree/mod_cmd/locallang.php:tx_graytree_cmd_nothing.title'
	);	
	
	t3lib_extMgm::insertModuleFunction(
		'web_txgraytreeCmd',
		'tx_graytree_cmd_new',
		t3lib_extMgm::extPath($_EXTKEY).'mod_cmd/class.tx_graytree_cmd_new.php',
		'LLL:EXT:graytree/mod_cmd/locallang.php:tx_graytree_cmd_new.title'
	);		

		// add context menu
	$GLOBALS['TBE_MODULES_EXT']['tx_graytree_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_graytree_cm1',
		'path' => t3lib_extmgm::extPath('graytree').'class.tx_graytree_cm1.php'
	);
	
	// add new filed to pages
	
	
	$tempColumns = Array (
		'tx_graytree_foldername' => Array (
			'exclude' => 1,
			'label' => 'internal foldername',
			'config' => Array (
				'type' => 'none',
				
			)
		),
	);
	
	
	t3lib_div::loadTCA('pages');
	
	
	t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
}
?>