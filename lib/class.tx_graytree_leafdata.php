<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003 René Fritz (r.fritz@colorcube.de)
*  (c) 2005-2007 Franz Holzinger <kontakt@fholzinger.com>
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
 * Base class for a tree table class
 * works in union with the tx_graytree_leafView class
 * You should make a subclass from this for an easy initialisation.
 * 
 * see class.tx_graytree_browsetree.php for the usage
 *
 * @author	René Fritz <r.fritz@colorcube.de>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com>
 * $Id$
 *
 */

 
 
class tx_graytree_leafData {
	/*** Variables that must be filled in from derived classes: ***/

	/**
	 * Database table to get the tree data from.
	 */
	var $table='fill out $table in subclass of class tx_graytree_leafData with the name of the table!';
	var $tableAlias='main'; // alias for $table
	var $mm_table='';	// if $mm_table is not provided then $parentField is needed
	var $mm_tableAlias='mm';	// alias for $mm_table
	var $parentField='';   // Parent-id field name. if $parentField is not provided then a $mm_table is needed. If empty $TCA[$this->table]['ctrl']['treeParentField'] will be used..
	var $mm_field='';   // if $mm_table is provided the field $mm_field of $table gives you the number of categories above 

	var $parentTable='';	// use this, if $table build via $mm_table a tree to $table; in this case the $uid will refer to $parentTable
	var $parentTableAlias='parent';	// use this, if $table build via $mm_table a tree to $table; in this case the $uid will refer to $parentTable
	var $parentLeafData = '';	// insert here the parent's leaf data class name and don't forget to inlcude this file

	/*** Variables that can be filled in from derived classes: ***/

	var $mm_prependTableName=TRUE;
	var $pidList='';
	var $pidListWhere='';

	var $mm_sortField='sorting';
	var $sorting='sorting';
	var $where_default = '';

	/**
	 * Default set of fields selected from the tree table.
	 * @see setFields()
	 */
	var $fieldList = 'uid';

	/**
	 * List of other fields which are ALLOWED to set
	 * @see setFields()
	 */
	var $defaultList = 'uid,pid,tstamp,sorting,deleted,perms_userid,perms_groupid,perms_user,perms_group,perms_everybody,crdate,cruser_id';


	/*** Variables that are calculated or private ***/
	var $mm_sorting='';
	var $resReturn = false;


	/**
	 * Initialize the object.
	 * The class will be setup for BE use. See setEnableFields().
	 *
	 * @return	void
	 */
	function init()	{
		global $TCA, $TSFE;

		t3lib_div::loadTCA($this->table);
		if (trim($this->parentField) == '') {
			$this->parentField = $TCA[$this->table]['ctrl']['treeParentField'];
		}

		$this->setFields();
		$this->setSortFields($this->sorting);

		$this->setEnableFields((is_object($TSFE)?'FE':'BE'));

	    $this->where_default = $this->enableFields('delete').$this->enableVersionFields('sys_language_uid');

		if($this->mm_table)
			$this->initMM($this->mm_sortField);
	}

	/**
	 * Initialize the the MM-tables
	 *
	 * @return	void
	 */
	function initMM($mm_sortField='sorting')	{
		$this->mm_sorting = ' ORDER BY '.$this->mm_tableAlias.'.'. $mm_sortField;
		t3lib_div::loadTCA($this->mm_table);
	}


	/**
	 * Sets the internal pid-list.
	 *
	 * @param	string		Commalist of ids
	 * @return	void
	 */
	function setPidList ($pidList)  {
		$this->pidList = $pidList;
	 	$this->pidListWhere = $pidList ? ' AND '. $this->tableAlias . '.pid IN ('.$pidList.')' : '';
	}



	/**
	 * Extends the fieldList with the alias table name
	 *
	 * @param	string		Commalist of fields
	 * @param	string		alias table name
	 * @return	the fieldlist containting the alias name
	 */
	function getFieldsAlias($fields, $tableAlias)	{
		
		$tableAlias = ($tableAlias ? $tableAlias : $this->$tableAlias);
		$fieldArr = explode(',',$fields);
		$setFields=array();
		foreach($fieldArr as $field) {
			$setFields[]= $tableAlias.'.'.$field.' '.$field;
		}

		$rc = implode(',',$setFields);
		return $rc;	
	}



