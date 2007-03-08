<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Franz Holzinger <kontakt@fholzinger.com>
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
 * Base class for selection tree classes	--> Start here to use the tree functionality!
 * This is the top level base class you can use to build your own trees. You should use a derived class from this as the 
 * starting point for the creation of your own category trees with as many different leaf tables as you want. 
 * 
 * 
 * In this class the tree class and the tree leaf classes get instantiated and initialised.
 *
 * Make a derived class from this like in this example. The tree data class must derive from tx_graytree_db.
 * The tree view class must derive from tx_graytree_View.
 * All the data and view leaf classes must derive from tx_graytree_leafData and tx_graytree_leafView respectively.
 * Make all your settings only in the derived classes. That is all what needs to be done to build your category trees.
 * You can use commerce/mod_category/class.tx_commerce_category_navframe.php as an example for the
 * usage of the Graytree class library.
 *
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage graytree
 * $Id$
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */


#define('GRAYTREE_BROWSETREE_DLOG', '1');


class tx_graytree_browseTree {
	var $treeView;
	var $treeData;
	var $isTreeViewClass = TRUE;
	var $tree;	// tree array of views
	var $treeInfoArray;
	var $leafInfoArray;
	var $treeName = ''; // If set this will override the treename of the instantiated tree class

//	// variables only needed for display in TCEforms:	
//	var $TCEforms_itemFormElName='';
//	var $TCEforms_nonSelectableItemsArray=array();
//
//	/**
//	 * Icon TAG attributes for plus/minus symbol
//	 */
//	var $pmIconTagAttributes = array();
		
	/**
	 * initialisation
	 * must only be called from the Constructor of a derived class
	 * The $treeInfoArray and $leafInfoArray must have been filled in. This function creates all the classes from this array.
	 * Each element of this array consists of an array ('data', 'view'):
	 * The first element corresponds to the tree, the others to the leaf tables.
	 * The values must contain class names, where 'data' must or $treeInfoArray be derived from tx_graytree_db and
	 * 'view' must be derived from tx_graytree_View.
	 * For the $leafInfoArray 'data' must be derived from tx_graytree_leafData and
	 * 'view' must be derived from tx_graytree_leafView.
	 * 
	 * example for creating a category tree with products:
	 * 
	 *   $this->treeInfoArray = array ('data' => 'tx_graytree_db', 'view' => 'tx_commerce_treeView');
	 *   $tempArray = array ('data' => 'tx_commerce_stdselectionCategoryData', 'view' => 'tx_commerce_stdselectionCategoryView');
	 *   $this->leafInfoArray[] = $tempArray;
	 *   $tempArray = array ('data' => 'tx_commerce_stdselectionProductData', 'view' => 'tx_commerce_stdselectionProductView');
	 *   $this->leafInfoArray[] = $tempArray;
	 * 
	 * You can add as many leaf classes for tables as you want.
	 * The data class can access the database directly or use variables in the computer memory or something else. 
	 * The data is will only be accessed by means of its member functions. So the flexibility is left to the programmer
	 * of the data leaf class.
	 * 
	 * The first element in these arrays must be the category.
	 * 
	 * @param 	table name for following data
	 * @param	startRow		the row where the tree has been started in TCE
	 * @param	limitCatArray	Array of categories from which no subcategories shall be shown
	 * @return	void
	 */
	function init($table='', $startRow=array(), $limitCatArray=array())	{
			// do not init twice !
		if (!is_object($this->treeData)) {
			$tree=array();

			$this->treeData = &t3lib_div::makeInstance($this->treeInfoArray['data']);
			$this->treeView = &t3lib_div::makeInstance($this->treeInfoArray['view']);
			if ($this->treeName) {
				$this->treeView->treeName = $this->treeName; 	
			}

			$this->treeData->init($this->leafInfoArray);
			$this->treeView->init($this->treeData, $this->leafInfoArray, $table, $startRow, $limitCatArray);
		}
		
	}
	
	
	/**
	 * Returns the HTML code of the tree
	 *
	 * @param	void
	 * @return	string HTML code
	 */
	function printTree()	{
		$this->tree = $this->treeView->getBrowsableTree();
		return $this->tree;
	}


	/**
	 * Disables or enables the context/click menu on all of the leaf icons of the tree
	 *
	 * @param	boolean		false to enable the context menu
	 * @return	void
	 */
	function setExtIconMode($ext_IconMode)	{
	
		for ($i=0; $i<count($this->treeView->leafArray); ++$i) {
			$tree = &$this->treeView->getLeafView($i);
			$tree->ext_IconMode = $ext_IconMode;  
		}
	}

	/**
	 * Sets the current script to all leaves. This is needed to reload the tree.
	 *
	 * @param	string		name of the script which draws the tree
	 * @return	void		...
	 */
	function setScript($thisScript)	{
		$this->treeView->thisScript = $thisScript;
		for ($i=0; $i<count($this->treeView->leafArray); ++$i) {
			$tree = &$this->treeView->getLeafView($i);
			$tree->thisScript = $thisScript;
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_browsetree.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_browsetree.php']);
}


?>