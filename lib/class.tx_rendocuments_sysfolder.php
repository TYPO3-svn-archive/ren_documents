<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 DSIT Ville de Rennes <dsit@ville-rennes.fr>
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
/*
 * Original author:	Rene Fritz <r.fritz@colorcube.de>
 * Original package: DAM-Core
 * Original subpackage: Lib
 *
 * Modified by Pierrick Caillon <pierrick@in-cite.net>
 * to be included in ren_documents to make the same sysfolder feature as DAM for the Documents repository.
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */




/**
 * Documents sysfolder functions
 * A sysfolder is used for Documents record storage. The sysfolder will be created automatically.
 * In principle it could be possible to use more than one sysfolder. But that concept is not easy to handle and therefore not used yet.
 *
 * @origauthor	Rene Fritz <r.fritz@colorcube.de>
 * @origpackage DAM-Core
 * @origsubpackage Lib
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_rendocuments_sysfolder {

	protected static $pid = 0;
	
	/**
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @desc Récupérer l'identifiant du dossier de stockage des documents.
	 * @return integer L'identifiant de la page.
	 */
	static function getPid() {
		if (!tx_rendocuments_sysfolder::$pid)
			tx_rendocuments_sysfolder::$pid = tx_rendocuments_sysfolder::init();
		return tx_rendocuments_sysfolder::$pid;
	}

	/**
	 * Find the documents folders or create one.
	 *
	 * @return	integer		The uid of the default sysfolder
	 */
	static function init()	{

		if (!is_object($GLOBALS['TYPO3_DB'])) return false;

		$aDocFolders = tx_rendocuments_sysfolder::getAvailable();
		if (!count($aDocFolders)) {
				// creates a DAM folder on the fly
			tx_rendocuments_sysfolder::create();
			$aDocFolders = tx_rendocuments_sysfolder::getAvailable();
		}
		$aDF = current($aDocFolders);

		return $aDF['uid'];
	}


	/**
	 * Find the documents folders and return an array of record arrays.
	 *
	 * @return	array		Array of rows of found documents folders with fields: uid, pid, title. An empty array will be returned if no folder was found.
	 */
	static function getAvailable() {
		$aRows = array();
		if ($aDocFolders = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,pid,title,doktype', 'pages', 'module='.$GLOBALS['TYPO3_DB']->fullQuoteStr('ren_docs', 'pages').' AND deleted=0', '', '', '', 'uid')) {
			$aRows = $aDocFolders;
		}
		return $aRows;
	}


	/**
	 * Returns pidList/list of pages uid's of documentss Folders
	 *
	 * @return	string		Commalist of PID's
	 */
	static function getPidList() {
		return implode(',',array_keys(tx_rendocuments_sysfolder::getAvailable()));
	}


	/**
	 * Create a document folders
	 *
	 * @param	integer		$pid The PID of the sysfolder which is by default 0 to place the folder in the root.
	 * @return	void
	 */
	static function create($pid=0) {
		$fields_values = array();
		$fields_values['pid'] = $pid;
		$fields_values['sorting'] = 29999;
		$fields_values['perms_user'] = 31;
		$fields_values['perms_group'] = 31;
		$fields_values['perms_everybody'] = 31;
		$fields_values['title'] = 'Documents';
		$fields_values['doktype'] = 254; // sysfolder
		$fields_values['module'] = 'ren_docs';
		$fields_values['crdate'] = time();
		$fields_values['tstamp'] = time();
		return $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $fields_values);
	}


	/**
	 * Move lost DAM records to the DAM sysfolder.
	 * This is a maintance function.
	 *
	 * @param	integer		$pid If set this PID will be used as storage sysfolder for the lost folder.
	 * @param	boolean		$forceAll If true (default) all DAM records will be moved not only the ony with pid=0.
	 * @return	void
	 */
	/*function collectLostRecords($pid=NULL, $forceAll=true)	{

		$pid = $pid ? $pid : tx_dam_db::getPid();

		if ($pid) {

			$mediaTables = tx_dam::register_getEntries('mediaTable');
			$values = array ('pid' => $pid);

			if($forceAll) {
				foreach ($mediaTables as $table) {
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $table.'.pid NOT IN ('.tx_rendocuments_sysfolder::getPidList().')', $values);
				}
			} else {
				foreach ($mediaTables as $table) {
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $table.'.pid=0', $values);
				}
			}
		}
	}*/
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_documents/lib/class.tx_rendocuments_sysfolder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_documents/lib/class.tx_rendocuments_sysfolder.php']);
}

?>