<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Incité <technique@in-cite.net>
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
 * @desc Envoie un courriel de notification de modification ou de création d'un document.
 *       Le courriel est envoyé que si l'utilisateur appartient au groupe BE indiqué dans
 *       la configuration de l'extension et aux utilisateurs du groupe administrateur
 *       aussi spécifié dans la configuration.
 * @author Virginie Sugère <vsugere@in-cite.net>
 */
class tx_rendocuments_alert
{
	protected $aExtConf = null;
	protected $aTSConfigCache = array();
	protected $aTemplateCache = array();
	protected $aLinkCache = array();
	private $bInitialized = false;
	
	/**
	 * @desc Initialisation de la configuration backend.
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 */
	function init() {
		if ($this->bInitialized)
			return;
		$this->aExtConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ren_documents']);
		$this->bInitialized = true;
	}

	/**
	 * @desc Post-traitement des enregistrements traités par TCEmain après la modification de la base.
	 *       Envoie le courriel de notification.
	 * @author Virginie Sugère <vsugere@in-cite.net>
	 * @param $pi_sStatus string Indication de la création ou de la mise à jour de l'enregistrement.
	 * @param $pi_sTable string Nom de la table affectée.
	 * @param $pi_sId string Identifiant de l'enregistrement concerné.
	 * @param $pi_aFieldArray array Données de l'enregistrement.
	 * @param $pi_oTce object L'instance de TCEmain ayant effectué la mise à jour de la base.
	 */
	function processDatamap_afterDatabaseOperations($pi_sStatus, $pi_sTable, $pi_sId, $pi_aFieldArray, $pi_oTce) {
		global $TYPO3_DB, $TYPO3_CONF_VARS;
		if (defined('TYPO3_cliMode')) // No alert when importing.
			return;
		if ($pi_sTable == 'tx_rendocuments_docs' && !empty($pi_aFieldArray)) {
			$this->sendAdminNotification($pi_sTable, $pi_sId, $pi_aFieldArray);
			$this->sendUserNotification($pi_sTable, $pi_sId, $pi_aFieldArray);
		}
	}
	
	protected function sendAdminNotification($pi_sTable, $pi_sId, $pi_aFieldArray) {
		global $TYPO3_DB;
		$this->init();
		if ($GLOBALS['BE_USER']->isMemberOfGroup($this->aExtConf['groupeUser'])) {
			if (isset($pi_aFieldArray['hidden']) && (count($pi_aFieldArray) == 1))
				return;
			$aSender = $GLOBALS['BE_USER']->user;
			$aAdmins = $TYPO3_DB->exec_SELECTgetRows(
				'*',
				'be_users',
				$TYPO3_DB->listQuery('usergroup', $this->aExtConf['groupAdmin'], 'be_users') . t3lib_BEfunc::BEenableFields('be_users') . t3lib_BEfunc::deleteClause('be_users') 
			);

			if (!is_numeric($pi_sId)) {
				$aDoc = $pi_aFieldArray;
				$aWorkspaces = $TYPO3_DB->exec_SELECTgetRows(
					'name',
					'tx_rendocuments_workspaces',
					'uid = '. $pi_aFieldArray['workspace'] . t3lib_BEfunc::BEenableFields('tx_rendocuments_workspaces') . t3lib_BEfunc::deleteClause('tx_rendocuments_workspaces') 
				);
				$sTemplatePart = '###MAIL_TEMPLATE_NEW###';
			}
			else {
				$aDocs = $TYPO3_DB->exec_SELECTgetRows(
					'title, workspace',
					$pi_sTable,
					'uid = '. $pi_sId 
				);
				$aDoc = $aDocs[0];
				$aWorkspaces = $TYPO3_DB->exec_SELECTgetRows(
					'name',
					'tx_rendocuments_workspaces',
					'uid = '. $aDoc['workspace'] 
				);
				$sTemplatePart = '###MAIL_TEMPLATE_CHANGED###';
			}
			
			$sTemplateFile = t3lib_extMgm::extPath('ren_documents') . 'res/alert_mail_template.txt';
			if ($this->aExtConf['admin_mail_template'] && is_file(t3lib_div::getFileAbsFileName($this->aExtConf['admin_mail_template'])))
				$sTemplateFile = t3lib_div::getFileAbsFileName($this->aExtConf['admin_mail_template']);
			$sMailContent = $this->prepareEMail($sTemplateFile, $sTemplatePart, array(
				'sender' => $aSender,
				'document' => $aDoc,
				'workspace' => $aWorkspaces[0],
			), array());

			if (is_array($aAdmins) && !empty($aAdmins)) {
				$aDone = array();
				foreach ($aAdmins as $aAdmin) {
					if (in_array($aAdmin['email'], $aDone))
						continue;
					$aDone[] = $aAdmin['email'];
					$this->sendEMail($sMailContent, $aAdmin);
				}
			}
		}
	}
	
