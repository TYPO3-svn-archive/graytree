<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2006 Ren� Fritz (r.fritz@colorcube.de)
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
*  A copy is found in the textfile GPL.txt and important notices to the license 
*  from the author is found in LICENSE.txt distributed with these scripts.
*
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * div functions
 *
 * @author	Ren� Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * $Id$
 *
 */

define('GRAYTREE_DIV_DLOG', '0');

class tx_graytree_div {

	/**
	 * Makes click menu link (context sensitive menu)
	 * Returns $str (possibly an <|img> tag/icon) wrapped in a link which will activate the context sensitive menu for the record ($table/$uid) or file ($table = file)
	 * The link will load the top frame with the parameter "&item" which is the table,uid and listFr arguments imploded by "|": rawurlencode($table.'|'.$uid.'|'.$listFr)
	 *
	 * @param   string      extension key of the caller
	 * @param	string		String to be wrapped in link, typ. image tag.
	 * @param	string		Table name/File path. If the icon is for a database record, enter the tablename from $TCA. If a file then enter the absolute filepath
	 * @param	integer		PID for the sysfolder containing the record
	 * @param	integer		If icon is for database record this is the UID for the record from $table
	 * @param	boolean		Tells the top frame script that the link is coming from a "list" frame which means a frame from within the backend content frame.
	 * @param	string		Additional GET parameters for the link to tx_graytree_clickMenu.php
	 * @param	string		Enable / Disable click menu items. Example: "+new,view" will display ONLY these two items (and any spacers in between), "new,view" will display all BUT these two items.
	 * @param	string		Clickmenu script. Default: $BACK_PATH.tx_graytree_clickMenu.php.
	 * @return	string		The link-wrapped input string.
	 */
	function clickMenuWrap($extKey, $str, $table, $pid='', $uid='', $listFrame=true, $addParams='', $enDisItems='', $cmdMod='', $clickMenuScript='')	{
		if (TYPO3_DLOG && GRAYTREE_DIV_DLOG) t3lib_div::devLog('tx_graytree_div clickMenuWrap $table = '.$table.' pid ='. $pid. ' $uid = '.$uid, GRAYTREE_EXTkey);
		$onClick = tx_graytree_div::clickMenuOnClick($extKey, $table, $pid, $uid, $listFrame, $addParams, $enDisItems, $cmdMod='', $clickMenuScript);
		if (TYPO3_DLOG && GRAYTREE_DIV_DLOG) t3lib_div::devLog('tx_graytree_div $onClick = '.$onClick, GRAYTREE_EXTkey);
		return '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.$str.'</a>';
	}


