<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj <kasperYYYY@typo3.com>
*  (c) 2005-2007 Franz Holzinger <kontakt@fholzinger.com>
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
 * Base class for creating a browsable array/page/folder tree in HTML
 *
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage t3lib
 * @see t3lib_browsetree, t3lib_pagetree
 * $Id$
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *
 *
 * TOTAL FUNCTIONS: 31
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



require_once (PATH_t3lib.'class.t3lib_iconworks.php');
require_once (PATH_t3lib.'class.t3lib_befunc.php');
require_once (PATH_t3lib.'class.t3lib_div.php');

#define('GRAYTREE_VIEW_DLOG', '1');


class tx_graytree_View {

		// EXTERNAL, static:
	var $expandFirst=0;		// If set, the first element in the tree is always expanded.
	var $expandAll=0;		// If set, then ALL items will be expanded, regardless of stored settings.
	var $thisScript='';		// Holds the current script to reload to.
	var $titleAttrib = 'title';		// Which HTML attribute to use: alt/title. See init().
	var $addSelfId = 0;				// If set, the id of the mounts will be added to the internal ids array
	var $title='no title';			// Used if the tree is made of records (not folders for ex.)
	var $rootIconName = '_icon_website.gif';  // Icon for the root of the tree
	var $rootPid; // PID of the root record. All created records in the tree will use this page id
	var $startRow = array(); // row of root leaf where it has started

	// variables only needed for display in TCEforms:
	var $TCEforms_itemFormElName='';
	var $TCEforms_nonSelectableItemsArray=array();
	var $limitCatArray = array(); // categories from which no subcategories should be displayed

	/**
	 * Needs to be initialized with $GLOBALS['BE_USER']
	 * Done by default in init()
	 */
	var $BE_USER='';

	/**
	 * Needs to be initialized with e.g. $GLOBALS['WEBMOUNTS']
	 * Default setting in init() is 0 => 0
	 * The keys are mount-ids (can be anything basically) and the values are the ID of the root element (COULD be zero or anything else. For pages that would be the uid of the page, zero for the pagetree root.)
	 */
	var $MOUNTS='';

	/**
	 * Data model to get the tree data from.
	 * Leave blank if data comes from an array.
	 */
	var $graytree_db='';

	/**
	 * Unique name for the tree.
	 * Used as key for storing the tree into the BE users settings.
	 * Used as key to pass parameters in links.
	 * MUST NOT contain underscore chars.
	 * etc.
	 */
	var $treeName = '';

	/**
	 * A prefix for table cell id's which will be wrapped around an item.
	 * Can be used for highlighting by JavaScript.
	 * Needs to be unique if multiple trees are on one HTML page.
	 * @see printTree()
	 */
	var $domIdPrefix = 'row';

	/**
	 * Icon file name for item icons.
	 */
	var $iconName = 'default.gif';

	/**
	 * Icon TAG attributes for plus/minus symbol
	 */
	var $pmIconTagAttributes = array();

	/**
	 * If TRUE, HTML code is also accumulated in ->tree array during rendering of the tree.
	 */
	var $makeHTML=1;

	/**
	 * If TRUE, records as selected will be stored internally in the ->recs array
	 */
	var $setRecs = 0;

	/**
	 * Sets the associative array key which identifies a new sublevel if arrays are used for trees.
	 * This value has formerly been "subLevel" and "--sublevel--"
	 */
	var $subLevelID = '_SUB_LEVEL';

	/**
	 * The array of tree leafs out of which the tree is constructed
	 */
	var $leafArray = array();


		// *********
		// Internal
		// *********
		// For record trees:
	var $ids = Array();				// one-dim array of the uid's selected.
	var $ids_hierarchy = array();	// The hierarchy of element uids
	var $buffer_idH = array();		// Temporary, internal array

		// For FOLDER trees:
	var $specUIDmap=array();		// Special UIDs for folders (integer-hashes of paths)

		// For arrays:
	var $data = FALSE;				// Holds the input data array
	var $dataLookup = FALSE;		// Holds an index with references to the data array.

		// For both types
	var $stored = array();			// Holds (session stored) information about which items in the tree are unfolded and which are not.
	var $bank=0;					// Points to the current mountpoint key
	var $recs = array();			// Accumulates the displayed records.

