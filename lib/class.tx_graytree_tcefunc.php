<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 René Fritz <r.fritz@colorcube.de>
*  (c) 2005-2006 Franz Holzinger <kontakt@fholzinger.com>
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
 * Provide TCE and TCEforms functions for usage in own extension.
 *
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id: class.tx_graytree_tcefunc.php 274 2006-07-14 05:27:28Z franz $
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *
 */


class tx_graytree_tceFunc {

	/**********************************************************
	 *
	 * Rendering of TCEform fields for common usage
	 *
	 ************************************************************/

	/**
	 * Generation of TCEform elements of the type "select"
	 * This will render a selector box element, or possibly a special construction with two selector boxes. That depends on configuration.
	 *
	 * @param	array		The record data array where the value(s) for the field can be found
	 *						['table'] ... The table name of the record
	 * 						['field'] ... The field name which this element is supposed to edit
	 * @param	array		An array with additional configuration options.
	 * @return	string		The HTML code for the TCEform field
	 */
	function getSingleField_selectTree($PA, &$pObj)	{
		global $BACK_PATH;
		
		$table = $PA['table'];
		$field = $PA['field'];
		$row = $PA['row'];
		$this->pObj = &$PA['pObj'];
		
			// Field configuration from TCA:
		$config = $PA['fieldConf']['config'];

		// it seems TCE has a bug and do not work correctly with '1'
		$config['maxitems'] = ($config['maxitems'] == 2) ? 1 : $config['maxitems'];

		// Getting the selector box items from the system
		$selItems = $this->pObj->addSelectOptionsToItemArray($this->pObj->initItemArray($PA['fieldConf']),$PA['fieldConf'],$this->pObj->setTSconfig($table,$row),$field);
		$selItems = $this->pObj->addItems($selItems,$PA['fieldTSConfig']['addItems.']);
		if ($config['itemsProcFunc']) $selItems = $this->pObj->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);

