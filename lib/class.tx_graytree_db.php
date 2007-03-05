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
 * database functions for a table that is organized like a tree with parent_id (pid)
 * Base class for a tree class
 * for internal use inside Graytree
 *
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tx_graytree
 * $Id: class.tx_graytree_db.php 148 2006-04-04 14:17:44Z franz $
 *
 */



/**
 * Base class for a tree table class
 * works in union with the tx_graytree_leafView class
 * You should make a subclass from this for an easy initialisation.
 *
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage graytree
 */
 
 
//require_once(PATH_txgraytree.'lib/class.tx_graytree_leafdata.php');


class tx_graytree_db {
	var $dataArray = array();
	var $tree;
	var $treeLookup;

	/**
	 * initialisation and instantiation of the leaf data classes
	 * must only be called from the Constructor of a derived class
	 * The $leafArray must be given as a parameter. This function creates all the objects of the classes from this array.
	 * Each element of this array consists of an array ('data', 'view'):
	 * 'data' must be derived from tx_graytree_leafData and
	 * 'view' must be derived from tx_graytree_leafView.
	 * 
	 * @param	array	array of array ('view', 'data'). The first element must be the category.
	 * @return	void
	 */

	function init ($leafArray=array()) {
		foreach ($leafArray as $leaf) {
				$dataclass = t3lib_div::makeInstanceClassName($leaf['data']);
				$data = new $dataclass();
				$data->init();
				$this->dataArray[] = $data;
		}
	}

	/**
	 * returns the data object for the data class of a leaf
	 * 
	 * @param	integer	index number of the leaf, starting with 0
	 * @return	object	reference to the leaf data object
	 */
	function &getLeafData($i) {
		return $this->dataArray[$i];
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_db.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_db.php']);
}


?>
