<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2007 Franz Holzinger <kontakt@fholzinger.com>
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
 * Contains base class for creating a browsable array/page/folder tree in HTML
 * works in union with the tx_graytree_leafData class
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @see t3lib_browsetree, t3lib_pagetree
 * $Id$
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



require_once (PATH_t3lib.'class.t3lib_iconworks.php');
require_once (PATH_t3lib.'class.t3lib_befunc.php');
require_once (PATH_t3lib.'class.t3lib_div.php');

define('GRAYTREE_LEAFVIEW_DLOG', '0'); // Switch for debugging error messages


class tx_graytree_leafView {
	var $graytree_leafData;
	/**
	 * Back path for icons
	 */

	var $backPath;
	var $clickMenuScript; // alternative click menu script
	var $extKey;	// caller extension key. Leaf this empty
			// if you do not have a file class.tx_($extKey)_clickmenu.php with a class
			// tx_($extKey)_clickMenu in a subfolder 'mod_clickmenu' which extends tx_graytree_clickMenu 

	/**
	 * Icon file name for the table including the path.
	 */
	var $iconFullName = '';	// do not set this if you want TYPO3 to determine the images itself (hidden)
	// Icon path to the root icon
	var $iconPath;	// only the root table needs to set the iconPath

	var $title = ''; // display name for the leaf
	var $ext_IconMode = false;		// If true, no context menu is rendered on icons. If set to "titlelink" the icon is linked as the title is.

	/**
	 * Unique name for the leaf.
	 * Used as key for storing the tree into the BE users settings.
	 * Used as key to pass parameters in links.
	 * MUST NOT contain underscore chars.
	 * etc.
	 */
	var $name = '';
	
	var $titleAttrib = 'title'; // HTML title attribute
	
	var $isCategory = true;	// set if this is a category in the tree
	var $tree; // reference to tree class
	var $usePM; // if the PlusMinus symbol shall be used for this class

	/**
	 * initialisation
	 * must only be called from init function of the derived class
	 * this connects the view to its data object and the tree object
	 * 
	 * @param	object	reference to the leaf data object
	 * @param	object	reference to the tree object
	 * @return	void
	 */
	function init(&$graytree_leafData, &$tree)	{
		global $BACK_PATH;
	
		$this->graytree_leafData = &$graytree_leafData;
		$this->backPath = $BACK_PATH;	// Setting backpath.
		$this->iconPath = ($this->iconPath ? $this->iconPath : $this->backPath.'gfx/i/');
		$this->iconFullName = ($this->iconFullName ? $this->iconFullName : ($this->iconName ? $this->iconPath . $this->iconName : ''));
		$this->tree = &$tree;
	}


	/**
	 * returns the data object for the data class of a leaf
	 * 
	 * @param	void
	 * @return	object	reference to the leaf data object
	 */
	function &getData() {
		return $this->graytree_leafData;
	}


	/*******************************************
	 *
	 * rendering parts
	 *
	 *******************************************/


	/**
	 * Wrapping $title in a-tags.
	 *
	 * @param	string		Title string
	 * @param	string		Item record
	 * @param	integer		Bank pointer (which mount point number)
	 * @return	string
	 * @access private
	 */
	function wrapTitle($title,$row,$bank=0)	{
		$res = '';
		
		if (TYPO3_DLOG && GRAYTREE_LEAFVIEW_DLOG) t3lib_div::devLog('tx_graytree_leafView::wrapTitle  $title = '. $title. ' $leaf = '.$leaf, GRAYTREE_EXTkey);
		if ($this->isCategory)	{
			$aOnClick = 'return jumpTo(\''.$this->getJumpToParam($row).'\',this,\''.$this->domIdPrefix.$this->graytree_leafData->getId($row).'_'.$bank.'\',\'\');';
		} else {
			$data = $this->getData();
			// $res = tx_graytree_div::clickMenuWrap($this->extKey, $title, $data->table, $row['pid'], $row['uid'], false, $addParams='', $enDisItems='', '', $this->clickMenuScript);
			// +++
			// $addParam='&columnsOnly='.rawurlencode(implode(',',$GLOBALS['TCA'][$data->table]['ctrl']['enablecolumns']));
			$param = 'edit['.$data->table.']['.$row['uid'].']=edit'.$addParam;
			$url = 'alt_doc.php';
			$aOnClick = 'return jumpTo(\''.$param.'\',this,\''.$this->domIdPrefix.$this->graytree_leafData->getId($row).'_'.$bank.'\',\''.$url.'\');';		
		}
		$res = '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.$title.'</a>';

		return $res;
	}


