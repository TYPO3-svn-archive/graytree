<?php
/***************************************************************
*  Copyright notice
*
*  (c)  1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)  
*  (c)       2005 Ingo Schmitt (is@marketing-factory.de)
*  All  rights reserved
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
 * Needed change/fix for the changing nav frames
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Ingo Schmitt <is@marketing-factory.de> 
 * @maintainer	Ingo Schmitt <is@marketing-factory.de>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id: class.ux_alt_menu_functions.php 148 2006-04-04 14:17:44Z franz $
 *
 * 
 */



class ux_alt_menu_functions extends alt_menu_functions{

	var $getNavFramePrefix_debug=false;
	/**
	 * Returns a prefix used to call the navframe with parameters to call the scripts defined in the modules info array.
	 *
	 * @param	string		Module info array
	 * @param	string		Submodule info array
	 * @return	string		Result url string
	 */
	function getNavFramePrefix($moduleInfo, $subModuleInfo=array()) {
		global $BE_USER;

		$prefix = '';
		$navFrameScript = $subModuleInfo['navFrameScript'] ? $subModuleInfo['navFrameScript'] : $moduleInfo['navFrameScript'];
		$navFrameScriptParam = isset($subModuleInfo['navFrameScriptParam']) ? $subModuleInfo['navFrameScriptParam'] : $moduleInfo['navFrameScriptParam'];
		
		if (TYPO3_DLOG && $this->getNavFramePrefix_debug) 
		{
			t3lib_div::devLog('Graytree ux_alt_menu_functions - getNavFramePrefix ', GRAYTREE_EXTkey);
		}
		if ($navFrameScript)	{
			if ($BE_USER->uc['condensedMode'])	{
				$prefix=$this->wrapLinkWithAB($navFrameScript).$navFrameScriptParam.'&currentSubScript=';
				if (TYPO3_DLOG && $this->getNavFramePrefix_debug) 
				{
					t3lib_div::devLog('ux_alt_menu_functions - condensedMode $prefix = '.$prefix, GRAYTREE_EXTkey);
				}
			} else {
				$prefix='alt_mod_frameset.php?'.
				'fW="+top.TS.navFrameWidth+"'.
				'&nav="+top.TS.PATH_typo3+"'.rawurlencode($this->wrapLinkWithAB($navFrameScript).$navFrameScriptParam).
				'&script=';
				if (TYPO3_DLOG && $this->getNavFramePrefix_debug) 
				{
					t3lib_div::devLog('ux_alt_menu_functions - $prefix = '.$prefix, GRAYTREE_EXTkey);
				}
			}
		}

		return $prefix;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/class.ux_alt_menu_functions.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/class.ux_alt_menu_functions.php']);
}
?>