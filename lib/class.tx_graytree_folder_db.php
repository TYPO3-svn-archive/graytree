<?php
/***************************************************************
*  Copyright notice
*
*  (c)  2005 	Franz Holzinger <kontakt@fholzinger.com>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Misc GRAYTREE db functions
 *
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id: class.tx_graytree_folder_db.php 151 2006-04-05 05:58:32Z franz $
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
 
require_once(PATH_t3lib.'class.t3lib_befunc.php');


class tx_graytree_folder_db {

	
/***************************************
	 *
	 *	 GRAYTREE sysfolder
	 *
	 ***************************************/


	/**
	 * Create your database table folder
	 * overwrite this if wanted
	 * 
	 * @param	[type]		$pid: ...
	 * @return	void		
	 * @TODO	title aus extkey ziehen
	 * @TODO	Sortierung
	 */
	function createFolder($title = 'Graytree', $module = 'graytree', $pid=0) {		
		$fields_values = array();
		$fields_values['pid'] = $pid;
		$fields_values['sorting'] = 10111; #TODO
		$fields_values['perms_user'] = 31;
		$fields_values['perms_group'] = 31;
		$fields_values['perms_everybody'] = 31;
		$fields_values['title'] = $title;
		$fields_values['tx_graytree_foldername'] =  strtolower($title);
		$fields_values['doktype'] = 254;
		$fields_values['module'] = $module;
		$fields_values['crdate'] = time();
		$fields_values['tstamp'] = time();
		return $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $fields_values);
	}


	/**
	 * Find the extension folders
	 * 
	 * @return	array		rows of found extension folders
	 */
	function getFolders($module = 'graytree',$pid = 0,$title ='' ) {
		$rows=array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,pid,title', 'pages', 'doktype=254 and tx_graytree_foldername = "'.strtolower($title).'"and pid = "'.$pid.'"and module="'.$module.'" '.t3lib_BEfunc::deleteClause('pages'));
    		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$rows[$row['uid']]=$row;
		}
		return $rows;
	}
	
	
	/**
	 * Returns pidList of extension Folders
	 * 
	 * @return	string		commalist of PIDs
	 */
	function getFolderPidList($module = 'graytree') {
		return implode(',',array_keys(tx_graytree_folder_db::getFolders($module)));
	}


	/**
	 * Find the extension folders or create one.
	 * @param  (title) Folder Title as named in pages table
	 * @param  (module) Extension Moduke
	 * @param  (pid) Parent Page id 
	 * @param  (parenTitle) Parent Folder Title
	 * 
	 * @return	array		
	 */
	function initFolders($title = 'Graytree', $module = 'graytree',$pid=0,$parentTitle='')	{
		// creates a GRAYTREE folder on the fly
		// not really a clean way ...
		
		if($parentTitle){
		    $pFolders = tx_graytree_folder_db::getFolders($module,$pid,$parentTitle);
		    $pf = current($pFolders);
		    $pid = $pf['uid'];
		}
	
		$folders = tx_graytree_folder_db::getFolders($module,$pid,$title);
		if (!count($folders)) {
			tx_graytree_folder_db::createFolder($title, $module,$pid);
			$folders = tx_graytree_folder_db::getFolders($module,$pid,$title);
		
		}
		$cf = current($folders);
				
		return array ($cf['uid'],implode(',',array_keys($folders)));	
	
	}	
	
	

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_folder_db.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_folder_db.php']);
}

?>