	/**
	 * Sets the internal fieldList.
	 * The field list will be used for SELECT.
	 *
	 * @param	string		Commalist of fields
	 * @param	boolean		If set, the fieldnames will be set no matter what. Otherwise the field name must be found as key in $TCA['pages']['columns']
	 * @return	void
	 */
	function setFields($fields='',$noCheck=0)	{
		global $TCA;

		$fields = $fields ? $fields : 'uid,pid,'.$TCA[$this->table]['ctrl']['label'];
		$fieldArr = explode(',',$fields);

		$setFields=array();
		foreach($fieldArr as $field) {
			if ($noCheck || is_array($TCA[$this->table]['columns'][$field]) ||
					t3lib_div::inList($this->defaultList,$field))	{
				$setFields[]= ($this->mm_prependTableName ? $this->tableAlias.'.'.$field.' '.$field : $field);
			}
		}
		$this->fieldList = implode(',',$setFields);
	}




	/**
	 * Sets the internal sorting fields.
	 * The field list will be used for SELECT.
	 *
	 * @param	string		Commalist of fields
	 * @return	void
	 */
	function setSortFields($sortFields='')	{
		if($sortFields) {
			$this->sorting = ' ORDER BY '.$sortFields;
		} else {
			$this->sorting = ($TCA[$this->table]['ctrl']['sortby'] ? ' ORDER BY '.$TCA[$this->table]['ctrl']['sortby'] : ' '.$TCA[$this->table]['ctrl']['default_sortby']);
		}

//		if ($this->mm_prependTableName) {
//			$sortFields=array();
//			$sortingArr = explode(',',$this->sorting);
//			foreach ($sortingArr as $sort) {
//				$sortFields[] = $this->tableAlias.'.'.sort;
//			}
//			$this->sorting = implode (',', $sortFields);
//		}
	}


	/**
	 * Sets the internal where clause for enable-fields..
	 * The field list will be used as enable-fields.
	 *
	 *
	 * @param	string		Commalist of fields. "FE" set the proper frontend fields, "BE" for backend.
	 * @return	void
	 * @see enableFields()
	 */
	function setEnableFields ($fields)  {
		if ($fields=='FE') {
			$this->where_default = $this->enableFields('delete,disabled,starttime,endtime,fe_group');
		} elseif ($fields=='BE') {
			$this->where_default = $this->enableFields('delete');
		} else {
			$this->where_default = $this->enableFields($fields);
		}
	}



	/*******************************************
	 *
	 * common record functions (using SQL queries)
	 *
	 *******************************************/

	/**
	 * Sets a flag which let all record functions return the query result not the records.
	 *
	 * @param	boolean		true if the result records should be returned
	 * @return	void
	 */
	function setResReturn ($resReturn=true)  {		// default auf true gesetzt - Franz
		$this->resReturn = $resReturn;
	}


	/**
	 * Returns the pid from the record (typ. uid)
	 *
	 * @param	array		Record array
	 * @return	integer		The "uid" field value.
	 */
	function getPid($row) {
		return $row['pid'];
	}


	/**
	 * Returns the id from the record (typ. uid)
	 *
	 * @param	array		Record array
	 * @return	integer		The "uid" field value.
	 */
	function getId($row) {
		return $row['uid'];
	}

//
//	/**
//	 * Returns the title of an item
//	 *
//	 * @param	[type]		$id: ...
//	 * @return	string
//	 */
//	function getItemTitle($id)	{
//		global $TYPO3_DB;
//		$itemTitle=$id;
//
//		if ($id > 0) {
//			$res = $TYPO3_DB->exec_SELECTquery(implode(',',$this->fieldArray), $this->table.' '.$this->tableAlias, 'uid='.intval($id));
//			while($row = $TYPO3_DB->sql_fetch_assoc($res)) {
//				$itemTitle = $this->getTitleStr($row);
//			}
//		} else {
//			$itemTitle = $this->title;
//		}
//		return $itemTitle;
//	}