		// Possibly remove some items:
		$removeItems=t3lib_div::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);
		foreach($selItems as $tk => $p)	{
			if (in_array($p[1],$removeItems))	{
				unset($selItems[$tk]);
			} else if (isset($PA['fieldTSConfig']['altLabels.'][$p[1]])) {
				$selItems[$tk][0]=$this->pObj->sL($PA['fieldTSConfig']['altLabels.'][$p[1]]);
			}

				// Removing doktypes with no access:
			if ($table.'.'.$field == 'pages.doktype')	{
				if (!($GLOBALS['BE_USER']->isAdmin() || t3lib_div::inList($GLOBALS['BE_USER']->groupData['pagetypes_select'],$p[1])))	{
					unset($selItems[$tk]);
				}
			}
		}

		// Creating the label for the "No Matching Value" entry.
		$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->pObj->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$this->pObj->getLL('l_noMatchingValue').' ]';
		$nMV_label = @sprintf($nMV_label, $PA['itemFormElValue']);

		// Prepare some values:
		$maxitems = intval($config['maxitems']);
		$minitems = intval($config['minitems']);
		$size = intval($config['size']);

		// If a SINGLE selector box...
		if ($maxitems<=1 AND !$config['treeView'])	{

		} else {
			$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'" />';

				// Set max and min items:
			$maxitems = t3lib_div::intInRange($config['maxitems'],0);
			if (!$maxitems)	$maxitems=100000;
			$minitems = t3lib_div::intInRange($config['minitems'],0);

				// Register the required number of elements:
			$this->pObj->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);

			if($config['treeView'] && $config['treeClass']) {
				global $TCA, $LANG;

				if(is_object($treeObj = &t3lib_div::getUserObj($config['treeClass'],'user_',false)))      {
				} else {
					if (TYPO3_DLOG) {
						t3lib_div::devLog('error in tx_graytree_tceFunc::getSingleField_selectTree', GRAYTREE_EXTkey);
						t3lib_div::devLog('Object to class '.$config['treeClass'].' cannot be created.', GRAYTREE_EXTkey);
					}
					return;
				}
				if ($config['isRoot'])	{
					$limitCatArray = array($row['uid']); // no subcategories shall be shown to avoid loops
				}

				$treeObj->init($table, $row, $limitCatArray, $config['leafInfoArray']);  
				$treeObj->backPath = $this->pObj->backPath;
				//$treeObj->expandAll=1;
				$treeObj->expandFirst=1;
				$treeObj->ext_IconMode = true; // no context menu on icons
				$treeObj->title = $LANG->sL($TCA[$config['foreign_table']]['ctrl']['title']);
				$cOnClick = 'if (confirm('.$GLOBALS['LANG']->JScharCode($pObj->getLL('m_onChangeAlert')).') && TBE_EDITOR_checkSubmit(-1)){ ';
				$treeObj->treeView->pmIconTagAttributes['onclick'] = array();
				$treeObj->treeView->pmIconTagAttributes['onclick'][0] = htmlspecialchars($cOnClick);
				$treeObj->treeView->pmIconTagAttributes['onclick'][1] = htmlspecialchars('TBE_EDITOR_submitForm();};'); // htmlspecialchars('};'); //
				$treeObj->treeView->TCEforms_itemFormElName = $PA['itemFormElName'];
				//$treeObj->setScript($BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))); // Current script name

				if ($table == $config['foreign_table']) {
					$treeObj->treeView->TCEforms_nonSelectableItemsArray[] = $row['uid'];
				}
				$item.= '<input type="hidden" name="'.$treeObj->treeView->treeName.'_PM" value="'.($config['plusminus']?1:0).'" />';

				$treeContent = $treeObj->printTree();
				$treeItemC = count($treeObj->ids);
				#if ($this->pObj->docLarge)	$cols = round($cols*$this->pObj->form_largeComp);
				#$width = ceil($cols*$this->pObj->form_rowsToStylewidth);
				$width=240;
				$config['autoSizeMax'] = t3lib_div::intInRange($config['autoSizeMax'],0);
				$height = $config['autoSizeMax'] ? t3lib_div::intInRange($treeItemC+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
					// hardcoded: 12 is the height of the font
				$height=$height*13;
				$divStyle = 'position:relative; left:0px; top:0px; height:'.$height.'px; width:'.$width.'px;border:solid 1px;overflow:auto;background:#fff;';
				$thumbnails='<div  name="'.$PA['itemFormElName'].'_selTree" style="'.htmlspecialchars($divStyle).'">';
				$thumbnails.=$treeContent;
				$thumbnails.='</div>';

			} else {
				$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); '.implode('',$PA['fieldChangeFunc']);

					// Put together the select form with selected elements:
				$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"';
				$size = $config['autoSizeMax'] ? t3lib_div::intInRange(count($selItems)+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
				$thumbnails = '<select style="width:150 px;" name="'.$PA['itemFormElName'].'_sel"'.$this->pObj->insertDefStyle('select').($size?' size="'.$size.'"':'').' onchange="'.htmlspecialchars($sOnChange).'"'.$PA['onFocus'].$selector_itemListStyle.'>';
				#$thumbnails = '<select                       name="'.$PA['itemFormElName'].'_sel"'.$this->pObj->insertDefStyle('select').($size?' size="'.$size.'"':'').' onchange="'.htmlspecialchars($sOnChange).'"'.$PA['onFocus'].$selector_itemListStyle.'>';
				foreach($selItems as $p)	{
					$thumbnails.= '<option value="'.htmlspecialchars($p[1]).'">'.htmlspecialchars($p[0]).'</option>';
				}
				$thumbnails.= '</select>';

			}
			
			// Perform modification of the selected items array:
			$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);
			
			foreach($itemArray as $tk => $tv) {
				
				$tvP = explode('|',$tv,2);
				$evalValue = rawurldecode($tvP[0]);
				/**
				 * Converts from UTF-8 Entries, to prevent double UTF-8 encoding
				 * @since 10.01.2006
				 * @author ingo schmitt <is@marketing-factory.de>
				 * @TODO Check is a hack
				 */
				$evalValue = rawurldecode($tvP[0]);
				if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']=='utf-8') {
					$tvP[1]=rawurlencode(utf8_decode(rawurldecode($tvP[1])));
					
				}
				
			
				if (in_array($tvP[0],$removeItems) && !$PA['fieldTSConfig']['disableNoMatchingValueElement'])	{
					$tvP[1] = rawurlencode($nMV_label);
				} elseif (isset($PA['fieldTSConfig']['altLabels.'][$evalValue])) {
					$tvP[1] = rawurlencode($pObj->sL($PA['fieldTSConfig']['altLabels.'][$evalValue]));
				} else {
					$tvP[1] = rawurlencode($pObj->sL(rawurldecode($tvP[1])));
				}
				$itemArray[$tk]=implode('|',$tvP);
			}
			
			$params=array(
				'size' => $size,
				'autoSizeMax' => t3lib_div::intInRange($config['autoSizeMax'],0),
				#'style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"',
				'style' => ' style="width:140px;"',
				'dontShowMoveIcons' => ($maxitems<=1),
				'maxitems' => $maxitems,
				'info' => '',
				'headers' => array(
					'selector' => $this->pObj->getLL('l_selected').':<br />',
					'items' => $this->pObj->getLL('l_items').':<br />'
				),
				'noBrowser' => 1,
				'thumbnails' => $thumbnails
			);
		
			$item.= $this->pObj->dbFileIcons($PA['itemFormElName'],'','',$itemArray,'',$params,$PA['onFocus']);
		}

			// Wizards:
		$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
		$item = $this->pObj->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'],$specConf);

		return $item;
	}






	/************************************************************
	 *
	 * Form element helper functions
	 *
	 ************************************************************/

	/**
	 * Prints the selector box form-field for the db/file/select elements (multiple)
	 *
	 * @param	string		Form element name
	 * @param	string		Mode "db", "file" (internal_type for the "group" type) OR blank (then for the "select" type)
	 * @param	string		Commalist of "allowed"
	 * @param	array		The array of items. For "select" and "group"/"file" this is just a set of value. For "db" its an array of arrays with table/uid pairs.
	 * @param	string		Alternative selector box.
	 * @param	array		An array of additional parameters, eg: "size", "info", "headers" (array with "selector" and "items"), "noBrowser", "thumbnails"
	 * @param	string		On focus attribute string
	 * @param	[type]		$user_el_param: ...
	 * @return	string		The form fields for the selection.
	 */
	function dbFileIcons($fName,$mode,$allowed,$itemArray,$selector='',$params=array(),$onFocus='',$user_el_param='')	{

			// Sets a flag which means some JavaScript is included on the page to support this element.
		$this->pObj->printNeededJS['dbFileIcons']=1;

			// INIT
		$uidList=array();
		$opt=array();
		$itemArrayC=0;

			// Creating <option> elements:
		if (is_array($itemArray))	{
			$itemArrayC=count($itemArray);
			reset($itemArray);
			switch($mode)	{
				case 'db':
					while(list(,$pp)=each($itemArray))	{
						if($pp['title']) {
							$pTitle = $pp['title'];
						} else {
							$pRec = t3lib_BEfunc::getRecord($pp['table'],$pp['id']);
							$pTitle = is_array($pRec) ? $pRec[$GLOBALS['TCA'][$pp['table']]['ctrl']['label']] : NULL;
						}
						if ($pTitle)	{
							$pTitle = t3lib_div::fixed_lgd_cs($this->pObj->noTitle($pTitle),$this->pObj->titleLen);
							$pUid = $pp['table'].'_'.$pp['id'];
							$uidList[]=$pUid;
							$opt[]='<option value="'.htmlspecialchars($pUid).'">'.htmlspecialchars($pTitle).'</option>';
						}
					}
				break;
				default:
					while(list(,$pp)=each($itemArray))	{
						$pParts = explode('|',$pp);
						$uidList[]=$pUid=$pParts[0];
						$pTitle = $pParts[1];
						$opt[]='<option value="'.htmlspecialchars(rawurldecode($pUid)).'">'.htmlspecialchars(rawurldecode($pTitle)).'</option>';
					}
				break;
			}
		}

			// Create selector box of the options
		if (!$selector)	{
			$sSize = $params['autoSizeMax'] ? t3lib_div::intInRange($itemArrayC+1,t3lib_div::intInRange($params['size'],1),$params['autoSizeMax']) : $params['size'];
			$selector = '<select size="'.$sSize.'"'.$this->pObj->insertDefStyle('group').' multiple="multiple" name="'.$fName.'_list" '.$onFocus.$params['style'].'>'.implode('',$opt).'</select>';
		}


		$icons = array(
			'L' => array(),
			'R' => array(),
		);
		if (!$params['noBrowser'])	{
			$aOnClick='setFormValueOpenBrowser(\''.$mode.'\',\''.($fName.'|||'.$allowed.'|'.$user_el_param.'|').'\'); return false;';
			$icons['R'][]='<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->pObj->backPath,'gfx/insert3.gif','width="14" height="14"').' border="0" '.t3lib_BEfunc::titleAltAttrib($this->pObj->getLL('l_browse_'.($mode=='file'?'file':'db'))).' />'.
					'</a>';
		}
		if (!$params['dontShowMoveIcons'])	{
			$icons['L'][]='<a href="#" onclick="setFormValueManipulate(\''.$fName.'\',\'Up\'); return false;">'.
					'<img'.t3lib_iconWorks::skinImg($this->pObj->backPath,'gfx/group_totop.gif','width="14" height="14"').' border="0" '.t3lib_BEfunc::titleAltAttrib($this->pObj->getLL('l_move_to_top')).' />'.
					'</a>';
		}

		$clipElements = $this->pObj->getClipboardElements($allowed,$mode);
		if (count($clipElements))	{
			$aOnClick = '';
#			$counter = 0;
			foreach($clipElements as $elValue)	{
				if ($mode=='file')	{
					$itemTitle = 'unescape(\''.rawurlencode(basename($elValue)).'\')';
				} else {	// 'db' mode assumed
					list($itemTable,$itemUid) = explode('|', $elValue);
					$itemTitle = $GLOBALS['LANG']->JScharCode(t3lib_BEfunc::getRecordTitle($itemTable, t3lib_BEfunc::getRecord($itemTable,$itemUid)));
					$elValue = $itemTable.'_'.$itemUid;
				}
				$aOnClick.= 'setFormValueFromBrowseWin(\''.$fName.'\',unescape(\''.rawurlencode(str_replace('%20',' ',$elValue)).'\'),'.$itemTitle.');';

#				$counter++;
#				if ($params['maxitems'] && $counter >= $params['maxitems'])	{	break;	}	// Makes sure that no more than the max items are inserted... for convenience.
			}
			$aOnClick.= 'return false;';
			$icons['R'][]='<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->pObj->backPath,'gfx/insert5.png','width="14" height="14"').' border="0" '.t3lib_BEfunc::titleAltAttrib(sprintf($this->pObj->getLL('l_clipInsert_'.($mode=='file'?'file':'db')),count($clipElements))).' />'.
					'</a>';
		}

		$icons['L'][]='<a href="#" onclick="setFormValueManipulate(\''.$fName.'\',\'Remove\'); return false;">'.
				'<img'.t3lib_iconWorks::skinImg($this->pObj->backPath,'gfx/group_clear.gif','width="14" height="14"').' border="0" '.t3lib_BEfunc::titleAltAttrib($this->pObj->getLL('l_remove_selected')).' />'.
				'</a>';

		$str='<table border="0" cellpadding="0" cellspacing="0" width="1">
			'.($params['headers']?'
				<tr>
					<td>'.$this->pObj->wrapLabels($params['headers']['selector']).'</td>
					<td></td>
					<td></td>
					<td>'.$this->pObj->wrapLabels($params['headers']['items']).'</td>
				</tr>':'').
			'
			<tr>
				<td valign="top">'.
					$selector.'<br />'.
					$this->pObj->wrapLabels($params['info']).
				'</td>
				<td valign="top">'.
					implode('<br />',$icons['L']).'</td>
				<td valign="top">'.
					implode('<br />',$icons['R']).'</td>
				<td><img src="'.$this->pObj->backPath.'clear.gif" width="5" height="1" alt="" /></td>
				<td valign="top">'.
					$this->pObj->wrapLabels($params['thumbnails']).
				'</td>
			</tr>
		</table>';

			// Creating the hidden field which contains the actual value as a comma list.
		$str.='<input type="hidden" name="'.$fName.'" value="'.htmlspecialchars(implode(',',$uidList)).'" />';

		return $str;
	}








	/**********************************************************
	 *
	 * Rendering of TCEform fields for private usage for tables
	 *
	 ************************************************************/