	var $pmVar;						// 'PM' variable
	var $watchDogMax = 5000;			// define a watchdog to avoid loops

	/**
	 * Initialize the tree class. Needs to be overwritten
	 * Will set ->fieldsArray, ->backPath and ->clause
	 * The $leafInfoArray must be given as a parameter. This function creates all the objects of the classes from this array.
	 * Each element of this array consists of an array ('data', 'view'):
	 * 'data' must be derived from tx_graytree_leafData and
	 * 'view' must be derived from tx_graytree_leafView.
	 *
	 * @param   graytree_db 	Treelib data model
	 * @param	leafInfoArray	Array of leaf data and view classes
	 * @param 	table name for following data
	 * @param	startRow		the row where the tree has been started in TCE
	 * @param	limitCatArray	Array of categories from which no subcategories shall be shown
	 * @return	void
	 */
	function init(&$graytree_db, $leafInfoArray, $table='', $startRow=array(), $limitCatArray=array())	{

		$this->graytree_db = &$graytree_db;
		$this->startRow = $startRow;
		$this->limitCatArray[0] = $limitCatArray;

		$this->BE_USER = &$GLOBALS['BE_USER'];	// Setting BE_USER by default reference
		$this->titleAttrib = 'title';	// Setting title attribute to use.
		$this->backPath = $GLOBALS['BACK_PATH'];	// Setting backpath.

		if (!is_array($this->MOUNTS))	{
			$this->MOUNTS = array(0 => 0); // dummy
		}

		$this->setTreeName();
		$this->setRootPid();

		reset($leafInfoArray);
		foreach ($leafInfoArray as $key => $leaf) {
			if (TYPO3_DLOG && GRAYTREE_VIEW_DLOG) t3lib_div::devLog('tx_graytree_View::init eingefügt wird '.$key . ' - '.$leaf, GRAYTREE_EXTkey);

			$view = &t3lib_div::makeInstance($leaf['view']);
			$dum = &$graytree_db->getLeafData($key);
			$view->init($dum, $this);
			$this->leafArray[] = &$view;
		}

		// only DLOG output
		if (TYPO3_DLOG && GRAYTREE_VIEW_DLOG) {

			reset ($this->leafArray);
			t3lib_div::devLog('leafArray: ', GRAYTREE_EXTkey);
			foreach ($this->leafArray as $key => $leaf) {
				t3lib_div::devLog('tx_graytree_View::init $this->leafArray['.$key.'] = '.$leaf->title , GRAYTREE_EXTkey);
			}
		}

	}


	/**
	 * Sets the tree name which is used to identify the tree
	 * Used for JavaScript and other things
	 *
	 * @param	string		Default is the table name. Underscores are stripped.
	 * @return	void
	 */
	function setTreeName($treeName='') {
		$this->treeName = $treeName ? $treeName : $this->treeName;
		$dum = &$this->graytree_db->getLeafData(0);
		$this->treeName = $this->treeName ? $this->treeName : $dum->table;
		$this->treeName = str_replace('_','',$this->treeName);
	}


	/**
	 * Sets the page id for the root object of the tree
	 *
	 * @param	integer		page id if already known
	 * @return	void
	 */
	function setRootPid($pid='0') {
		$this->rootPid = ($pid ? $pid: $this->rootPid);
	}


	/**
	 * Resets the tree, recs, ids, and ids_hierarchy internal variables. Use it if you need it.
	 *
	 * @return	void
	 */
	function reset()	{
		$this->recs = array();
		$this->ids = array();
		$this->ids_hierarchy = array();
	}


	/*******************************************
	 *
	 * output
	 *
	 *******************************************/

	/**
	 * Will create and return the HTML code for a browsable tree
	 * Is based on the mounts found in the internal array ->MOUNTS (set in the constructor)
	 *
	 * @return	string		HTML code for the browsable tree
	 */
	function getBrowsableTree()	{

			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$out = '';
		$this->initializePositionSaving();

			// Init done:
		$titleLen=intval($this->BE_USER->uc['titleLen']);
		$treeArr=array();

			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)	{

				// Set first:
			$this->bank=$idx;
				$depthD='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/blank.gif','width="18" height="16"').' alt="" />';
				$this->getTree($uid,999,$depthD);

