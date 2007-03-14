<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 René Fritz <r.fritz@colorcube.de>
*  (c) 2005- Franz Holzinger <kontakt@fholzinger.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
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
 * Script Class for the clickmenu display
 *
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id$
 *
 *
 */

#define('GRAYTREE_CM1_DLOG', '1');


class tx_graytree_cm1 {
	function main(&$backRef, $menuItems, $table, $uid)	{
		global $BE_USER, $TCA, $LANG;
	
		if (TYPO3_DLOG && GRAYTREE_CM1_DLOG) { 
			t3lib_div::devLog('tx_graytree_cm1 - main  $backRef='.$backRef. ' $table = '.$table . ' $uid = '.$uid, GRAYTREE_EXTkey);					
			t3lib_div::devLog('$menuItems:          ############', GRAYTREE_EXTkey);
			if (count($menuItems)== 0) {
				t3lib_div::devLog('################keine MenuItems ! ################', GRAYTREE_EXTkey);
			}
			foreach ($menuItems as $k1=>$menuItem) {
				t3lib_div::devLog( 'Anzahl = '.count($menuItem), GRAYTREE_EXTkey);
				t3lib_div::devLog( 'Typ: '.$k1, GRAYTREE_EXTkey);
				if (is_array($menuItem)) {
					foreach($menuItem as $k2=>$v2) {
						t3lib_div::devLog($k2.': '.$v2, GRAYTREE_EXTkey);
					}
				}
			}

		}

		$root = ($uid == 0); //$root = 0;
		if (TYPO3_DLOG && GRAYTREE_CM1_DLOG) t3lib_div::devLog('$root='.$root, GRAYTREE_EXTkey);					
	
			// Returns directly, because the clicked item of the root category must not be checked 
		if ($root)	{
			return $menuItems;
		}

		$this->backRef = &$backRef;
		
			// save original items
		$orgItems = $menuItems;

			// just clear the whole menu
		$menuItemsTmp = array();			

		if ($backRef->cmLevel==0)	{

			if (TYPO3_DLOG && GRAYTREE_CM1_DLOG) t3lib_div::devLog('tx_graytree_cm1 cmLevel = 0', GRAYTREE_EXTkey);					
			
				// If record found (or root), go ahead and fill the $menuItems array which will contain data for the elements to render.
			if (is_array($backRef->rec))	{
				if (TYPO3_DLOG && GRAYTREE_CM1_DLOG) t3lib_div::devLog('graytree_cm1. IF-Zweig is_array($backRef)', GRAYTREE_EXTkey);					
					// Get permissions
				$lCP = $BE_USER->calcPerms(t3lib_BEfunc::getRecord('pages',($table=='pages'?$backRef->rec['uid']:$backRef->rec['pid'])));
							
					// Include localllang file
				$LL = $this->includeLL();
				
				// retrieve the original menu items
				$menuItemsTmp = $orgItems;
	
				$menuItemsTmp['spacer1']='spacer';
			}
			
		} elseif ($backRef->cmLevel==1) {
					// Extra options:
				if(isset($orgItems['history'])) {
					$menuItemsTmp['history']=$orgItems['history'];
					if (TYPO3_DLOG && GRAYTREE_CM1_DLOG) t3lib_div::devLog('tx_graytree_cm1  history', GRAYTREE_EXTkey);					
				}
		}
		
		if (TYPO3_DLOG && GRAYTREE_CM1_DLOG)  {
			t3lib_div::devLog('tx_graytree_cm1  history', GRAYTREE_EXTkey);					

			foreach ($menuItemsTmp as $key=>$menuItem) {
				if (is_array($menuItem)) {
					t3lib_div::devLog('-- tx_graytree_cm1 menuItem['. $key . '] --', GRAYTREE_EXTkey);
					foreach ($menuItem as $k1=>$v1) {
						t3lib_div::devLog('['.$k1.']='.$v1, GRAYTREE_EXTkey);
					}
				} else
				{
					t3lib_div::devLog('-- tx_graytree_cm1 menuItem['. $key . '] = '.$menuItem, GRAYTREE_EXTkey);
				}
			}
		}
		return $menuItemsTmp;
	} 


	/**
	 * Multi-function for adding an entry to the $menuItems array
	 *
	 * @param	string		record id
	 * @param	string		Script (eg. file_edit.php) to pass &target= to
	 * @param	string		label for the element
	 * @param	string		icon image
	 * @return	array		Item array, element in $menuItems
	 * @internal
	 */
	function db_launch($id,$cmd,$label,$icon)	{
		t3lib_div::devLog('tx_graytree_cm1  db_launch $id ='. $id . '$cmd = ' .$cmd . '$label = ' . $label . '$icon = '. $icon, GRAYTREE_EXTkey);
		
		$loc='top.content'.(!$this->backRef->alwaysContentFrame?'.list_frame':'');
		$script = PATH_txgraytree_rel.'mod_cmd/index.php?CMD='.$cmd;
		$editOnClick='if('.$loc.'){'.$loc.".document.location=top.TS.PATH_typo3+'".$script.'&id='.rawurlencode($id)."&returnUrl='+top.rawurlencode(".$this->backRef->frameLocation($loc.'.document').");}";

		return $this->backRef->linkItem(
			$label,
			$icon,
			$editOnClick.'return hideCM();'
		);
	}

	
	/**
	 * Includes the [extDir]/locallang_cm.xml and returns the $LOCAL_LANG array found in that file.
	 */
	function includeLL()	{
		$LOCAL_LANG = $GLOBALS['LANG']->includeLLFile('EXT:graytree/locallang_cm.xml',FALSE);
		return $LOCAL_LANG;
	}



} 


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/graytree/class.tx_graytree_cm1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/graytree/class.tx_graytree_cm1.php"]);
}

?>