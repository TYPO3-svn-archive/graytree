<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2005 René Fritz (r.fritz@colorcube.de)
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
 * Command module 'no command'
 *
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id: class.tx_graytree_cmd_nothing.php 148 2006-04-04 14:17:44Z franz $
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *

 *
 */


require_once(PATH_t3lib.'class.t3lib_extobjbase.php');



class tx_graytree_cmd_nothing extends t3lib_extobjbase {


	/**
	 * Do some init things and set some things in HTML header
	 * 
	 * @return	void		
	 */
	function head() {
		global $LANG, $SOBE, $BACK_PATH, $TYPO3_CONF_VARS;

		$SOBE->pageTitle = $LANG->getLL('tx_graytree_cmd_nothing.title');
	}


	/**
	 * Main function
	 *
	 * @return	void
	 */
	function main()	{
		global $LANG, $SOBE;

		$content ='';


		$content.= $this->pObj->wrongCommandMessage();
		
		$content.= '<br /><br />'.$this->pObj->btn_back('',$this->pObj->returnUrl);


			// CSH:
#		$content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'file_rename', $GLOBALS['BACK_PATH'],'<br/>');


		return $content;

	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_cmd/class.tx_graytree_cmd_nothing.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_cmd/class.tx_graytree_cmd_nothing.php']);
}


?>