					// Add tree:
				$treeArr=array_merge($treeArr,$this->tree);
		}

		$out.=$this->printTree($treeArr);
		return $out;
	}


//	/**
//	 * push the idlist with ids separated by comma in reverse order on the stack
//	 *
//	 * @param	array		the stack array
//	 * @param	string		array of uids
//	 * @param	integer		depth of the uids in the tree
//	 * @return	void
//	 */
//	function pushIdlist (&$stack, &$uidArray, $depth, $parent_id) {
//		$uidLast = end($uidArray);
//
//		$uidArray = array_reverse($uidArray);
//
//		foreach ($uidArray as $k => $v) {
//			$isLast = (($v == $uidLast) || count($uidArray) == 1);
//			$v['parent_id'] = $parent_id;
//			array_push ($stack, array('row' => $v, 'depth' => $depth, 'last' => $isLast)); 		// store the uid and the depth on the stack
//		}
//	}


	/********************************
	 *
	 * tree data buidling
	 *
	 ********************************/



	function processTreeData(&$treeDataArray, $kParent, &$kStack, &$stack)	{

		$treeData = &$treeDataArray[$kParent];
		$childKey = $treeData['childs']['k'];
		$childRow = $treeData['childs'][$childKey];
		$actChildRowKey = $treeData['childs'][$childKey]['k'];
		$treeData['childs'][$childKey]['k'] = $actChildRowKey + 1;
		$isLastChild = TRUE;
		if ($treeData['childs'][$childKey]['k'] < count ($treeData['childs'][$childKey]) - 1)	{
			$isLastChild = FALSE;
		} else {
			$treeData['childs']['k'] = $childKey + 1;
			if ($treeData['childs']['k'] < count ($treeData['childs']) - 1)	{ //	'k' is first element
				$isLastChild = FALSE;
			}
		}

		if (!$isLastChild)	{ //	deal with the rest later
			$kStack++;
			$stack[$kStack] = $kParent;
		}

		$k = $treeData['childs'][$childKey][$actChildRowKey];

		return $k;
	}



	/**
	 * Fetches the data for the tree
	 *
	 * @param	integer		item id for which to select subitems (parent id)
	 * @param	integer		Max depth (recursivity limit)
	 * @param	string		HTML-code prefix for recursive calls.
	 * @param	boolean		fetch all data
	 * @param   array		SQL WHERE conditions for the category tables
	 * @return	integer		The count of items on the level
	 */
	function &getTree($startuid, $maxDepth=999, $depthData='',$blankLineCode='',$gettreedata='',$addWhereArray = '',$withoutLeaf='')	{
		if (TYPO3_DLOG && GRAYTREE_VIEW_DLOG) 	t3lib_div::devLog('tx_graytree_View::getTree $startuid = '.$startuid, GRAYTREE_EXTkey);

		//$this->expandAll = TRUE;

if(!$gettreedata){
		$rootRec='';
		$rootIcon='';
		$isNextOpen = $this->expandNext(0, $startuid)  || $this->expandFirst;

		// Set PM icon for root of mount:
		$cmd=$this->bank.'_0_'.($isNextOpen?'0_':'1_').$startuid.'_'.$this->treeName;	// this belongs to table with index 0
		$icon='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.($isNextOpen?'minus':'plus').'only.gif','width="18" height="16"').' alt="" />';
		$firstHtml = $this->PM_ATagWrap($icon,$cmd);

}

			// Preparing rootRec for the mount
		if ($startuid)	{
			$tempObj = $this->graytree_db->getLeafData(0);
			$rows = $tempObj->getRecords ($startuid);
			$rootRec = $rows[$startuid];
if(!$gettreedata){
			$rootIcon = $categoryView->getIcon($rootRec);
}
		} else {
				// Artificial record for the tree root, id=0
			$rootRec = $this->getRootRecord($startuid);
			$rootIcon = $this->getRootIcon($rootRec);
		}
		$firstHtml .= $rootIcon;

if(!$gettreedata){
			// If the mount is expanded, go down:
		if (!$isNextOpen)	{
			$this->tree[] = array('leaf' => 0,'row'=>$rootRec,'bank'=>$this->bank,'HTML'=>$firstHtml);

			return;
		}
}
if(!$gettreedata){
		if (!$depthData) {
				// Set depth:
			$depthData='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/blank.gif','width="18" height="16"').' alt="" />';
		}
}

		$lastArray = array();
		$lastArray[0] = TRUE;