//	/**
//	 * [Describe function...]
//	 *
//	 * @param	[type]		$PA: ...
//	 * @param	[type]		$fobj: ...
//	 * @return	[type]		...
//	 */
//	function thumb ($PA, &$fobj) {
//
//		$config = $PA['fieldConf']['config'];
//		$row = $PA['row'];
//
//		$itemOut='';
//
//$filePath = preg_replace('/\/$/','',$row['file_path']);
//
//		if ($row['media_type'] == 2 OR $row['media_type'] == 7
//			 OR $row['file_type'] == 'pdf'
//			 OR $row['file_type'] == 'ps'
//			 OR $row['file_type'] == 'eps'
//			 ) {
//			if (!$PA['itemFormElValue'] && $row['file_name']) {
//				$rowCopy=array();
//				$rowCopy['file_name'] = $row['file_name'];
//				$itemOut = '<div style="margin:4px;margin-right:10px;padding:8px;background-color:#fff;border:solid #888 1px;">'.t3lib_BEfunc::thumbCode($rowCopy,$PA['table'],'file_name',$fobj->backPath,'thumbs.php',$filePath,0,' align="middle" style="border:solid 1px #ccc;"',160).'</div>';
//			}
//#TODO ???
//			if ($itemValue = $PA['itemFormElValue']) {
//
//				$rowCopy = array();
//				$rowCopy[$config['field']] = $itemValue;
//				$itemOut = '<div style="margin:4px;padding:2px;">'.t3lib_BEfunc::thumbCode($rowCopy,$PA['table'],$PA['field'],$fobj->backPath,'thumbs.php',$filePath,0,' align=middle').$itemValue.'</div>';
//			} elseif (!$itemOut) {
//				$itemOut = 'no thumbnail';
//			}
//		}
//
//
//
//		$out = '
//								</table>
//							</td>
//							<td><img src="clear.gif" width="10" height="1" alt="" /></td>
//							<td width="1%" valign="top" align="center">'.$itemOut.'</td>
//						</tr>
//					</table>
//				</td>
//			</tr>
//			<tr>
//				<td colspan="2"><img src="clear.gif" width="1" height="5" alt="" /></td>
//			</tr>';
//
//		return $out;
//	}


