<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 DSIT Ville de Rennes <dsit@ville-rennes.fr>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

 class tx_rendocuments_tcedummy {
	public $enableLogging = true;
	public $bypassFileHandling = false;
	public $uploadedFileArray = array();
	public $errors = array();
	public $alternativeFileName = array();
	public $fileFunc = null;
 
	function __construct() {
	}
	
	function getRecordProperties($table, $id, $noWSOL = false) {
		return array('event_pid' => 0);
	}
	
	function log($table,$recuid,$action,$recpid,$error,$details,$details_nr=-1,$data=array(),$event_pid=-1,$NEWid='') {
		$errors[] = sprintf($details, $data[0], $data[1], $data[2], $data[3], $data[4]);
	}
 }