if(!$gettreedata){
		$imgArray = array('join.gif','joinbottom.gif','plus.gif','plusbottom.gif','minus.gif','minusbottom.gif','line.gif');
		$img = array();
		$isNextOpen = FALSE;
		$html='';

		foreach ($imgArray as $actImg) {
			$name = basename($actImg, '.gif');
			$img [$name] =  '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.$actImg,'width="18" height="16"').' alt="" />';
		}
}

		$treeDataArray = array();
		$itemViewArray = array();
		$itemDataArray = array();
		$parentIndexArray = array();
		$parentTableArray = array();
		if ($withoutLeaf) {
			$itemViewArray[0] = &$this->leafArray[0];
			$itemDataArray[0] = &$itemViewArray[0]->getData();
			$parentTableArray[$itemDataArray[0]->table] = $itemDataArray[0]->parentTable;
			$parentIndexArray[$itemDataArray[0]->table] = 0;
		} else {
			$leafIndexArray = array();
			foreach ($this->leafArray as $tableIndex => $tmpView)	{
				$itemView = &$this->leafArray[$tableIndex];
				$itemData = &$itemView->getData();
				$leafIndexArray[$itemData->table] = $tableIndex;
			}

			foreach ($this->leafArray as $tableIndex => $tmpView)	{
				$itemView = &$this->leafArray[$tableIndex];
				$itemViewArray[$tableIndex] = &$itemView;
				$itemData = &$itemView->getData();
				$itemDataArray[$tableIndex] = &$itemData;
				$parentTable = $itemData->parentTable;
				if ($parentTable)	{
					$parentTableArray[$itemData->table] = $parentTable;
					$parentIndex = $leafIndexArray[$parentTable];
					if ($parentIndex !== FALSE && $this->leafArray[$parentIndex]->usePM)	{
						$parentIndexArray[$itemData->table] = $parentIndex;
					}
				}
			}
		}

		$keyParentIndexArray = array_keys($parentIndexArray);
		$flipKeyParentIndexArray = array_flip($keyParentIndexArray);
		$parentIndexCount = count($parentIndexArray);
		$itemTableCount = count($itemDataArray);
		$categories_available = TRUE;
		$treeDataArray[0] = array('leaf' => 0, 'row' => $rootRec, 'parent' => '', 'parentIndex' => 0, 'childs' => array(), 'last' => TRUE);

		$bReady = FALSE;
		$frontDataIndex = 0;
		$endDataIndex = $frontDataIndex;
		$watchdog = 0;

		// get raw tree data
		do {

			$actTreeData = $treeDataArray[$frontDataIndex];
			$uid = $actTreeData['row']['uid'];
			$parentIndex = $actTreeData['parentIndex'];
			$leaf = $actTreeData['leaf'];
			$isNextOpen = $this->expandNext($leaf, $uid);

			if (!isset($parentIndex))	{
				// nothing
			} else if (!count($this->limitCatArray[$parentIndex]) || !in_array($uid,$this->limitCatArray[$parentIndex]) || $gettreedata)	{

				$parentDataIndex = '';
				$lastAddedIndex = '';
				$startIndex = $leaf;

				if ($itemDataArray[$startIndex]->parentTable != $itemDataArray[$startIndex]->table)	{
					$startIndex++;
				}

				for ($tableIndex = $startIndex; $tableIndex < $itemTableCount; ++$tableIndex)	{

					if (
						($tableIndex == $leaf || $itemDataArray[$tableIndex]->parentTable == $itemDataArray[$leaf]->table)
					)	{
						$tableAlias = $itemDataArray[$tableIndex]->tableAlias;
						$parentTable = $itemDataArray[$tableIndex]->parentTable;
						$itemSubidsArray =
							$itemDataArray[$tableIndex]->getParentSubRecords(
								$parentTable,
								$uid,
								$tableAlias.'.pid,'.$tableAlias.'.uid,'.$tableAlias.'.title,'.$tableAlias.'.navtitle,'.$tableAlias.'.hidden',
								$addWhereArray[$tableIndex],
								TRUE
							);

						if (count($itemSubidsArray))	{

							if (in_array($parentTable, $flipKeyParentIndexArray))	{
								$pIndex = $flipKeyParentIndexArray[$parentTable];
							} else {
								$pIndex = '';
							}

							$childArray = array();
							foreach ($itemSubidsArray as $k => $row)	{

								if($pIndex == '' || (!count($this->limitCatArray[$pIndex]) || !in_array($row['uid'], $this->limitCatArray[$pIndex])) || $gettreedata)	{

									if (!$isNextOpen && !$gettreedata)	{
										$childArray[] = TRUE;
										break; // other data is not needed
									} else {
										$endDataIndex++;
										$treeDataArray[$endDataIndex] = array('leaf' => $tableIndex,'row' => $row, 'parent' => $frontDataIndex, 'parentIndex' => $pIndex, 'childs' => array(), 'last' => FALSE);

										$childArray[] = $endDataIndex;
										$lastAddedIndex = $endDataIndex;
									}
								}
							}

							if (count($childArray))	{
								$childArray['k'] = 0;
							}
							if (!isset($treeDataArray[$frontDataIndex]['childs']['k']) || $tableIndex < $treeDataArray[$frontDataIndex]['childs']['k'])	{
								$treeDataArray[$frontDataIndex]['childs']['k'] = $tableIndex;
							} else {
								$treeDataArray[$frontDataIndex]['childs']['k'] = 0;
							}
							$treeDataArray[$frontDataIndex]['childs'][$tableIndex] = $childArray;
						}
					}
				}
				if (isset($lastAddedIndex) && is_array($treeDataArray[$lastAddedIndex]))	{
					$treeDataArray[$lastAddedIndex]['last'] = TRUE;
				}
			}

			$frontDataIndex++;
			$watchdog++;
		} while ($frontDataIndex <= $endDataIndex && $watchdog < $this->watchDogMax);

		$nextDepth = 0;
		$watchdog = 0;
		$k = 0;
		$kMax = count ($treeDataArray);
		$kStack = 0;
		$stack  = array();
		// create HTML data
		while ($k <= $kMax)	{
			$treeData = &$treeDataArray[$k];
			$bHasChilds = FALSE;
			$bKassigned = FALSE;
			$iconConnect = '';
			$iconBottom = '';
			$pm = FALSE;
			$row = $treeData['row'];
			$uid = $row['uid'];
			$parentIndex = $treeData['parentIndex'];
			$parent = $treeData['parent'];			
			$leaf = $treeData['leaf'];
			
			if (t3lib_div::testInt($parent))	{
				$parentTreeData = &$treeDataArray[$parent];
				$depth = $parentTreeData['depth'] + 1;
			} else {
				$depth = 0;
			}
			if (isset($parentIndex))	{
				$isNextOpen = $this->expandNext($leaf, $uid);
				$childCount = count ($treeData['childs']);
				$bHasChilds = ($childCount > 0);
				if ($bHasChilds)	{
					$pm = TRUE;
					if($isNextOpen) {
						$iconConnect = 'minus';
					} else {
						$iconConnect = 'plus';
					}

					if ($isNextOpen) {
						$k = $this->processTreeData($treeDataArray, $k, $kStack, $stack); // put rest on stack
						$bKassigned = TRUE;
						$nextDepth = $depth + 1;

						if ($nextDepth <= $maxDepth) {
							$lastArray[$nextDepth] = FALSE;
						} else {
							$lastArray[$nextDepth] = TRUE;
							// This Debug must remain here!
							debug ('maximum recursion of '.$maxDepth.' has been reached for table ""'.$categoryData->table.'" in the Graytree Library!');
						}
					} else if (t3lib_div::testInt($parent))	{
						$parentChilds = $parentTreeData['childs'];
						$depthChilds = $parentChilds[$treeData['leaf']];
						$endKey = end($depthChilds);

						if ($k == $endKey)	{
							$lastArray[$depth] = TRUE;
						}
					}
				} else {
					$lastArray[$depth + 1] = TRUE;
					$iconConnect = 'join';
					// $isNextOpen = TRUE;
				}
			}

	if(!$gettreedata)	{
			if (isset($parentIndex))	{
				$parIcon = $itemViewArray[$leaf]->getIcon($row);
				$parentIcon = ($parentIndex == 0 && $uid == $startuid ? $rootIcon : $parIcon);
			}

			$html = ($depth >= 1 ? $depthData : '');
			for ($k2 = 1; $k2 < $depth; $k2++) {
				$depthImg = ($lastArray[$k2] ? $depthData : $img['line']);
				$html .= $depthImg;
			}

			if ($treeData['last']) {
				$lastArray[$depth] = TRUE;
				$iconBottom = 'bottom';
			} else
			{
				// nothing
			}

			$htmlProd = $html . ($lastArray[$depth] ? $depthData : $img['line']);
			$iconFinal = $iconConnect . $iconBottom;
			$iconImage = $img[$iconFinal];
			$iconHtml = $iconImage;

			if ($pm) {
				$cmd = $this->bank.'_'.$leaf.'_'.($isNextOpen?'0_':'1_').$uid.'_'.$this->treeName;
				$iconHtml = $this->PM_ATagWrap($iconImage,$cmd);
			}

			$html .= $iconHtml;
			$html .= $parentIcon;
	}

			$treeData['depth'] = $depth; 	// store the depth which will be needed to determine the depth of the childs
			$this->tree[] = array('row'=>$row ,'HTML'=>$html, 'leaf' => $treeData['leaf'], 'bank'=>$this->bank);
			$watchdog++;
			if ($watchdog > $this->watchDogMax)	{
				break;
			}

			if (!$bKassigned) {
				if ($kStack > 0)	{
					$kParent = $stack[$kStack];
					$kStack--;
					$k = $this->processTreeData($treeDataArray, $kParent, $kStack, $stack);	// fetch next from stack
				} else {
					break;
				}
			}

			if ($kStack == 0 && (!$bHasChilds || !$isNextOpen))	{
				while ($lastArray[$depth] == TRUE)	{
					$depth--;
				}
				if ($depth == -1)	{
					break;
				}
			}
		}  // while ($k <= $kMax)	{


		return $this->tree;
	}


	/**
	 * Compiles the HTML code for displaying the structure found inside the ->tree array
	 *
	 * @param	array		"tree-array" - if blank string, the internal ->tree array is used.
	 * @return	string		The HTML code for the tree
	 */
	function printTree($treeArr=array())	{


		$titleLen=intval($this->BE_USER->uc['titleLen']);
		if (!is_array($treeArr))	$treeArr=$this->tree;

		$out='';

			// put a table around it with IDs to access the rows from JS
			// not a problem if you don't need it
			// In XHTML there is no "name" attribute of <td> elements - but Mozilla will not be able to highlight rows if the name attribute is NOT there.
		$out .= '

			<!--
			  TYPO3 tree structure.
			-->
			<table cellpadding="0" cellspacing="0" border="0" id="typo3-tree">';

		$watchdog = 0;

		foreach($treeArr as $k => $v)	{
			$leafIndex = $v['leaf'];
			$tempDataObj = &$this->graytree_db->getLeafData($leafIndex);
			$tempViewObj = &$this->leafArray[$leafIndex];
			if (is_object($tempDataObj) && is_object($tempViewObj))	{
				$idAttr = htmlspecialchars($this->domIdPrefix.$tempDataObj->getId($v['row']).'_'.$v['bank']);
				$out.='
					<tr>
						<td id="'.$idAttr.'">'.
							$v['HTML'].
							$tempViewObj->wrapTitle($tempViewObj->getTitleStr($v['row'],$titleLen),$v['row'],$v['bank']).
						'</td>
					</tr>
				';
			} else {
				break; // an error has occurred
			}
			$watchdog++;
			if ($watchdog > $this->watchDogMax)	{
				break;
			}
		}
		$out .= '
			</table>';
		return $out;
	}


	/**
	 * Get stored tree structure AND updating it if needed according to incoming PM GET var.
	 *
	 * @return	void
	 * @access private
	 */
	function initializePositionSaving()	{
		$this->pmVar = 'PM';
		if ($this->TCEforms_itemFormElName)	{
			$this->pmVar = $this->treeName.'_PM';
		}

			// PM action
			// (If an plus/minus icon has been clicked, the PM GET var is sent and we must update the stored positions in the tree):
		$PM = explode('_',t3lib_div::_GP($this->pmVar));	// 0: mount key, 1: table index, 2: set/clear boolean, 3: item ID (cannot contain "_"), 4: treeName

			// Get stored tree structure:
		$this->stored=unserialize($this->BE_USER->uc['browseTrees'][$this->treeName]);

		if (is_array ($this->stored) && is_array ($this->stored[0]) && is_array ($this->stored[0][0]) && (count($PM) == 5 || count($PM) == 1))	{
			// ok
		} else {
			$this->stored = array(); // reinitialize damaged array
			$this->savePosition();
		}

		if (count($PM) == 5 && $PM[4] == $this->treeName)	{
			if (isset($this->MOUNTS[$PM[0]]))	{
				if ($PM[2])	{	// set
					$this->stored[$PM[0]][$PM[1]][$PM[3]] = 1;
					$this->savePosition();
				} else {	// clear
					unset($this->stored[$PM[0]][$PM[1]][$PM[3]]);
					$this->savePosition();
				}
			}
		}
	}


    function makeTree($uid, $depth, $maxDepth, &$array, &$pointer)       {
         while ($v = array_shift($array)){
	           $prod = $v[row][leaf];
			if($v[row][depth]>$depth){
				    if($prod){

					    $pointer[$v[row][parent_id]]['--subLevel--']['10000'.$v[row][uid]]=$v[row];
					    $this->makeTree($uid,$depth,$maxDepth,$array,$pointer);
				    } else {
					    $pointer[$v[row][parent_id]]['--subLevel--'][$v[row][uid]]=$v[row];
	    				    $this->treeLookup[$v[row][uid]]=&$pointer[$v[row][parent_id]]['--subLevel--'][$v[row][uid]];
					    $this->makeTree($v[row][uid],$depth+1,$maxDepth,$array,$pointer[$v[row][parent_id]]['--subLevel--']);
				    }
			} elseif ($v[row][depth]==$depth){
				    if($uid==$v[row][parent_id]){
					$pointer[$v[row][parent_id]]['--subLevel--'][$v[row][uid]]=$v[row];
					$this->treeLookup[$v[row][uid]]=&$pointer[$v[row][parent_id]]['--subLevel--'][$v[row][uid]];
					$this->makeTree($v[row][uid],$depth+1,$maxDepth,$array,$pointer[$v[row][parent_id]]['--subLevel--']);
				    } else {
					$pointer[$v[row][uid]]=$v[row];
					$this->treeLookup[$v[row][uid]]=&$pointer[$v[row][uid]];
					$this->makeTree($uid,$depth,$maxDepth,$array,$pointer);
				    }
			} else {
			        array_unshift($array,$v);
				return TRUE;
			}
		}
    }


	/**
	 * Saves the content of ->stored (keeps track of expanded positions in the tree)
	 * $this->treeName will be used as key for BE_USER->uc[] to store it in
	 *
	 * @return	void
	 * @access private
	 */
	function savePosition()	{
		$this->BE_USER->uc['browseTrees'][$this->treeName] = serialize($this->stored);
		$this->BE_USER->writeUC();
	}



	/******************************
	 *
	 * Functions that might be overwritten by extended classes
	 *
	 ********************************/

	/**
	 * Returns root record for uid (<=0)
	 *
	 * @param	integer		uid, <= 0 (normally, this does not matter)
	 * @return	array		Array with title/uid keys with values of $this->title/0 (zero)
	 */
	function getRootRecord($uid) {
		return array('pid'=>$this->rootPid, 'uid'=>0, 'title'=>$this->title, 'leaf'=>0);
	}


	/**
	 * Returns the root icon for a tree/mountpoint (defaults to the globe)
	 *
	 * @param	array		Record for root.
	 * @return	string		Icon image tag.
	 */
	function getRootIcon($rec) {
		if (TYPO3_DLOG && GRAYTREE_VIEW_DLOG) 	t3lib_div::devLog('tx_graytree_View::getRootIcon $this->rootIconName = '. $this->rootIconName, GRAYTREE_EXTkey);

		$res = $this->leafArray[0]->wrapIcon('<img'.t3lib_iconWorks::skinImg($this->leafArray[0]->getIconPath(),$this->rootIconName,'width="18" height="16"').' title="Root" alt="" />',$rec);
		return $res;
	}


	/*******************************************
	 *
	 * rendering parts
	 *
	 *******************************************/


	/**
	 * Wrap the plus/minus icon in a link
	 *
	 * @param	string		HTML string to wrap, probably an image tag.
	 * @param	string		Command for 'PM' get var
	 * @param	boolean		If set, the link will have a anchor point (=$bMark) and a name attribute (=$bMark)
	 * @return	string		Link-wrapped input string
	 * @access private
	 */
	function PM_ATagWrap($icon,$cmd,$bMark='')	{
		$rc = '';

		if ($this->thisScript) {
			if ($bMark)	{
				$anchor = '#'.$bMark;
				$name=' name="'.$bMark.'"';
			}
			$separator = (strstr($this->thisScript,'?') ? '&' : '?');
			$aUrl = $this->thisScript.$separator.$this->pmVar.'='.$cmd.$anchor;

			if ($this->pmIconTagAttributes['onclick'])	{

				$cOnClickArray = $this->pmIconTagAttributes['onclick'];
				$cOnClick = $cOnClickArray[0].htmlspecialchars('var name=\''.$this->pmVar.'\'; var elements = document.getElementsByName(name); elements.item(0).value=\''.$cmd.'\';').$cOnClickArray[1];
				$rc = '<a href="#" onclick="'.$cOnClick.'"'.$name.'>'.$icon.'</a>';
			} else {
				$rc = '<a href="'.htmlspecialchars($aUrl).'"'.$name.'>'.$icon.'</a>';
			}
		} else {
			$rc = $icon;
		}
		return $rc;
	}


	/**
	 * Generate the plus/minus icon for a browsable tree.
	 *
	 * @param	array		record for the entry
	 * @param	string		icon HTML
	 * @param	integer		The number of sub-elements to the current element.
	 * @param	integer		table index
	 * @param	boolean		The element was expanded to render subelements if this flag is set.
	 * @return	string		Image tag with the plus/minus icon.
	 * @access private
	 * @see t3lib_pageTree::PMicon()
	 */
	function PMicon($row,$icon,$nextCount,$cattableIndex,$exp)	{

		if ($nextCount)	{
			$cmd = $this->bank.'_'.$cattableIndex.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->getTreeTitle();
			$bMark = ($this->bank.'_'.$row['uid']);
			$icon = $this->PM_ATagWrap($icon,$cmd,$bMark);
		}
		return $icon;
	}



	/******************************
	 *
	 * Functions that might be overwritten by extended classes
	 *
	 ********************************/

	/**
	 * Returns TRUE/FALSE if the next level for $id should be expanded - based on data in $this->stored[][] and ->expandAll flag.
	 * Extending parent function
	 *
	 * @param	integer		record id/key
	 * @return	boolean
	 * @access private
	 * @see t3lib_pageTree::expandNext()
	 */
	function expandNext($cattableIndex, $id)	{
		$rc = ($this->stored[$this->bank][$cattableIndex][$id] || $this->expandAll) ? 1 : 0;
		return $rc;
	}


	/**
	 * Returns the title for the tree
	 *
	 * @return	string
	 */
	function getTreeTitle()	{
		return $this->title;
	}


	/**
	 * Returns the treename (used for storage of expanded levels)
	 *
	 * @return	string
	 */
	function getTreeName()	{
		return $this->treeName;
	}


	/**
	 * Returns the reference to the object of the leaf view
	 *
	 * @param	integer		index of the leaf starting with 0
	 * @return	object		reference to the leaf view object
	 */
	function &getLeafView($i) {
		return $this->leafArray[$i];
	}


	/**
	 * Returns the count of leaves in the view
	 *
	 * @param	void
	 * @return	integer		count of tree leaves
	 */
	function getCount() {
		return count($this->treeView->leafArray);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_view.php']);
}
?>