	/**
	 * Gets records with uid IN $uids
	 * You can set $field to a list of fields (default is '*')
	 * Additional WHERE clauses can be added by $where (fx. ' AND blabla=1')
	 *
	 * @param	string		Commalist of UIDs of records
	 * @param 	boolean 	Enable sorting
	 * @param	string		Commalist of fields to select
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @return	array		Returns the rows if found, otherwise empty array
	 */
	function getRecords ($uids, $sorting=true, $fields='', $where='')	{
		global $TYPO3_DB;
		$fields = $fields?$fields:$this->fieldList;
		
		$sort = $sorting?$this->sorting:'';

		$rows = array();
		
 		$res2 = $TYPO3_DB->SELECTquery($fields, $this->table.' '.$this->tableAlias, $this->tableAlias.'.uid IN ('.$uids.')'.$where.$this->where_default.$this->pidListWhere.$sort);
		$res = $TYPO3_DB->exec_SELECTquery($fields, $this->table.' '.$this->tableAlias, $this->tableAlias.'.uid IN ('.$uids.')'.$where.$this->where_default.$this->pidListWhere.$sort);

		while ($row = $TYPO3_DB->sql_fetch_assoc($res))	{
			$rows[$row['uid']]=$row;
		}
		return $rows;
	}



	/*******************************************
	 *
	 * root-record functions (using SQL queries)
	 *
	 *******************************************/


	/**
	 * Returns an array with rows of root-records with parent_id=0
	 *
	 * @param	string		List of fields to select
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @param 	boolean 	Enable sorting
	 * @return	array		Returns the rows if found, otherwise empty array
	 */
	 function getRootRecords ($fields='',$where='',$sorting=true)	{
		$fields = $fields?$fields:$this->fieldList;

		return $this->getSubRecords ('0',$fields,$where,$sorting);
	}


	/**
	 * Returns a commalist of record ids of root records (parent_id=0)
	 *
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @return	string		Comma-list of record ids of root records
	 */
	function getRootRecordsIdList($where='')	{
		$rows = $this->getSubRecords ('0','uid',$where,false);
		return implode(',',array_keys($rows));
	}


	/*******************************************
	 *
	 * sub-record functions (using SQL queries)
	 *
	 *******************************************/


	/**
	 * Returns an array with rows for subrecords with parent_id=$uid
	 *
	 * @param	integer		UID of parent of records
	 * @param	string		List of fields to select (default is '*')
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @param 	boolean 	Enable sorting
	 * @return	array		Returns the rows if found, otherwise empty array
	 */
	function getSubRecords ($uid, $fields='', $where='', $sorting=true)	{
		global $TYPO3_DB;
		
		$res='';
		$fields = $fields?$fields:$this->fieldList;
		
		if ($sorting) {
			$orderBy = $this->sorting;
			#$mm_orderBy = $this->mm_sorting;
			$mm_orderBy = ' ORDER BY main.' .$this->mm_sortField; // $this->mm_sorting;
		} else {
			$orderBy = '';
			$mm_orderBy = '';
		}
		
		
		$whereList = $where.$this->where_default.$this->pidListWhere;

		// not the root record which is 0 ?
		if ($uid) {
			if ($this->parentField)	{
				$res = $TYPO3_DB->exec_SELECTquery($fields, $this->table.' '.$this->tableAlias, $this->tableAlias.'.'.$this->parentField.' IN ('.$uid.') '.$whereList.$orderBy);
			}
			else if ($this->mm_table){
				$res = $this->exec_SELECT_mm_alias_query($fields, 
						$this->table, $this->tableAlias,
						$this->mm_table, $this->mm_tableAlias,
						$this->parentTable, $this->parentTableAlias,
						'AND ' .$this->mm_tableAlias.'.uid_foreign='.intval($uid).$whereList.$mm_orderBy);
			}
		} else {
		#	$res2 = $TYPO3_DB->SELECTquery($fields, $this->table.' '.$this->tableAlias, $this->tableAlias.'.'.$this->parentField.'='.intval($uid).$whereList.$orderBy);

			$tmpWhere = $this->tableAlias.'.'.$this->mm_field.'='.intval($uid).$whereList.$orderBy;
			$res = $TYPO3_DB->exec_SELECTquery($fields, $this->table.' '.$this->tableAlias, $tmpWhere);
		}


		if($this->resReturn) return $res;
		$rows = array();
		while ($row = $TYPO3_DB->sql_fetch_assoc($res))	{
			$rows[$row['uid']]=$row;
		}
		return $rows;
	}