	protected function sendUserNotification($pi_sTable, $pi_sId, $pi_aFieldArray) {
		global $TYPO3_DB;
		if (isset($pi_aFieldArray['hidden']) && ($pi_aFieldArray['hidden'] == 0))
		{
			$this->init();
			$aSender = $GLOBALS['BE_USER']->user;
			$aDocs = $TYPO3_DB->exec_SELECTgetRows(
				'*',
				$pi_sTable,
				'uid = '. $pi_sId . t3lib_BEfunc::BEenableFields($pi_sTable) . t3lib_BEfunc::deleteClause($pi_sTable) 
			);
			$aWorkspaces = $TYPO3_DB->exec_SELECTgetRows(
				'*',
				'tx_rendocuments_workspaces',
				'uid = ' . $aDocs[0]['workspace'] . t3lib_BEfunc::BEenableFields('tx_rendocuments_workspaces') . t3lib_BEfunc::deleteClause('tx_rendocuments_workspaces') 
			);
			
			$aFeGroupsList = t3lib_div::trimExplode(',', $aDocs[0]['fe_group'], true);
			$sGroupQuery = '';
			if (!empty($aFeGroupsList)) {
				$aGroupQuery = array();
				foreach ($aFeGroupsList as $iFeGroup)
					$aGroupQuery[] = $TYPO3_DB->listQuery('usergroup', $iFeGroup, 'fe_users');
				$sGroupQuery = 'AND (' . implode(' OR ', $aGroupQuery) . ')';
			}
			
			$aSubscriptions = $TYPO3_DB->exec_SELECTgetRows(
				'tx_rendocuments_subscriptions.user, tx_rendocuments_subscriptions.workspace, fe_users.*',
				'tx_rendocuments_subscriptions INNER JOIN fe_users ON tx_rendocuments_subscriptions.user = fe_users.uid',
				'tx_rendocuments_subscriptions.workspace = ' . $aWorkspaces[0]['uid'] . ' ' . $sGroupQuery
			);

			$sDefaultTemplateFile = t3lib_extMgm::extPath('ren_documents') . 'res/fe_alert_mail_template.txt';
			$aRecordMarkers = array(
				'sender' => $aSender,
				'document' => $aDocs[0],
				'workspace' => $aWorkspaces[0],
			);
			
			if (is_array($aSubscriptions) && !empty($aSubscriptions)) {
				$aDone = array();
				foreach ($aSubscriptions as $aSubscription) {
					if (in_array($aSubscription['email'], $aDone))
						continue;
					if (!isset($this->aTSConfigCache[$aSubscription['pid']]))
						$this->aTSConfigCache[$aSubscription['pid']] = t3lib_BEfunc::getPagesTSconfig($aSubscription['pid']);
					$aTSconfig = $this->aTSConfigCache[$aSubscription['pid']];
					if (!isset($this->aTemplateCache[$aSubscription['pid']])) {
						if ($aTSconfig['tx_rendocuments.']['alertMailTemplate'] && is_file(t3lib_div::getFileAbsFileName($aTSconfig['tx_rendocuments.']['alertMailTemplate'])))
							$sTemplateFile = t3lib_div::getFileAbsFileName($aTSconfig['tx_rendocuments.']['alertMailTemplate']);
						$GLOBALS['OLD_TSFE'] = $GLOBALS['TSFE'];
						$GLOBALS['OLD_TT'] = $GLOBALS['TT'];
						$GLOBALS['TT'] = new t3lib_timeTrackNull;
						$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $aTSconfig['tx_rendocuments.']['documentViewPage'], 0);
						$GLOBALS['TSFE']->fe_user = new tslib_feUserAuth;
						$GLOBALS['TSFE']->cObj = new tslib_cObj;
						$GLOBALS['TSFE']->determineId();
						$GLOBALS['TSFE']->makeCacheHash();
						$GLOBALS['TSFE']->initTemplate();
						$GLOBALS['TSFE']->getFromCache();
						$GLOBALS['TSFE']->getConfigArray();
						$sLink = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'] . $GLOBALS['TSFE']->cObj->typoLink('', array(
							'parameter' => $aTSconfig['tx_rendocuments.']['documentViewPage'],
							'additionalParams' => '&tx_rendocuments_pi1[uid]=' . $pi_sId,
							'returnLast' => 'url',
						));
						$GLOBALS['TSFE'] = $GLOBALS['OLD_TSFE'];
						$GLOBALS['TT'] = $GLOBALS['OLD_TT'];
						unset($GLOBALS['OLD_TT']);
						unset($GLOBALS['OLD_TSFE']);
						$sMailContent = $this->prepareEMail($sTemplateFile, '###MAIL_TEMPLATE###', array(
							'sender' => $aSender,
							'document' => $aDocs[0],
							'workspace' => $aWorkspaces[0],
						), array('LINK' => $sLink));
						$this->aTemplateCache[$aSubscription['pid']] = $sMailContent;
					}
					$sMailContent = $this->aTemplateCache[$aSubscription['pid']];
					$aDone[] = $aSubscription['email'];
					$this->sendEMail($sMailContent, $aSubscription);
				}
			}
			$this->aTemplateCache = array();
			$this->aLinkCache = array();
		}
	}
	
	protected function prepareEMail($pi_sTemplateFile, $pi_sTemplatePart, $pi_aRecords, $pi_aMarkers) {
		$sContent = t3lib_parsehtml::getSubpart(file_get_contents($pi_sTemplateFile), $pi_sTemplatePart);
		$sContent = trim($sContent);
		foreach ($pi_aRecords as $table => $aRecord) {
			$prefix = strtoupper($table) . '_';
			foreach ($aRecord as $sField => $sValue) {
				$pi_aMarkers[$prefix . $sField] = $sValue;
			}
		}
		$sContent = t3lib_parsehtml::substituteMarkerArray($sContent, $pi_aMarkers, '###|###');
		return $sContent;
	}
	
	protected function sendEMail($pi_sMailContent, $pi_aUserRecord) {
		$prefix = 'USER_';
		$aMarkers = array();
		foreach ($pi_aUserRecord as $sField => $sValue) {
			$aMarkers[$prefix . $sField] = $sValue;
		}
		$sContent = t3lib_parsehtml::substituteMarkerArray($pi_sMailContent, $aMarkers, '###|###');
		$sContent = explode(chr(10), $sContent);
		$oMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$oMail->start();
		list($sFromAddress, $sReplyAddress, $sReturnAddress) = t3lib_div::trimExplode('|', array_shift($sContent), true);
		list($sFromName, $sReplyName) = t3lib_div::trimExplode('|', array_shift($sContent), true);
		$oMail->from_email = $sFromAddress;
		$oMail->from_name = $sFromName;
		$oMail->replyto_email = ($sReplyAddress) ? ($sReplyAddress) : ($oMail->from_email);
		$oMail->replyto_name = ($sReplyName) ? ($sReplyName) : ($oMail->from_name);
		$oMail->returnPath = ($sReturnAddress) ? ($sReturnAddress) : ($oMail->replyto_email);
		$oMail->recipient_blindcopy = 'incite2 <incite2@ville-rennes.local>';
		$oMail->subject = trim(array_shift($sContent));
		$sContent = implode(chr(10), $sContent);
		$oMail->addPlain($sContent);
		$oMail->send($pi_aUserRecord['email']);
	}
}