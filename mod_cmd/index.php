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
 * TreeLib command module
 * Script class for the graytree command script
 * 
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id$
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]

 *
 */



unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

require_once (PATH_t3lib.'class.t3lib_scbase.php');

$LANG->includeLLFile('EXT:graytree/mod_cmd/locallang.php');


// Module is available to everybody
// $BE_USER->modAccess($MCONF,1);

#define('GRAYTREE_CMD_DLOG', '1');



class tx_graytree_mod_cmd extends t3lib_SCbase {

	/**
	 * the action for the form tag
	 */
	var $actionTarget = '';

	/**
	 * the page title
	 */
	var $pageTitle = '[no title]';
	
	/**
	 * t3lib_basicFileFunctions object
	 */
	var $basicFF;	
	
	
	
	/**
	 * Initializes the backend module
	 * 
	 * @return	void		
	 */
	function init()	{
		global $BE_USER, $SOBE, $TYPO3_CONF_VARS, $FILEMOUNTS;
$TYPO3_CONF_VARS['SYS']['doNotCheckReferer']=1;
#TODO
		$this->vC = t3lib_div::_GP('vC');
		
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::init ', GRAYTREE_EXTkey);		
		
			// Checking referer / executing
		$refInfo=parse_url(t3lib_div::getIndpEnv('HTTP_REFERER'));
		$httpHost = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
		if ($httpHost!=$refInfo['host'] && $this->vC!=$BE_USER->veriCode() && !$TYPO3_CONF_VARS['SYS']['doNotCheckReferer'])	{
			t3lib_BEfunc::typo3PrintError ('Access Error','Referer did not match and veriCode was not valid either!','');
			exit;
		}



		parent::init();
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::init $this->id ' . $this->id, GRAYTREE_EXTkey);

#TODO			// Initialize GPvars:
		$this->data = t3lib_div::_GP('data');
		$this->returnUrl = t3lib_div::_GP('returnUrl');
		$this->returnUrl = $this->returnUrl ? $this->returnUrl : t3lib_div::getIndpEnv('HTTP_REFERER');
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::init $this->returnUrl: ' . $this->returnUrl, GRAYTREE_EXTkey);
		
		$this->redirect = t3lib_div::_GP('redirect');
		$this->redirect = $this->redirect ? $this->redirect : $this->returnUrl;
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::init $this->redirect: ' . $this->redirect, GRAYTREE_EXTkey);
		
	}	
	


	/**
	 * Loads $this->extClassConf with the configuration for the CURRENT function of the menu.
	 * If for this array the key 'path' is set then that is expected to be an absolute path to a file which should be included - so it is set in the internal array $this->include_once
	 *
	 * @param	string		The key to MOD_MENU for which to fetch configuration. 'function' is default since it is first and foremost used to get information per "extension object" (I think that is what its called)
	 * @param	string		The value-key to fetch from the config array. If NULL (default) MOD_SETTINGS[$MM_key] will be used. This is usefull if you want to force another function than the one defined in MOD_SETTINGS[function]. Call this in init() function of your Script Class: handleExternalFunctionValue('function', $forcedSubModKey)
	 * @return	void
	 * @see getExternalItemConfig(), $include_once, init()
	 */
	function handleExternalFunctionValue($MM_key='function', $MS_value=NULL)	{
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::handleExternalFunctionValue $MM_key = '.$MM_key. ' $MS_value = '.$MS_value, GRAYTREE_EXTkey);
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::handleExternalFunctionValue $this->CMD = '.$this->CMD, GRAYTREE_EXTkey);
		
		if (is_null($MS_value)) {
			if ($this->CMD) {
				$MS_value = $this->CMD;
			} else {
				$MS_value = 'tx_graytree_cmd_nothing';
			};
		}

		$this->extClassConf = $this->getExternalItemConfig($this->MCONF['name'],$MM_key,$MS_value);
		if (is_array($this->extClassConf) && $this->extClassConf['path'])	{
			$this->include_once[]=$this->extClassConf['path'];
		} else {
			$this->extClassConf = $this->getExternalItemConfig($this->MCONF['name'],$MM_key,'tx_graytree_cmd_nothing');
			if (is_array($this->extClassConf) && $this->extClassConf['path'])	{
				$this->include_once[]=$this->extClassConf['path'];
			}	
		}
#		$this->MOD_MENU['function'][$MS_value] = $MS_value;
#		$this->MOD_SETTINGS['function'] = $MS_value;
	}




	
	/**
	 * Main function of the module. Write the content to $this->content
	 * 
	 * @return	void		
	 */
	function main()	{
		global $BE_USER, $LANG, $BACK_PATH, $TYPO3_CONF_VARS, $HTTP_GET_VARS, $HTTP_POST_VARS;

		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main', GRAYTREE_EXTkey);
		
		//
		// Initialize the template object
		//

		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType = 'xhtml_trans';

		// Access check...
		// The page will show only if there is a valid page and if this page may be viewed by the user
#		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
#		$access = is_array($this->pageinfo) ? 1 : 0;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			$access = TRUE;
		}