	/**
	 * Returns an array with rows for subrecords dependant on a parent category table
	 *
	 * @param	integer		UID of parent of records
	 * @param	string		List of fields to select (default is '*')
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @param 	boolean 	Enable sorting
	 * @return	array		Returns the rows if found, otherwise empty array
	 */
	function getParentSubRecords ($table, $uid, $fields='', $where='', $sorting=true)	{
		$rc = $this->getSubRecords ($uid, $fields, $where, $sorting); // TODO: integrate the dependancy on a table
		return $rc;
	}


	/**
	 * Count subrecords with parent_id=$uid
	 * Additional WHERE clauses can be added by $where (fx. ' AND blabla=1')
	 *
	 * @param	integer		UIDs of records
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @return	integer		Count of subrecords
	 */
	function countSubRecords($uid,$where='')	{
		$row = $this->getSubRecords ($uid,'COUNT(*)',$where,false);
		
		reset($row);
		$row = current($row);
		$rc = intval($row['COUNT(*)']);

		return $rc;
	}


	/**
	 * Generates a list of Page-uid's from $id. List does not include $id itself. The sorting of this class object is used.
	 * Returns the list with a comma in the end (if any pages selected!)
	 * $begin is an optional integer that determines at which level in the tree to start collecting uid's. Zero means 'start right away', 1 = 'next level and out'
	 *
	 * @param	integer		UIDs of records
	 * @param	integer		depth in the tree
	 * @param	integer		begin level
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @return	string		comma separated list of uids
	 */
	function getTreeList($uid,$depth=1,$beginLevel=0,$where='')	{
		/* Generates a list of Page-uid's from $id. List does not include $id itself

		 Returns the list with a comma in the end (if any pages selected!)
		 $begin is an optional integer that determines at which level in the tree to start collecting uid's. Zero means 'start right away', 1 = 'next level and out'
		*/

		global $TYPO3_DB;

		$depth=intval($depth);
		$begin=intval($beginLevel);
		$id=intval($uid);
		$theList='';
		$where_clause = '';
		$from_table = '';
		$select_fields = 'uid';
		$res = '';

		$whereList = $where.$this->where_default.$this->sorting;

		if ($depth>0)	{
			if ($uid > 0) {
				if (!$this->mm_table || !$this->parentTable) {
					$where_clause = $this->tableAlias.'.'.$this->parentField.'='.intval($uid).$whereList;
					$from_table = $this->table.' '.$this->tableAlias;
	
				 	$res = $TYPO3_DB->exec_SELECTquery($select_fields, $from_table, $where_clause);
					echo mysql_error();
				} else {
						$select_fields = $this->tableAlias.'.'.$select_fields;
						$where_clause = ' AND '.$this->parentTableAlias.'.uid ='.intval($uid).$whereList;
		
						$res = $this->exec_SELECT_mm_alias_query($select_fields,
								$this->table, $this->tableAlias,
								$this->mm_table, $this->mm_tableAlias,
								$this->parentTable, $this->parentTableAlias,
								$where_clause);
				}
			} else {
				$res = $TYPO3_DB->exec_SELECTquery('uid', $this->table.' '.$this->tableAlias, $this->tableAlias.'.'.$this->mm_field.'='.intval($uid).$whereList);
			}

			while ($row = $TYPO3_DB->sql_fetch_assoc($res))	{
				if ($beginLevel<=0)	{
					$theList.=$row['uid'].',';
				} 
				if ($depth>1) {
					$theList.=$this->getTreeList($row['uid'], $depth-1, $begin-1, $where);
				}
			}
		}

		return $theList;
	}


