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

// $TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = 1;
// $TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_alwaysAuthUser'] = 1;

/**
 * @file tx_rendocuments_download.php
 *
 * @author Tsi YANG <tsi@in-cite.net>
 * @desc Télécharge le document et incrémente le compteur de téléchargement
 *
 */
$TSFE = t3lib_div::makeInstance('tslib_fe', $TYPO3_CONF_VARS, 0, 0);
$TSFE->connectToDB();
$TSFE->includeTCA(false);
$TSFE->initFEuser();

if ($TSFE->fe_user->user['uid'])
{
	$TSFE->initUserGroups();
	$sUid = t3lib_div::_GP('uid');
	$sys_page = new t3lib_pageSelect();
	$sys_page->init(false);
	$sTable = 'tx_rendocuments_docs';
	$aDocuments = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
		'*',
		$sTable,
		'uid = ' . intval($sUid) . ' ' . 
		$sys_page->enableFields($sTable)
	);
	$aDocument = $aDocuments[0];
	$sFilepath = $aDocument['file'];
	if (empty($sFilepath))
	{
		//erreur 404
		header('HTTP/1.0 404 Not found');
		header('Content-Type: text/html; charset=utf-8');
		readfile(t3lib_extMgm::extPath('ren_documents') . 'res/404.html');
		echo '<!-- ' . __LINE__ . ' -->';
	}
	else
	{
		$aUpdateArray = array(
			'pid' => $aDocument['pid'],
			'tstamp' => time(),
			'crdate' => time(),
			'downloads' => ($aDocument['downloads'] + 1),				
		);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($sTable, 'uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $sTable), $aUpdateArray);
		$sFilepath = t3lib_div::getFileAbsFileName($sFilepath);
		if (file_exists($sFilepath) && is_file($sFilepath))
		{
			//envoi du header
			if (extension_loaded('mime_magic'))
			{
				$mimetype = mime_content_type($sFilepath);
			}
			elseif (extension_loaded('fileinfo'))
			{
				$finfo = finfo_open(FILEINFO_MIME); // Retourne le type mime à la extension mimetype
				$mimetype = finfo_file($finfo, $sFilepath) . "\n";
				finfo_close($finfo);
			}
			if (!isset($mimetype) || empty($mimetype))
			{
				$mimetype = 'application/octet-stream';
			}
			header('Content-Type: ' . $mimetype);
			header('Content-Disposition: attachment; filename="' . basename($sFilepath) . '";');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($sFilepath));
			readfile($sFilepath);
			exit;
		}
		else
		{
			//erreur 404
			header('HTTP/1.0 404 Not found');
			header('Content-Type: text/html; charset=utf-8');
			readfile(t3lib_extMgm::extPath('ren_documents') . 'res/404.html');
			echo '<!-- ' . __LINE__ . ' -->';
			exit;
		}
	}
}
else
{
	// Redirect to login page
	$TSFE->determineId();
	$pagesTSC = $TSFE->getPagesTSConfig($TSFE->id);
	if (isset($pagesTSC['tx_rendocuments.']['loginUrl'])) {
		$additionalParams = ((strpos($pagesTSC['tx_rendocuments.']['loginUrl'], '?') !== false) ? '&' : '?') . 'redirect_url=' . rawurlencode(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
		$path = t3lib_div::locationHeaderUrl($pagesTSC['tx_rendocuments.']['loginUrl'] . $additionalParams);
	}
	elseif (isset($pagesTSC['tx_ren_documents.']['loginPage'])) {
		$path = t3lib_div::locationHeaderUrl($TSFE->cObj->typoLink_URL(array('paramter' => $pagesTSC['tx_ren_documents.']['pidLogin'], 'additionalParams' => '&redirect_url=' . rawurlencode(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')))));
	}
	else {
		header('HTTP/1.0 403 Forbidden');
		header('Content-Type: text/html; charset=utf-8');
		readfile(t3lib_extMgm::extPath('ren_documents') . 'res/403.html');
		echo '<!-- ' . __LINE__ . ' -->';
		exit;
	}
	header('Location: ' .  $path);
}