//	/**
//	 * [Describe function...]
//	 *
//	 * @param	[type]		$PA: ...
//	 * @param	[type]		$fobj: ...
//	 * @return	[type]		...
//	 */
//	function file_mime_type ($PA, &$fobj) {
//		$config = $PA['fieldConf']['config'];
//		if($config['type']=='none') {
//			$PA['itemFormElValue'] = $PA['row']['file_mime_type'].'/'.$PA['row']['file_mime_subtype'];
//			$out = $fobj->getSingleField_typeNone($PA['table'], $PA['field'], $PA['row'], $PA);
//		} else {
//			$out = $fobj->getSingleField_SW($PA['table'], $PA['field'], $PA['row'], $PA);
//		}
//
//		return $out;
//	}
//

//	/**
//	 * [Describe function...]
//	 *
//	 * @param	[type]		$PA: ...
//	 * @param	[type]		$fobj: ...
//	 * @return	[type]		...
//	 */
//	function fileUsage ($PA, &$fobj) {
//		global $TCA;
//
//		$config = $PA['fieldConf']['config'];
//		$itemOut = '';
//
//		$rows = $this->getRecordsByWhere('tt_content',
//				"image REGEXP BINARY '[^, ]*".str_replace('.',"(_[0-9][0-9])?\.",$GLOBALS['TYPO3_DB']->quoteStr($PA['row']['file_name'],'tt_content'))."[^, ]*'",'uid,pid,image');
//
//		$config['rows']=0;
//		if (is_array($rows)) {
//			reset($rows);
//			while(list(,$row)=each($rows)) {
//				$pageRec = t3lib_BEfunc::getRecord ('pages',$row['pid'],"uid,pid,".$TCA['pages']['ctrl']['label']);
//				$theIcon = '<img src="'.$fobj->backPath.t3lib_iconWorks::getIcon('pages',$pageRec).'" width="18" height="16" align="top" border="0" title="id='.$row[pid].'" alt="" />';
//				$itemOut.= '<a href="#" onclick="'.htmlspecialchars('loadEditId('.$row['pid'].');').'">'.$theIcon.' '.t3lib_BEfunc::getRecordTitle('pages',$pageRec,1).', tt_content:'.$row['uid'].'</a><br />';
//				$config['rows']++;
//			}
//		} else {
//			$itemOut.= 'Wird nicht verwendet.';
//		}
//		if($config['rows']) {
//
//#TODO
//			$fobj->extJSCODE.= "
//			function loadEditId(id)	{
//				if (top.goToModule)	{
//					top.theMenu.recentuid=id;
//					if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
//						top.content.nav_frame.refresh_nav();
//					}
//					top.goToModule('web_layout');
//				} else {
//					top.theMenu.recentuid = id;
//					top.modPane_web.click('modPane_web_layout');
//				}
//			}
//		";
//		}
//		$config['pass_content'] = true;
//		$config['fixedRows'] = true;
//		$config['rows'] = min(5,$config['rows'])+1;
//
//		$out = $this->intoTemplate($fobj,$fieldTemplate,$itemOut,$PA,$fobj->sL($PA['label'])); #TODO: label??
//
//		return $out;
//	}

	/************************************************************
	 *
	 * Form element helper functions
	 *
	 ************************************************************/