	/**
	 * Returns a commalist of record ids for a query (eg. 'WHERE parent_id IN (...)')
	 * $uid_list is a comma list of record ids
	 * $rdepth is an integer >=0 telling how deep to dig for uids under each entry in $uid_list
	 * @param	integer		UIDs of records
	 * @param	integer		depth in the tree
	 * @param	integer		begin level
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @return	string		comma separated list of uids
	 */
	function getSubRecordsIdList($uid_list,$depth=1,$beginLevel=0,$where=' ')	{
		$depth = t3lib_div::intInRange($depth,0);

		$uid_list_arr = array_unique(t3lib_div::trimExplode(',',$uid_list,1));
		$uid_list='';
		reset($uid_list_arr);
		
		while(list(,$val)=each($uid_list_arr))	{
			$val = t3lib_div::intInRange($val,0);
			$subids = $this->getTreeList($val,$depth,$beginLevel,$where);
			$uid_list.=$subids.',';
		}
		$uid_list = preg_replace('/,+$/','',$uid_list); 
		return $uid_list;
	}


	/**
	 * Returns a commalist of record ids (including the ones from $uid_list) for a query (eg. 'WHERE parent_id IN (...)')
	 * $uid_list is a comma list of record ids
	 * $depth is an integer >=0 telling how deep to dig for uids under each entry in $uid_list
	 * @param	integer		UIDs of records
	 * @param	integer		depth in the tree
	 * @param	integer		begin level
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @return	string		comma separated list of uids
	 */
	function getRecordsIdList($uid_list,$depth=0,$beginLevel=0,$where=' ')	{

		$uid_list_prepend = $uid_list;
		$uid_list=$this->getSubRecordsIdList($uid_list,$depth,$beginLevel,$where);

		return str_replace(',,','',$uid_list_prepend.','.$uid_list);
	}




	//------------------------- MM related -------------------------------------

	/**
	 * insert a relation from a data record to a tree-record
	 * @param	integer		UIDs of records
	 * @param	integer		UIDs of tree records
	 * @return	void
	 */


	/**
	 * insert a relation from a data record to a tree-record
	 */
	function writeMM($dataRecordUid,$treeRecordUid)	{

			// delete all relations:
		$uid = intval($uid);
		$query='DELETE FROM '.$this->mm_table.' WHERE uid_local='.$dataRecordUid.' AND foreign_uid='.$treeRecordUid;
		$query.=$this->mm_prependTableName?' AND tablenames=\''.$this->table.'\'':'';
		$res=mysql(TYPO3_db,$query);

		if ($this->mm_prependTableName)	{
			$prependTable=',tablenames';
			$prependTableName=',\''.addslashes($this->table).'\'';
		}
		$sort=0; // what to set here???
		$query='INSERT INTO '.$this->mm_table.' (uid_local,uid_foreign,sorting'.$prependTable.') VALUES (\''.$dataRecordUid.'\',\''.$treeRecordUid.'\','.$sort.$prependTableName.')';
		$res=mysql(TYPO3_db,$query);

// !!!!!! update the relation counter in the data table

	}




	/*******************************************
	 *
	 * misc functions
	 *
	 *******************************************/

	/**
	 * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields set to values that should de-select them according to the current time, preview settings or user login.
	 * It is using the $TCA arrays "ctrl" part where the key "enablefields" determines for each table which of these features applies to that table.
	 *
	 * @param	array		Commalist you can pass where items can be "disabled", "starttime", "endtime", "fe_group" (keys from "enablefields" in TCA) and if set they will make sure that part of the clause is not added. Thus disables the specific part of the clause. For previewing etc.
	 * @param	string		Table name found in the $TCA array (default $this->table)
	 * @see tslib_cObj::enableFields(), deleteClause()
	 */
	function enableFields($useFields='delete,disabled,starttime,endtime,fe_group',$table='',$tableAlias='main')	{
		if (!is_array($useFields)) {
			$useFields = t3lib_div::trimExplode(',',$useFields,1);
		}
		$table=$table?$table:$this->table;
		$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
		$query='';
		if (is_array($ctrl))	{
			if ($ctrl['delete'] && in_array('delete',$useFields))	{
				$query.=' AND NOT '.$tableAlias.'.'.$ctrl['delete'];
			}
			if (is_array($ctrl['enablecolumns']))	{
				if ($ctrl['enablecolumns']['disabled'] && in_array('disabled',$useFields))	{
					$field = $tableAlias.'.'.$ctrl['enablecolumns']['disabled'];
					$query.=' AND NOT '.$field;
				}
				if ($ctrl['enablecolumns']['starttime'] && in_array('starttime',$useFields))	{
					$field = $table.'.'.$ctrl['enablecolumns']['starttime'];
					$query.=' AND ('.$field.'<='.$GLOBALS['SIM_EXEC_TIME'].')';
				}
				if ($ctrl['enablecolumns']['endtime'] && in_array('endtime',$useFields))	{
					$field = $tableAlias.'.'.$ctrl['enablecolumns']['endtime'];
					$query.=' AND ('.$field.'=0 OR '.$field.'>'.$GLOBALS['SIM_EXEC_TIME'].')';
				}
				if ($ctrl['enablecolumns']['fe_group'] && in_array('fe_group',$useFields))	{
					$field = $tableAlias.'.'.$ctrl['enablecolumns']['fe_group'];
					$gr_list = $GLOBALS['TSFE']->gr_list;
					if (!strcmp($gr_list,''))	$gr_list=0;
					$query.=' AND '.$field.' IN ('.$gr_list.')';
				}
			}
		} else {die ('NO entry in the \$TCA-array for \''.$table.'\' with alias '.'\''. $tableAlias .'\'' );}

		return $query;
	}


