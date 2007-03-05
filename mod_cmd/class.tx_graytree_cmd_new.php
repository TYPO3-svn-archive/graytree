<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 René Fritz (r.fritz@colorcube.de)
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
 * Command module 'new command'
 *
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id: class.tx_graytree_cmd_new.php 148 2006-04-04 14:17:44Z franz $
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *

 *
 */

//	// DEFAULT initialization of a module [BEGIN]
//unset($MCONF);
//require ("conf.php");
//require ($BACK_PATH."init.php");
//require ($BACK_PATH."template.php");


require_once(PATH_t3lib.'class.t3lib_extobjbase.php');


define('GRAYTREE_CMD_DLOG', '1');


class tx_graytree_cmd_new extends t3lib_extobjbase {


	/**
	 * Do some init things and set some things in HTML header
	 * 
	 * @return	void		
	 */
	function head() {
		global $LANG, $SOBE, $BACK_PATH, $TYPO3_CONF_VARS;

		$SOBE->pageTitle = $LANG->getLL('newRecordGeneral',1);
	}


	/**
	 * Main function
	 *
	 * @return	void
	 */
	function main()	{
		global $LANG, $SOBE, $TCA, $BACK_PATH;

		$content ='';

		$param = t3lib_div::_GP('edit');
		$table = key($param);
		$uid = (string)key($param[$table]);
		$cmd = $param[$table][$uid];
		$pid = t3lib_div::_GP('id');
	
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) {
			t3lib_div::devLog('TYPO3 tx_graytree_cmd_new::main $param = '.$param, GRAYTREE_EXTkey);
			t3lib_div::devLog('TYPO3 tx_graytree_cmd_new::main $table = '.$table, GRAYTREE_EXTkey);
			t3lib_div::devLog('TYPO3 tx_graytree_cmd_new::main $uid = '.$uid, GRAYTREE_EXTkey);
			t3lib_div::devLog('TYPO3 tx_graytree_cmd_new::main $cmd = '.$cmd, GRAYTREE_EXTkey);
			t3lib_div::devLog('TYPO3 tx_graytree_cmd_new::main $pid = '.$pid, GRAYTREE_EXTkey);
		}
	
//		$content='This is the GET/POST vars sent to the script:<BR>'.
//			'GET:'.t3lib_div::view_array($GLOBALS['HTTP_GET_VARS']).'<BR>'.
//			'POST:'.t3lib_div::view_array($GLOBALS['HTTP_POST_VARS']).'<BR>'.
//			'';
		
		if(is_array($TCA[$table]) AND $cmd=='new') {
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_cmd_new::main nach if(is_array($TCA[$table]) AND $cmd==\'new\')  ', GRAYTREE_EXTkey);

//require_once(PATH_txgraytree.'lib/class.tx_graytree_folder_db.php');
//list($this->defaultPid,$this->defaultFolder,$this->folderList) = tx_graytree_folder_db::initFolders('');

			$getArray['edit'][$table][$pid]='new';
			$getArray['defVals'] = t3lib_div::_GP('defVals');
			$getArray['defVals'][$table]['pid']=$pid;

			$getArray = t3lib_div::compileSelectedGetVarsFromArray('edit,defVals,overrideVals,columnsOnly,disHelp,noView,editRegularContentFromId',$getArray);
			$getUrl = t3lib_div::implodeArrayForUrl('',$getArray);
		
			header('Location: '.$BACK_PATH.'alt_doc.php?id='.$pid.$getUrl);
		} else {
			$content.= 'wrong comand!';
		}
	
#TODO do it always this way (with if)		
		if ($this->pObj->returnUrl) {
			$content.= '<br /><br />'.$this->pObj->btn_back('',$this->pObj->returnUrl);
		}

			// CSH:
#		$content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'file_rename', $GLOBALS['BACK_PATH'],'<br/>');

		return $content;

	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_cmd/class.tx_graytree_cmd_new.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_cmd/class.tx_graytree_cmd_new.php']);
}


?>