//	/**
//	 * [Describe function...]
//	 *
//	 * @param	[type]		$$fobj: ...
//	 * @param	[type]		$fieldTemplate: ...
//	 * @param	[type]		$content: ...
//	 * @param	[type]		$PA: ...
//	 * @param	[type]		$label: ...
//	 * @return	[type]		...
//	 */
//	function intoTemplate (&$fobj, &$fieldTemplate, &$content, $PA, $label='') {
//		return $fobj->intoTemplate( array(
//					'NAME'=>($label ? $label: $PA['label']),
//					'ID'=>$PA['row']['uid'],
//					'FIELD'=>$PA['field'],
//					'TABLE'=>$PA['table'],
//					'ITEM'=>$content,
//					'HELP_ICON' => $fobj->helpTextIcon($PA['table'],$PA['field'],1)
//				),
//				$fieldTemplate);
//	}

//	/**
//	 * [Describe function...]
//	 *
//	 * @param	[type]		$theTable: ...
//	 * @param	[type]		$where: ...
//	 * @param	[type]		$fieldList: ...
//	 * @param	[type]		$endClause: ...
//	 * @return	[type]		...
//	 */
//	function getRecordsByWhere($theTable,$where,$fieldList="*",$endClause='')	{
//		global $TCA;
//		if (is_array($TCA[$theTable])) {
//			$del=t3lib_BEfunc::deleteClause($theTable);
//			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fieldList, $theTable, $where.$del.' '.$endClause);
//			$rows=array();
//			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
//				$rows[] = $row;
//			}
//			if (count($rows))	return $rows;
//		}
//	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_tcefunc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_tcefunc.php']);
}

?>