	/**
	 * Wrapping the image tag, $icon, for the row, $row (except for mount points)
	 *
	 * @param	string		The image tag for the icon
	 * @param	array		The row for the current element
	 * @return	string		The processed icon input value.
	 * @access private
	 */

	function wrapIcon($icon,$row)	{
			// Add title attribute to input icon tag
		$theIcon = $this->addTagAttributes($icon,($this->titleAttrib ? $this->titleAttrib.'="'.$this->getTitleAttrib($row).'"' : ''));
				
			// Wrap icon in click-menu link.
		if (!$this->ext_IconMode)	{
			$tempObj = $this->getData();
			$pid = $tempObj->getPid($row);
			$pid = ($pid ? $pid : $this->tree->rootPid);
			$theIcon = tx_graytree_div::clickMenuWrap($this->extKey,$theIcon, $tempObj->table, $pid, $tempObj->getId($row), 0, $addParams='', $enDisItems='', '', $this->clickMenuScript);
		} elseif (!strcmp($this->ext_IconMode,'wrapIcon: titlelink'))	{

			// unused for now
			$aOnClick = 'return jumpTo(\''.$this->getJumpToParam($row).'\',this,\''.$this->domIdPrefix.$this->getId($row).'_'.$this->bank.'\',\'\');';
			$theIcon='<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.$theIcon.'</a>';
		}
		return $theIcon;
	}


	/**
	 * Adds attributes to image tag.
	 *
	 * @param	string		Icon image tag
	 * @param	string		Attributes to add, eg. ' border="0"'
	 * @return	string		Image tag, modified with $attr attributes added.
	 */
	function addTagAttributes($icon,$attr)	{
		return ereg_replace(' ?\/?>$','',$icon).' '.$attr.' />';
	}


	/**
	 * Adds a red "+" to the input string, $str, if the field "php_tree_stop" in the $row (pages) is set
	 *
	 * @param	string		Input string, like a page title for the tree
	 * @param	array		record row with "php_tree_stop" field
	 * @return	string		Modified string
	 * @access private
	 */
	function wrapStop($str,$row)	{
		if ($row['php_tree_stop'])	{
			$str.='<span class="typo3-red">+ </span>';
		}
		return $str;
	}


	/******************************
	 *
	 * Functions that might be overwritten by extended classes
	 *
	 ********************************/


	/**
	 * Get icon for the row.
	 * If $this->iconFullName is set, try to get icon based on those values.
	 *
	 * @param	array		Item row.
	 * @return	string		Image tag.
	 */
	function getIcon($row) {
		$rc = '';
		if ($this->iconFullName) {
			$icon = '<img'.t3lib_iconWorks::skinImg('',$this->iconFullName,'width="18" height="16"').' alt="" />';
		} else {
			$icon = t3lib_iconWorks::getIconImage($this->graytree_leafData->table,$row,$this->backPath,'align="top" class="c-recIcon"');
		}

		$rc = $this->wrapIcon($icon,$row);
		return $rc;
	}


	/**
	 * returns the link from the tree used to jump to a destination
	 *
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
	function getJumpToParam($row) {
		// vorher: $res = '&SLCMD['.$command.']['.	$this->name.']['.$row['uid'].']=1';
		//$res = '&table['.$this->graytree_leafData->table.']['.$row['uid'].']=1';
		$res = '&id='.$row['pid'].'&control['.$this->graytree_leafData->table.'][uid]='.$row['uid'];
		return $res;
	}


	/**
	 * Returns the default icon for the leaf
	 *
	 * @return	[type]		...
	 */
	function getDefaultIcon()	{
		return $this->iconFullName;
	}
	

	/**
	 * Returns the path to the icon
	 *
	 * @return	[type]		...
	 */
	function getIconPath()	{
		return $this->iconPath;
	}


	/**
	 * Returns the title for the input record. If blank, a "no title" label (localized) will be returned.
	 * Do NOT htmlspecialchar the string from this function - has already been done.
	 *
	 * @param	array		The input row array (where the key "title" is used for the title)
	 * @param	integer		Title length (30)
	 * @return	string		The title.
	 */
	function getTitleStr($row,$titleLen=30)	{
		$title = (!strcmp(trim($row['title']),'')) ? '<em>['.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.no_title',1).']</em>' : htmlspecialchars(t3lib_div::fixed_lgd_cs($row['title'],$titleLen));
		return $title;
	}


	/**
	 * Returns the value for the image "title" attribute
	 *
	 * @param	array		The input row array (where the key "title" is used for the title)
	 * @return	string		The attribute value (is htmlspecialchared() already)
	 * @see wrapIcon()
	 */
	function getTitleAttrib($row) {
		return htmlspecialchars('id='.$row['uid']);
	}
	
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_leafview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_leafview.php']);
}
?>