	/**
	 * Makes click menu link (context sensitive menu)
	 * Returns $str (possibly an <|img> tag/icon) wrapped in a link which will activate the context sensitive menu for the record ($table/$uid) or file ($table = file)
	 * The link will load the top frame with the parameter "&item" which is the table,uid and listFr arguments imploded by "|": rawurlencode($table.'|'.$uid.'|'.$listFr)
	 *
	 * @param   string      extension key of the caller
	 * @param	string		Table name/File path. If the icon is for a database record, enter the tablename from $TCA. If a file then enter the absolute filepath
	 * @param	integer		If icon is for database record this is the UID for the record from $table
	 * @param	integer		PID for the sysfolder containing the record
	 * @param	boolean		Tells the top frame script that the link is coming from a "list" frame which means a frame from within the backend content frame.
	 * @param	string		Additional GET parameters for the link to tx_graytree_clickMenu.php
	 * @param	string		Enable / Disable click menu items. Example: "+new,view" will display ONLY these two items (and any spacers in between), "new,view" will display all BUT these two items.
	 * @param	string		tce target script to be called by clickmenu.
	 * @param	string		Clickmenu script. Default: $BACK_PATH.tx_graytree_clickMenu.php.
	 * @return	string		The link-wrapped input string.
	 */	
	function clickMenuOnClick($extKey, $table, $pid='', $uid='', $listFrame=true, $addParams='', $enDisItems='', $cmdMod='', $clickMenuScript='')	{
		if (TYPO3_DLOG && GRAYTREE_DIV_DLOG) t3lib_div::devLog('tx_graytree_div clickMenuOnClick  $pid = '. $pid . ' $uid = '.$uid, GRAYTREE_EXTkey);
		$clickMenuScript = $clickMenuScript ? $clickMenuScript : $GLOBALS['BACK_PATH'].t3lib_extmgm::extRelPath('graytree').'mod_clickmenu/index.php';
		
		$cmdMod = $cmdMod ? '&cmdMod'.$cmdMod : '';
		
		$backPath = '&backPath='.rawurlencode($GLOBALS['BACK_PATH']).'|'.t3lib_div::shortMD5($GLOBALS['BACK_PATH'].'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
		
		$paramsArr = array('table'=>$table,'pid'=>$pid, 'uid'=>$uid,'listFrame'=>$listFrame,'enDisItems'=>$enDisItems);
		$itemParam = tx_graytree_div::compilePipeParams($paramsArr);
		if (TYPO3_DLOG && GRAYTREE_DIV_DLOG) t3lib_div::devLog('tx_graytree_div clickMenuOnClick  $itemParam = '. $itemParam, GRAYTREE_EXTkey);

		$url = $clickMenuScript.'?id='.$pid.'&extKey='.$extKey.'&item='.rawurlencode($itemParam).$backPath.$cmdMod.$addParams;
		$onClick = 'showClickmenu_raw(\''.$url.'\');'.template::thisBlur().'return false;';
		if (TYPO3_DLOG && GRAYTREE_DIV_DLOG) t3lib_div::devLog('tx_graytree_div::clickMenuOnClick $onClick = '. $onClick, GRAYTREE_EXTkey);
		return $onClick;
	}


	function compilePipeParams ($paramsArr) {
		$params = array();

		foreach($paramsArr as $key => $value) {
			if (t3lib_div::testInt($value) || $value)	{
				$params[] = $key.':'.$value;
			}
		}
		return implode ('|', $params);
	}


	function decodePipeParams($paramStr) {
		$paramsArr = array();
		
		$params = explode('|', $paramStr);

		foreach($params as $value) {
			list($key,$value) = $params = explode(':', $value);
			$paramsArr[$key] = $value;
		} 
		return $paramsArr;
	}


	/**
	 * Takes comma-separated lists and arrays and removes all duplicates
	 *
	 * @param	string		Accept multiple parameters which can be comma-separated lists of values and arrays.
	 * @return	string		Returns the list without any duplicates of values, space around values are trimmed
	 */
	function uniqueList()	{
		$listArray = array();

		$arg_list = func_get_args();
		foreach ($arg_list as $in_list)	{

			if (!is_array($in_list) AND empty($in_list))	{
				continue;
			}

			if (!is_array($in_list))	{
				$in_list = t3lib_div::trimExplode(',',$in_list,true);
			}
			if(count($in_list)) {
				$listArray = array_merge($listArray,$in_list);
			}
		}

		return implode(',',t3lib_div::uniqueArray($listArray));
	}


	/**
	 * Extract a list of uid's from an item array
	 *
	 * @param	array		array of item arrays
	 * @param	boolean		If set an uid list string is returned, otherwise an array
	 * @return	array		List of uid's
	 */
	function getUIDsFromItemArray ($itemArr, $makeList=TRUE) {
		$uidList = array();

		foreach ($itemArr as $item) {
			if($item['uid']=intval($item['uid'])) {
				$uidList[$item['uid']] = $item['uid'];
			}
		}
		$uidList = $makeList ? implode(',',$uidList) : $uidList;
		return $uidList;
	}


	/***************************************
	 *
	 *	 Arrays
	 *
	 ***************************************/


	function array_copy($target, $source, $keys='') {
		if (!is_array($keys)) {
			$keys = t3lib_div::trimExplode(',', $keys, 1);
		}
		foreach ($keys as $key) {
			if (isset($source[$key])) {
				$target[$key] = $source[$key];
			}
		}
		return $target;
	}


	/**
	 * This function is used to escape any ' -characters when transferring text to JavaScript!

	 * Function copied from DAM 1.0.7, lib/class.tx_dam_selprocbase.php

	 *
	 * @param   string      String to escape

	 * @param   boolean     If set, also backslashes are escaped.

	 * @param   string      The character to escape, default is ' (single-quote)

	 * @return  string      Processed input string

	 */
	function slashJS($string,$extended=0,$char="'") {
		if ($extended)	{
			$string = str_replace ("\\", "\\\\", $string);
		}
		$rc = str_replace ($char, "\\".$char, $string);
		return $rc;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_div.php']);
}


?>