	/**
	 * Returns a part of a WHERE clause which will filter out records with version and language information
	 *
	 * @param	array		Commalist you can pass where items can be "disabled", "starttime", "endtime", "fe_group" (keys from "enablefields" in TCA) and if set they will make sure that part of the clause is not added. Thus disables the specific part of the clause. For previewing etc.
	 * @param	string		Table name found in the $TCA array (default $this->table)
	 * @see tslib_cObj::enableFields(), deleteClause()
	 */
	function enableVersionFields($useFields='sys_language_uid',$table='',$tableAlias='main')	{
		if (!is_array($useFields)) {
			$useFields = t3lib_div::trimExplode(',',$useFields,1);
		}
		$table=$table?$table:$this->table;
		$query=' AND '. $tableAlias.'.sys_language_uid=0';

		return $query;
	}

//---------------------------------------------------------------------------------------------------

	/**
	 * Returns an array with rows for subrecords with parent_id=$uid
	 * for internal usage only
	 *
	 * @param	integer		UID of record
	 * @param	string		List of fields to select (default is '*')
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @param	[type]		$table: ...
	 * @param	[type]		$where: ...
	 * @return	array		Returns the rows if found, otherwise empty array
	 */
	function _getSubRecords ($uidList,$level=1,$fields='*',$table='',$where='')	{
		$rows = array();
		
		$table=$table?$table:$this->tableAlias;
		
		while ($level && $uidList)	{
			$level--;

			$newIdList = array();
			t3lib_div::loadTCA($table);
			$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
			$tmpWhere = $ctrl['treeParentField'].' IN ('.$uidList.') '.$where.' AND NOT '.$table.'.'.$ctrl['delete'];
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $tmpWhere);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$rows[$row['uid']] = $row;
				$newIdList[] = $row['uid'];
			}
			$uidList = implode(',', $newIdList);

		}


		return $rows;
	}


	/**
	 * Returns a commalist of sub record ids
	 * for internal usage only
	 *
	 * @param	integer		UIDs of record
	 * @param	string		Additional WHERE clause, eg. " AND blablabla=0"
	 * @param	[type]		$table: ...
	 * @param	[type]		$where: ...
	 * @return	string		Comma-list of record ids
	 */
	function _getSubRecordsIdList($uidList,$level=1,$table='',$where='')	{
		$table=$table?$table:$this->tableAlias;
		$rows = $this->getSubRecords ($uidList,$level,'uid',$table,$where);
		return implode(',',array_keys($rows));
	}



	/**
	 * Returns true if the tablename $checkTable is allowed to be created on the page with record $pid_row
	 *
	 * @param	array		Record for parent page.
	 * @param	string		Table name to check
	 * @return	boolean		Returns true if the tablename $checkTable is allowed to be created on the page with record $pid_row
	 */
	function isTableAllowedForThisPage($pid_row, $checkTable)	{
		global $TCA, $PAGES_TYPES;
		if (!is_array($pid_row))	{
			if ($GLOBALS['BE_USER']->user['admin'])	{
				return true;
			} else {
				return false;
			}
		}
			// be_users and be_groups may not be created anywhere but in the root.
		if ($checkTable=='be_users' || $checkTable=='be_groups')	{
			return false;
		}
			// Checking doktype:
		$doktype = intval($pid_row['doktype']);
		if (!$allowedTableList = $PAGES_TYPES[$doktype]['allowedTables'])	{
			$allowedTableList = $PAGES_TYPES['default']['allowedTables'];
		}
		if (strstr($allowedTableList,'*') || t3lib_div::inList($allowedTableList,$checkTable))	{		// If all tables or the table is listed as a allowed type, return true
			return true;
		}
	}


	/********************************
	 *
	 * exension supporting functions
	 *
	 ********************************/

	/**
	 * Function, processing the query part for selecting/filtering records in the calling extension
	 * Called from the extension
	 *
	 * @param	string		Query type: AND, OR, ...
	 * @param	string		Operator, eg. '!='
	 * @param	string		Category - corresponds to the "treename" used for the category tree in the nav. frame
	 * @param	string		The select value/id
	 * @param	string		The select value (true/false,...)
	 * @param	object		Reference to the parent object of the extension
	 * @return	string
	 */
	function selectProc($queryType, $operator, $cat, $id, $value, &$extObj)      {

		$catUidList = tx_graytree_div::uniqueList(intval($id), $this->getSubRecordsIdList(intval($id),99,$this->table));
		if ($operator=='!=')	{
			$query= $this->mm_table.'.uid_foreign NOT IN ('.$catUidList.')';
		} else {
			$query= $this->mm_table.'.uid_foreign IN ('.$catUidList.')';
		}

		$extObj->qg->queryAddCategoryJoin();

		return array($queryType,$query);
	}


	/*******************************************
	 *
	 * Misc
	 *
	 *******************************************/	

	/**
	 * Returns field list with table name prepended
	 *
	 * @param	string		Table name
	 * @param	mixed		Field list as array or comma list as string
	 * @param	boolean		If set the fields are checked if set in TCA
	 * @param	boolean		If set the fields are prepended with table.
	 * @return	string		Comma list of fields with table name prepended
	 */
	function compileFieldList($table, $fields, $checkTCA=TRUE, $prependTableName=TRUE) {
		global $TCA;

		$fieldList = array();

		$fields = is_array($fields) ? $fields : t3lib_div::trimExplode(',', $fields, 1);

		if ($checkTCA) {
			if (is_array($TCA[$table])) {
				$fields = $this->cleanupFieldList($table, $fields);
			} else {
				$table = NULL;
			}
		}
		if ($table) {
			foreach ($fields as $field) {
				if ($prependTableName) {
					$fieldList[$table.'.'.$field] = $table.'.'.$field;
				} else {
					$fieldList[$field] = $field;
				}
			}
		}
		return implode(',',$fieldList);
	}


	/**
	 * Removes fields from a record row array that are not configured in TCA
	 *
	 * @param	string		Table name
	 * @param	array		Record row
	 * @return	array		Cleaned row
	 */
	function cleanupRecordArray($table, $row) {
		$allowedFields = $this->getTCAFieldListArray($table);
		foreach ($row as $field => $val) {
			if (!in_array($field, $allowedFields)) {
				unset($row[$field]);
			}
		}
		return $row;
	}
	

	/**
	 * Removes fields from a field list that are not configured in TCA
	 *
	 * @param	string		Table name
	 * @param	mixed		Field list as array or comma list as string
	 * @return	array		Cleaned field list as array
	 */
	function cleanupFieldList($table, $fields) {
		$allowedFields = $this->getTCAFieldListArray($table);
		$fields = is_array($fields) ? $fields : t3lib_div::trimExplode(',', $fields, 1);

		foreach ($fields as $key => $field) {
			if (!in_array($field, $allowedFields)) {
				unset($fields[$key]);
			}
		}
		return $fields;
	}
	

	/**
	 * Returns an array of fields which are configured in TCA for a table.
	 * This includes uid, pid, and ctrl fields.
	 *
	 * @param	string		Table name
	 * @param	boolean		If true not all fields from the TCA columns-array will be used but the ones from the ctrl-array
	 * @param	array		Field list array which should be appended to the list
	 * @return	array		Field list array
	 */
	function getTCAFieldListArray($table, $mainFieldsOnly=FALSE, $addFields=array())	{
		global $TCA;

		$fieldListArr=array();

		if (!is_array($addFields)) {
			$addFields = t3lib_div::trimExplode(';', $addFields, 1);
		}
		foreach ($addFields as $field)	{
			#if ($TCA[$table]['columns'][$field]) {
				$fieldListArr[$field] = $field;
			#}
		}

		if (is_array($TCA[$table]))	{
			t3lib_div::loadTCA($table);
			if (!$mainFieldsOnly) {
				foreach($TCA[$table]['columns'] as $fieldName => $dummy)	{
					$fieldListArr[$fieldName] = $fieldName;
				}
			}
			$fieldListArr['uid'] = 'uid';
			$fieldListArr['pid'] = 'pid';

			$ctrlFields = array ('label','label_alt','type','typeicon_column','tstamp','crdate','cruser_id','sortby','delete','fe_cruser_id','fe_crgroup_id');
			foreach ($ctrlFields as $field)	{
				if ($TCA[$table]['ctrl'][$field]) {
					$subFields = t3lib_div::trimExplode(',',$TCA[$table]['ctrl'][$field],1);
					foreach ($subFields as $subField)	{
						$fieldListArr[$subField] = $subField;
					}
				}
			}

			if (is_array($TCA[$table]['ctrl']['enablecolumns'])) {
				foreach ($TCA[$table]['ctrl']['enablecolumns'] as $field)	{
					if ($field) {
						$fieldListArr[$field] = $field;
					}
				}
			}
		}
		return $fieldListArr;
	}


	/**
	 * Creates and executes a SELECT query, selecting fields ($select) from two/three tables joined
	 * Use $mm_table together with $local_table or $foreign_table to select over two tables. Or use all three tables to select the full MM-relation.
	 * The JOIN is done with [$local_table].uid <--> [$mm_table].uid_local  / [$mm_table].uid_foreign <--> [$foreign_table].uid
	 * The function is very useful for selecting MM-relations between tables adhering to the MM-format used by TCE (TYPO3 Core Engine). See the section on $TCA in Inside TYPO3 for more details.
	 *
	 * Usage: 12 (spec. ext. sys_action, sys_messages, sys_todos)
	 *
	 * @param	string		Field list for SELECT
	 * @param	string		Tablename, local table
	 * @param	string		Tablename, local table alias
	 * @param	string		Tablename, relation table
	 * @param	string		Tablename, relation table alias
	 * @param	string		Tablename, foreign table
	 * @param	string		Tablename, foreign table alias
	 * @param	string		Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	pointer		MySQL result pointer / DBAL object
	 * @see exec_SELECTquery()
	 */
	function exec_SELECT_mm_alias_query($select,$local_table,$local_table_alias, $mm_table, $mm_table_alias, $foreign_table, $foreign_table_alias,$whereClause='',$groupBy='',$orderBy='',$limit='')	{
		
		$where_local_table = $local_table_alias ? $local_table_alias : $local_table;
		$where_mm_table = $mm_table_alias ? $mm_table_alias : $mm_table;
		$where_foreign_table = $foreign_table_alias ? $foreign_table_alias : $foreign_table;

		$mmWhere = $where_local_table ? $where_local_table.'.uid='.$where_mm_table.'.uid_local' : '';
		$mmWhere.= ($where_local_table AND $where_foreign_table) ? ' AND ' : '';
		$mmWhere.= $where_foreign_table ? $where_foreign_table.'.uid='.$where_mm_table.'.uid_foreign' : '';
		
		return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$select,
			($local_table ? $local_table . ($where_local_table ? ' '.$where_local_table: '' ) .',' : '').
			$mm_table.($mm_table_alias ? ' '.$mm_table_alias : '').
			($foreign_table ? ','.$foreign_table. ($foreign_table_alias?' '.$foreign_table_alias : '' ): ''),
			$mmWhere.' '.$whereClause,		// whereClauseMightContainGroupOrderBy
			$groupBy,
			$orderBy,
			$limit
		);
	}



}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_leafdata.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/graytree/lib/class.tx_graytree_leafdata.php']);
}


?>