			// page-selection permission clause (reading)
		$this->perms_clause = $BE_USER->getPagePermsClause(1);
		
		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $this->id='.$this->id, GRAYTREE_EXTkey);
				
			// Id a positive id is supplied, ask for the page record with permission information contained:
		if ($this->id > 0)	{
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		}

		if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $this->pageinfo[\'uid\']='.$this->pageinfo['uid'], GRAYTREE_EXTkey);

			// If a page-record was returned, the user had read-access to the page.
		if ($this->pageinfo['uid'])	{
				// Get record of parent page
			$this->pidInfo=t3lib_BEfunc::getRecord('pages',$this->pageinfo['pid']);
				// Checking the permissions for the user with regard to the parent page: Can he create new pages, new content record, new page after?
			if ($BE_USER->doesUserHaveAccess($this->pageinfo,8))	{
				$this->newPagesInto=1;
			}
			if ($BE_USER->doesUserHaveAccess($this->pageinfo,16))	{
				$this->newContentInto=1;
			}

			if (($BE_USER->isAdmin()||is_array($this->pidInfo)) && $BE_USER->doesUserHaveAccess($this->pidInfo,8))	{
				$this->newPagesAfter=1;
			}
		} elseif ($BE_USER->isAdmin())	{
				// Admins can do it all
			$this->newPagesInto=1;
			$this->newContentInto=1;
			$this->newPagesAfter=0;
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $this->pageinfo[\'uid\']='.$this->pageinfo['uid'], GRAYTREE_EXTkey);
		} else {
				// People with no permission can do nothing
			$this->newPagesInto=0;
			$this->newContentInto=0;
			$this->newPagesAfter=0;
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $this->pageinfo[\'uid\']='.$this->pageinfo['uid'], GRAYTREE_EXTkey);
		}	

			// If there was a page - or if the user is admin (admins has access to the root) we proceed:
		if ($this->pageinfo['uid'] || $BE_USER->isAdmin())	{
				// Acquiring TSconfig for this module/current page:
			$this->web_list_modTSconfig = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],'mod.web_list');
			$this->allowedNewTables = t3lib_div::trimExplode(',',$this->web_list_modTSconfig['properties']['allowedNewTables'],1);

				// Acquiring TSconfig for this module/parent page:
			$this->web_list_modTSconfig_pid = t3lib_BEfunc::getModTSconfig($this->pageinfo['pid'],'mod.web_list');
			$this->allowedNewTables_pid = t3lib_div::trimExplode(',',$this->web_list_modTSconfig_pid['properties']['allowedNewTables'],1);

			$access = TRUE;
		}


