<?php

if (!defined('TYPO3_MOD_PATH'))
	define('TYPO3_MOD_PATH', '../typo3conf/ext/ren_documents/mod1/');
if (!isset($BACK_PATH))
	$BACK_PATH='../../../../typo3/';

	// DO NOT REMOVE OR CHANGE THESE 2 LINES:
$MCONF['name'] = 'txdamM1_txrendocumentsM1';
$MCONF['script'] = '_DISPATCH';
	
$MCONF['access'] = 'user,group';
$MCONF['navFrameScript'] = 'tx_rendocuments_navframe.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:ren_documents/mod1/locallang_mod.xml';
?>