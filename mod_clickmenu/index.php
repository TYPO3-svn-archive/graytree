<?php

/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj <kasper@typo3.com>
*  (c) 2005-2006 Franz Holzinger <kontakt@fholzinger.com>
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
 * Context menu
 * Script Class for the Context Sensitive Menu in TYPO3 (rendered in top frame, normally writing content dynamically to list frames).
 *
 * The script is called in the top frame of the backend typically by a click on an icon for which a context menu should appear.
 * Either this script displays the context menu horizontally in the top frame or alternatively (default in MSIE, Mozilla) it writes the output to a <div>-layer in the calling document (which then appears as a layer/context sensitive menu)
 * Writing content back into a <div>-layer is necessary if we want individualized context menus with any specific content for any specific element.
 * Context menus can appear for either database elements or files
 * The input to this script is basically the "&init" var which is divided by "|" - each part is a reference to table|uid|listframe-flag.
 *
 * If you want to integrate a context menu in your scripts, please see template::getContextMenuCode()
 *
 * $Id$
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage graytree
 * @see template::getContextMenuCode()
 * $Id$
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 * 1252: class SC_alt_clickmenu
 * 1271:     function init()
 * 1369:     function main()
 * 1403:     function printContent()
 *
 * TOTAL FUNCTIONS: 43
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:lang/locallang_misc.xml');



require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once (PATH_t3lib.'class.t3lib_clipboard.php');
require_once(t3lib_extmgm::extPath('graytree').'lib/class.tx_graytree_clickmenu.php');
require_once(t3lib_extmgm::extPath('graytree').'lib/class.tx_graytree_div.php');


define('GRAYTREE_CLICKMENU_DLOG', '0');

class SC_tx_graytree_clickmenu extends t3lib_SCbase {

		// Internal, static: GPvar:
	var $backPath;					// Back path.
	var $item;						// Definition of which item the click menu should be made for.
	var $reloadListFrame;			// Defines the name of the document object for which to reload the URL.
	var $commandModule;				// Module to call instead of tce_db.php

		// Internal:
	var $content='';				// Content accumulation
	var $dontDisplayTopFrameCM=0;	// If set, then the clickmenu will NOT display in the top frame.
	var $extKey;					// extension key of the caller

	/**
	 * Constructor function for script class.
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$BACK_PATH;

		parent::init();
		
			// Setting GPvars:
		$this->backPath = t3lib_div::_GP('backPath');
		$this->item = t3lib_div::_GP('item');
		$this->reloadListFrame = t3lib_div::_GP('reloadListFrame');
		$this->commandModule = t3lib_div::_GP('cmdMod');
		$this->extKey = t3lib_div::_GP('extKey');

		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG) { 
			t3lib_div::devLog('SC_tx_graytree_clickmenu::init $this->backPath = '.$this->backPath. ' $this->item = ' . $this->item, GRAYTREE_EXTkey);					
			t3lib_div::devLog('SC_tx_graytree_clickmenu::init $this->reloadListFrame = '. $this->reloadListFrame, GRAYTREE_EXTkey);					
			t3lib_div::devLog('SC_tx_graytree_clickmenu::init $this->commandModule = '. $this->commandModule, GRAYTREE_EXTkey);					
			t3lib_div::devLog('SC_tx_graytree_clickmenu::init $this->id ' . $this->id, GRAYTREE_EXTkey);					
		}

			// Takes the backPath as a parameter BUT since we are worried about someone forging a backPath (XSS security hole) we will check with sent md5 hash:
		$inputBP = explode('|',$this->backPath);
		if (count($inputBP)==2 && $inputBP[1]==t3lib_div::shortMD5($inputBP[0].'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
			$this->backPath = $inputBP[0];
		} else {
			$this->backPath = $BACK_PATH;
		}

			// Setting internal array of classes for extending the clickmenu:
		$this->extClassArray = $GLOBALS['TBE_MODULES_EXT']['tx_graytree_clickmenu']['extendCMclasses'];

			// Traversing that array and setting files for inclusion:
		if (is_array($this->extClassArray))	{
			foreach($this->extClassArray as $extClassConf)	{
				if ($extClassConf['path'])	$this->include_once[]=$extClassConf['path'];
			}
		}

			// Initialize template object
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';
		$this->doc->backPath = $BACK_PATH;

		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG)  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::init  vorher $this->dontDisplayTopFrameCM = '. $this->dontDisplayTopFrameCM, GRAYTREE_EXTkey);					
	
			// Setting mode for display and background image in the top frame
		$this->dontDisplayTopFrameCM= $this->doc->isCMlayers() && !$BE_USER->getTSConfigVal('options.contextMenu.options.alwaysShowClickMenuInTopFrame');
		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG)  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::init  nachher ***** $this->dontDisplayTopFrameCM = '. $this->dontDisplayTopFrameCM, GRAYTREE_EXTkey);					
		
		if ($this->dontDisplayTopFrameCM)	{
			$this->doc->bodyTagId.= '-notop';
		}

			// Setting clickmenu timeout
		$secs = t3lib_div::intInRange($BE_USER->getTSConfigVal('options.contextMenu.options.clickMenuTimeOut'),1,100,5);	// default is 5

			// Setting the JavaScript controlling the timer on the page
		$listFrameDoc = $this->reloadListFrame!=2 ? 'top.content.list_frame' : 'top.content';
		$this->doc->JScode.=$this->doc->wrapScriptTags('
	var date = new Date();
	var mo_timeout = Math.floor(date.getTime()/1000);

	roImg =new Image();
	roImg.src = "'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/content_client.gif','width="7" height="10"',1).'";

	routImg =new Image();
	routImg.src = "'.$BACK_PATH.'clear.gif";

	function mo(c)	{	//
		var name="roimg_"+c;
		document[name].src = roImg.src;
		updateTime();
	}
	function mout(c)	{	//
		var name="roimg_"+c;
		document[name].src = routImg.src;
		updateTime();
	}
	function updateTime()	{	//
		date = new Date();
		mo_timeout = Math.floor(date.getTime()/1000);
	}
	function timeout_func()	{	//
		date = new Date();
		if (Math.floor(date.getTime()/1000)-mo_timeout > '.$secs.')	{
			hideCM();
			return false;
		} else {
			window.setTimeout("timeout_func();",1*1000);
		}
	}
	function hideCM()	{	//
		document.location="'.$BACK_PATH.'alt_topmenu_dummy.php";
		return false;
	}

		// Start timer
	timeout_func(); // comment this out for debugging

	'.($this->reloadListFrame ? '
		// Reload list frame:
	if('.$listFrameDoc.'){'.$listFrameDoc.'.document.location='.$listFrameDoc.'.document.location;}' :
	'').'
		');
		
	if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG)  
		t3lib_div::devLog('SC_tx_graytree_clickmenu::init $this->doc->JScode = '.$this->doc->JScode, GRAYTREE_EXTkey);						
	}

	/**
	 * Main function - generating the click menu in whatever form it has.
	 *
	 * @return	void
	 */
	function main()	{

		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG)  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::main', GRAYTREE_EXTkey);
		