$access = TRUE;

		// 
		// Main
		// 
		if ($access)	{
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $access='.$access, GRAYTREE_EXTkey);
			
			//
			// Output page header
			//
			$this->actionTarget = $this->actionTarget ? $this->actionTarget : t3lib_div::linkThisScript();
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $this->actionTarget='.$this->actionTarget, GRAYTREE_EXTkey);
			$this->doc->form='<form action="'.$this->actionTarget.'" method="POST" name="editform" enctype="'.$TYPO3_CONF_VARS['SYS']['form_enctype'].'">';

				// JavaScript
			$this->doc->JScodeArray['jumpToUrl'] = '
				var script_ended = 0;
				var changed = 0;
				
				function jumpToUrl(URL)	{
					document.location = URL;
				}
				
				function jumpBack()	{
					document.location = "'.$this->returnUrl.'";
				}
				';
				
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main $this->pageinfo[\'uid\']='.$this->pageinfo['uid'], GRAYTREE_EXTkey);
			$this->doc->postCode.= $this->doc->wrapScriptTags('
				script_ended = 1;');

			$this->extObjHeader();


				// Draw the header.
			$this->content.= $this->doc->startPage($this->pageTitle);
			$this->content.= $this->doc->header($this->pageTitle);
			$this->content.= $this->doc->spacer(5);

			//
			// Call submodule function  
			//
			
			$this->extObjContent();

			
			$this->content.= $this->doc->spacer(10);


		} else {
			if (TYPO3_DLOG && GRAYTREE_CMD_DLOG) t3lib_div::devLog('TYPO3 tx_graytree_mod_cmd::main ***KEINE RECHTE*** $access='.$access, GRAYTREE_EXTkey);
			
					// If no access
			$this->content.= $this->doc->startPage($LANG->getLL('title'));
			$this->content.= $this->doc->header($LANG->getLL('title'));
			$this->content.= $this->doc->spacer(5);
#TODO
			$this->content.= $this->doc->spacer(10);
		}
			
		
	}

	/**
	 * Prints out the module HTML
	 * 
	 * @return	string		HTML
	 */
	function printContent()	{
		global $SOBE;

		$this->content.= $this->doc->middle();
		$this->content.= $this->doc->endPage();
		$this->content=$this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

	/**
	 * Returns a message that the passed command was wrong
	 * 
	 * @return	string 	HTML content
	 */
	function wrongCommandMessage()	{
		global $SOBE, $LANG;
		
		$content = $SOBE->doc->section('',$SOBE->doc->icons(2).' '.$LANG->getLL('tx_graytree_cmd_nothing.message'));
		if ($SOBE->CMD) {
			$content.= $SOBE->doc->section('Command:',htmlspecialchars($SOBE->CMD), 0,0);
		}
		return $content;
	}

	/**
	 * Send redirect header
	 * 
	 * @return	void
	 */
	function redirect()	{
		if ($this->redirect) {
			Header('Location: '.t3lib_div::locationHeaderUrl($this->redirect));
			exit;
		}	
	}
	
	
// ----------------------------------------------------------	
	
	
	/**
	 * Button: go back
	 * 
	 * @param	array		Params array. Used to build a url with t3lib_div::linkThisScript()
	 * @param	string		Full url which should be the link href
	 * @return	string		Button HTML code
	 */
	function btn_back($params=array(), $absUrl='')	{
		global $LANG, $BACK_PATH;

		if ($absUrl) {
			$url = $absUrl;
		} else {
			$url = t3lib_div::linkThisScript($params);
		}

		$content = '<a href="'.htmlspecialchars($url).'" class="typo3-goBack">'.
					'<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/goback.gif"','width="14" height="14"').' class="absmiddle" alt="" /> Go back'.
					'</a>';		
		
		return $content;
	}		
	

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_cmd/index.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/mod_cmd/index.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('tx_graytree_mod_cmd');
$SOBE->init();

// Include files?
reset($SOBE->include_once);
while(list(,$INC_FILE)=each($SOBE->include_once))	{include_once($INC_FILE);}
$SOBE->checkExtObj();	// Checking for first level external objects

// Repeat Include files! - if any files has been added by second-level extensions
reset($SOBE->include_once);	
while(list(,$INC_FILE)=each($SOBE->include_once))	{include_once($INC_FILE);}
$SOBE->checkSubExtObj();	// Checking second level external objects

$SOBE->main();
$SOBE->printContent();
?>