			// Initialize Clipboard object:
		$clipObj = t3lib_div::makeInstance('t3lib_clipboard');
		$clipObj->initializeClipboard();
		$clipObj->lockToNormal();	// This locks the clipboard to the Normal for this request.

			// Update clipboard if some actions are sent.
		$CB = t3lib_div::_GET('CB');
		$clipObj->setCmd($CB);
		$clipObj->cleanCurrent();
		$clipObj->endClipboard();	// Saves

		$clickMenuClass = 'tx_graytree_clickMenu';
		if ($this->extKey && t3lib_extMgm::isLoaded($this->extKey))	{
			include_once(t3lib_extMgm::extPath($this->extKey).'mod_clickmenu/class.tx_'.$this->extKey.'_clickmenu.php');
			$clickMenuClass = 'tx_'.$this->extKey.'_clickMenu';
		}
		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG)  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::main $clickMenuClass='.$clickMenuClass, GRAYTREE_EXTkey);
			// Create clickmenu object
		$clickMenu = t3lib_div::makeInstance($clickMenuClass);

			// Set internal vars in clickmenu object:
		$clickMenu->clipObj = $clipObj;
		$clickMenu->extClassArray = $this->extClassArray;
		$clickMenu->dontDisplayTopFrameCM = $this->dontDisplayTopFrameCM;
		$clickMenu->backPath = $this->backPath;

			// Start page
		$this->content.=$this->doc->startPage('Context Sensitive Menu');

		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG)  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::main $this->item = ' . $this->item, GRAYTREE_EXTkey);						
		
			// Set content of the clickmenu with the incoming var, "item"
		$content = $clickMenu->init($this->item);
		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG) {  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::main ANZEIGE ************* clickMenu ***** ', GRAYTREE_EXTkey);						
			$contentarr = explode (" ", $content);
			foreach ($contentarr as $key=>$val) {
				t3lib_div::devLog('content = ' . $val, GRAYTREE_EXTkey);
			}
		}

		$this->content.= $content;
	}

	/**
	 * End page and output content.
	 *
	 * @return	void
	 */
	function printContent()	{
		t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent vor $this->doc->endPage() ', GRAYTREE_EXTkey);
		$this->content.= $this->doc->endPage();
		
		echo $this->content;
		$content = explode("\n" ,$this->content);
		$pos = 0;

		if (TYPO3_DLOG && GRAYTREE_CLICKMENU_DLOG) {  
			t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent ANZEIGE ************* clickMenu ***** ', GRAYTREE_EXTkey);						

			foreach ($content as $key=>$val) {
				if (($pos = strpos($val, 'setLayerObj')) == 0) { 
					t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent $content = ' . $val, GRAYTREE_EXTkey);
				} else {
					t3lib_div::devLog('**********************************'. $pos.'**********************************', GRAYTREE_EXTkey);
					t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent $content = '. substr ($val, 0, 200), GRAYTREE_EXTkey);
					t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent $content = '. substr ($val, 200, 200), GRAYTREE_EXTkey);
					t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent $content = '. substr ($val, 400, 200), GRAYTREE_EXTkey);
					t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent $content = '. substr ($val, 600, 200), GRAYTREE_EXTkey);
					t3lib_div::devLog('SC_tx_graytree_clickmenu::printContent $content = '. substr ($val, 800, 200), GRAYTREE_EXTkey);
				}
				
			}
		} 
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_clickmenu/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_clickmenu/index.php']);
}





// Make instance:
$SOBE = t3lib_div::makeInstance('SC_tx_graytree_clickmenu');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>