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

require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'Documents' for the 'ren_documents' extension.
 *
 * @author	Tsi YANG <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_rendocuments
 */
class tx_rendocuments_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_rendocuments_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_rendocuments_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ren_documents';	// The extension key.
	
	protected $sTemplateFile = 'typo3conf/ext/ren_documents/res/template.html';	// Chemin du fichier de template
	protected $aListFields = array(
		'title',
		'author',
		'published',
	);
	
	protected $aDetailFields = array (
		'title',
		'author',
		'published',
		'state',
		'description',
		'file',
		'service',
		'themes',
		'keywords',
		'tstamp',
		'changedby',
		'workspace',
	);

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$sContent: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($pi_sContent, $pi_aConf)
	{
		$this->conf = $pi_aConf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->sContent = '';
		if ($this->initConf())
		{
			// HINT: Le passage en mode détail est conditionné uniquement par la présence du paramettre qui indiquera l'uid de l'élément à afficher.
			if (isset($this->piVars['uid']))
			{
				$this->sContent .= $this->renderDetail(intval($this->piVars['uid']));
			}
			else
			{
				if(isset($this->piVars['follow']))
					$this->followSpace();
					
				$this->sContent .= $this->renderList();
			}
		}
		return $this->pi_wrapInBaseClass($this->sContent);
	}
	
	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Initialise la configuration de l'extension
	* @return boolean true si l'initialisation s'est correctement effectuée, sinon false
	*/
	function initConf()
	{
		$this->pi_initPIflexForm();
		$sMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mode', 'general');
		if (!$sMode)
		{
			$sMode = $this->conf['mode'];
		}
		if (!$sMode)
		{
			$sMode = $this->piVars['mode'];
		}
		switch ($sMode)
		{
			case 'workspace':
				// Initialise l'espace
				$iWorkspace = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'workspace', 'selection'));
				if (!$iWorkspace)
				{
					$aWorkspace = t3lib_div::trimExplode(',', $this->conf['workspaces'], true); // NOTE: Renommer comme le champ flexform, il faut garder l'indication que la valeur est unique.
					$iWorkspace = $aWorkspace[0]; // NOTE: Permettre qu'il soit calculé. C'est le principe du TypoScript
				}
				if (!$iWorkspace)
				{
					$aWorkspace = t3lib_div::trimExplode(',', $this->piVars['workspaces'], true); // NOTE: Renommer comme le champ flexform, il faut garder l'indication que la valeur est unique.
					$iWorkspace =  $aWorkspace[0];
				}
				if ($iWorkspace)
				{
					$this->aWorkspace = array($iWorkspace);
					// Recherche les sous-espaces
					$iSubworkspace = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'subworkspace', 'selection')); // NOTE: Peut être trouver un meilleur nom ?
					if (!$iSubworkspace)
					{
						$iSubworkspace = intval($this->conf['subworkspace']); // NOTE: Je l'appelerais ['workspace.']['showDocumentsFromChildren']. C'est plus explicite.
					}
					if (!$iSubworkspace)
					{
						$iSubworkspace = intval($this->piVars['subworkspace']); // NOTE: Même chose pour le nom.
					}
					if ($iSubworkspace)
					{
						$aWorkspace = $this->aWorkspace;
						while (true)
						{
							$aResult = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
								'uid, parent',
								'tx_rendocuments_workspaces',
								'deleted = 0 AND (uid IN (' . implode(',', $aWorkspace) . ') OR parent IN (' . implode(',', $aWorkspace) . '))',
								'',
								'',
								'',
								'uid'
							);
							if (count($aWorkspace) == count($aResult))
							{
								break;
							}
							$aWorkspace = array_keys($aResult);
						}
						$this->aWorkspace = $aWorkspace;
					}
				}
				break;
			case 'document':
				// NOTE: Pas besoin de charger les espaces utilisés pour la sélection, ce n'est pas filtrant.
				// Initialise les espaces
				$this->aWorkspace = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'workspaces', 'selection'), true);
				if (empty($this->aWorkspace))
				{
					$this->aWorkspace = t3lib_div::trimExplode(',', $this->conf['workspaces'], true);
				}
				if (empty($this->aWorkspace))
				{
					$this->aWorkspace = t3lib_div::trimExplode(',', $this->piVars['workspaces'], true);
				}
				
				// Initialise les documents
				$this->aDocuments = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'documents', 'selection'), true);
				if (empty($this->aDocuments))
				{
					$this->aDocuments = t3lib_div::trimExplode(',', $this->conf['documents'], true);
				}
				// NOTE: Permettre de faire la liste en TypoScript, et qu'elle soit calculée.
				break;
			default:
				// HOOK add mode init conf
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['modeInitConf'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['modeInitConf'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$_procObj->modeInitConf($sMode, $this);
					}
				}
				break;
		}
		if (empty($this->aWorkspace) && empty($this->aDocuments) && empty($this->piVars['uid']))
		{
			$this->sContent .= '<p class="error">' . htmlspecialchars($this->pi_getLL('error.documents')) . '</p>';
			return false;
		}
		// Récupère les colonnes à afficher
		$aListFields = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listFields', 'general'), true);
		if (empty($aListFields))
		{
			$aListFields = t3lib_div::trimExplode(',', $this->conf['listFields'], true);
		}
		if (!empty($aListFields))
		{
			$this->aListFields = $aListFields;
		}
		$this->aHeadersId = array();
		foreach ($this->aListFields as $sField)
		{
			$this->aHeadersId[$sField] = uniqid($this->prefixId);
		}
		
		// Récupère le template
		$sTemplate = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template', 'general');
		if (!$sTemplate)
		{
			$sTemplate = $this->conf['template'];
		}
		if ($sTemplate)
		{
			$this->sTemplateFile = $sTemplate;
		}
		
		return true;
	}

	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Restitue les marqueurs du template de liste
	* @return string le rendu de la vue liste
	*/
	function renderList()
	{
		$sHtml = $this->cObj->fileResource($this->sTemplateFile);
		$sTemplate = $this->cObj->getSubpart($sHtml, '###TEMPLATE_LIST###');
		$aMarkers = array(
			'###TITLE###' => htmlspecialchars($this->pi_getLL('list_title')),
			'###CAPTION###' => htmlspecialchars($this->pi_getLL('list_caption')),
			'###UNIQID###' => uniqid($this->prefixId),
			'###SUBSCRIPTION###' => $this->renderLinkSubscription(),
		);
		$sHeaderItems = $this->renderListHeader($this->cObj->getSubpart($sTemplate, '###HEADER_ITEM###'));
		$sDocItems = $this->renderListRows($this->cObj->getSubpart($sTemplate, '###DOCUMENT_ITEM###'));		
		
		$sTemplate = $this->cObj->substituteSubpart($sTemplate, '###HEADER_ITEM###', $sHeaderItems);
		$sTemplate = $this->cObj->substituteSubpart($sTemplate, '###DOCUMENT_ITEM###', $sDocItems);
		$sContent .= $this->cObj->substituteMarkerArray($sTemplate, $aMarkers);
		return $sContent;
	}
	
	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Remplace les marqueurs du template de l'en-tête
	* @param string $pi_sTemplate le template des titres de colonnes à appliquer
	* @return string le rendu de l'en-tête
	*/
	function renderListHeader($pi_sTemplate)
	{
		foreach ($this->aListFields as $sField)
		{
			$aMarkers = array(
				'###HEADERID###' => $this->aHeadersId[$sField],
				'###HEADER###' => htmlspecialchars($this->pi_getLL('th_' . $sField)),
			);
			$sContent .= $this->cObj->substituteMarkerArray($pi_sTemplate, $aMarkers);
		}
		return $sContent;
	}

	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Remplace les marqueurs des lignes
	* @param string $pi_sTemplate le template des lignes à appliquer
	* @return string le rendu des enregistrements
	*/
	function renderListRows($pi_sTemplate)
	{
		$sTable = 'tx_rendocuments_docs';
		$aListFields = $this->aListFields;
		$aListFields[] = 'uid';
		// NOTE: On génère les conditions et après on mets le enableFields, ainsi, il n'y a pas besoin du '1 ', ni de faire en tableau, les cas devenant exclusifs (cf commentaire au dessus).
		$aWhere = array();
		$aWhere[] = '1' . $this->cObj->enableFields($sTable);
		if (!empty($this->aWorkspace))
		{
			$aWhere[] = 'workspace IN (' . implode(',', $this->aWorkspace) . ')';
		}
		if (!empty($this->aDocuments))
		{
			$aWhere[] = 'uid IN (' . implode(',', $this->aDocuments) . ')';
		}
		// $aDocs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			// implode(',', $aListFields),
			// $sTable,
			// implode(' AND ', $aWhere)
		// );
		$aDocs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			implode(',', $aListFields),
			$sTable,
			implode(' AND ', $aWhere),
			'',
			'',
			'',
			'uid'
		);
		if (is_array($this->aDocuments))	{
			foreach ($this->aDocuments as $iDocument)	{
				$aDoc = $aDocs[$iDocument];
				if (is_array($aDoc))	{
					$sContent .= $this->cObj->substituteSubpart(
						$pi_sTemplate, '###DOCUMENT_VALUE_ITEM###', $this->renderListRow($this->cObj->getSubpart($pi_sTemplate, '###DOCUMENT_VALUE_ITEM###'), $aDoc)
					);
				}
			}
		}	else	{
			foreach ($aDocs as $aDoc)
			{
				$sContent .= $this->cObj->substituteSubpart(
					$pi_sTemplate, '###DOCUMENT_VALUE_ITEM###', $this->renderListRow($this->cObj->getSubpart($pi_sTemplate, '###DOCUMENT_VALUE_ITEM###'), $aDoc)
				);
			}
		}
		return $sContent;
	}
	
	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Remplace les marqueurs d'une ligne
	* @param string $pi_sTemplate le template des lignes à appliquer
	* @param array $pi_aDoc le tableau de données d'un document
	* @return string le rendu d'un enregistrement
	*/
	function renderListRow($pi_sTemplate, $pi_aDoc)
	{
		$sTable = 'tx_rendocuments_docs';
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->setParent($this->cObj->data, $this->cObj->currentRecord);
		$cObj->start($pi_aDoc, $sTable);
		foreach ($this->aListFields as $sField)
		{
			$aMarkers = array(
				'###HEADERID###' => $this->aHeadersId[$sField],
				'###DOCUMENT_VALUE###' => $cObj->stdWrap($pi_aDoc[$sField], $this->conf['list.'][$sField . '_stdWrap.']),
			);
			$sContent .= $this->cObj->substituteMarkerArray($pi_sTemplate, $aMarkers);
		}
		return $sContent;
	}
	
	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Restitue les marqueurs du template de détail d'un document
	* @param int $pi_iUid l'uid du document
	* @return string le rendu du détail d'un document
	*/
	function renderDetail($pi_iUid)
	{
		$sTable = 'tx_rendocuments_docs';
		$aDocs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$sTable,
			'uid = ' . intval($pi_iUid) . 
			$this->cObj->enableFields($sTable)
		);
		if (empty($aDocs))
			return 'Not authorized or non existant';
		$aDoc = $aDocs[0];
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($aDoc, $sTable);
		$cObj->setParent($this->cObj->data, $this->cObj->currentRecord);
		$sHtml = $this->cObj->fileResource($this->sTemplateFile);
		$sTemplate = $this->cObj->getSubpart($sHtml, '###TEMPLATE_DETAIL###');
		$aMarkers = array(
			'###HREF_PREVIOUS###' => htmlspecialchars(t3lib_div::linkThisUrl(t3lib_div::getIndpEnv('TYPO3_SITE_URL'), array('id' => $GLOBALS['TSFE']->id))), // NOTE: Le lien DOIT être généré avec une fonction basée sur tslib_cObj::typolink.
			'###PREVIOUS###' => $GLOBALS['TSFE']->sL('LLL:EXT:lang/locallang_common.xml:previous'),
		);//var_dump($aDoc, $sTable);
		foreach($this->aDetailFields as $sField)
		{
			$sValue = $aDoc[$sField];
			if ($sField == 'file')
				$sValue = basename($sValue);
			$aMarkers['###' . strtoupper($sField) . '###'] = htmlspecialchars($this->pi_getLL('detail_' . $sField));
			$aMarkers['###' . strtoupper($sField) . '_VALUE###'] = $cObj->stdWrap($sValue, $this->conf['detail.'][$sField . '_stdWrap.']);
		}
		$sContent .= $this->cObj->substituteMarkerArray(
			$sTemplate, $aMarkers
		);
		return $sContent;
	}
	
	/**
	* @author Pierrick Caillon <pierrick@in-cite.net>
	* @desc Génère le lien vers le document.
	* @param string $sContent Le contenu de départ (ignoré)
	* @param array $aConf La configuration de la génération du lien.
	* @return string Le lien généré.
	*/
	function buildLink($pi_sContent, $pi_aConf) {
		global $TYPO3_DB;
		$iDocument = $this->cObj->data['uid'];
		$aDocuments = $TYPO3_DB->exec_SELECTgetRows(
			'*',
			'tx_rendocuments_docs',
			'uid = ' . $iDocument . ' '. $this->cObj->enableFields('tx_rendocuments_docs')
		);
		$sDocument = basename($aDocuments[0]['file']);
		// if (strpos($sDocument, $iDocument . '-') === 0)
			// $sDocument = substr($sDocument, strlen($iDocument . '-'));
		$iWorkspace = $aDocuments[0]['workspace'];
		return $pi_aConf['prefix'] . $this->buildLinkVirtualPath($iWorkspace) . rawurlencode($sDocument);
	}
	
	/**
	* @author Pierrick Caillon <pierrick@in-cite.net>
	* @desc Génère le lien chemin virtuel des espaces.
	* @param integer $pi_iWorkspace L'identifiant de l'espace.
	* @param array $aConf La configuration de la génération du lien.
	* @return string Le lien généré.
	*/
	function buildLinkVirtualPath($pi_iWorkspace)
	{
		global $TYPO3_DB;
		$aWorkspaces = $TYPO3_DB->exec_SELECTgetRows(
			'name, parent',
			'tx_rendocuments_workspaces',
			'uid = ' . $pi_iWorkspace . ' '. $this->cObj->enableFields('tx_rendocuments_workspaces')
		);
		if ($aWorkspaces[0])
		{
			return rawurlencode($aWorkspaces[0]['name']) . '/' . ($aWorkspaces[0]['parent'] ? $this->buildLinkVirtualPath($aWorkspaces[0]['parent']) : '');
		}
		return '';
	}
	
	/**
	* @author Virginie Sugère <vsugere@in-cite.net>
	* @desc création du lien pour s'abonner ou se désabonner aux documents d'un espace
	* @return string le lien pour s'abonner ou se désabonner aux documents d'un espace
	*/
	function renderLinkSubscription()
	{
		$iSpace = implode(',', $this->aWorkspace);
		$aSubscriptions = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_rendocuments_subscriptions',
			'user = '.$GLOBALS['TSFE']->fe_user->user['uid'].' AND workspace = '.$iSpace.' '.$this->cObj->enableFields('tx_rendocuments_subscriptions')
		);
		
		if(empty($aSubscriptions)){
			$sLink = $this->pi_linkTP_keepPIvars(htmlspecialchars($this->pi_getLL('link_follow')), array('follow' => 1));
			//$sLink = htmlspecialchars(t3lib_div::linkThisUrl(t3lib_div::getIndpEnv('TYPO3_SITE_URL'), array('id' => $GLOBALS['TSFE']->id, 'follow' => 1)));
		}
		else{
			$sLink = $this->pi_linkTP_keepPIvars(htmlspecialchars($this->pi_getLL('link_no_follow')), array('follow' => 0));
		}
		
		return $sLink;
	}
	
	
	/**
	* @author Virginie Sugère <vsugere@in-cite.net>
	* @desc Ajoute ou supprime dans la base un abonnement aux alertes des documents
	*/
	function followSpace() 
	{
		$iSpace = implode(',', $this->aWorkspace);
		$sFollow = $this->piVars['follow'];
		$aSubscriptions = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_rendocuments_subscriptions',
			'user = '.$GLOBALS['TSFE']->fe_user->user['uid'].' AND workspace = '.$iSpace.' '.$this->cObj->enableFields('tx_rendocuments_subscriptions')
		);
		if($sFollow == 1)
		{
			if(empty($aSubscriptions))
			{
				$iStorage = $GLOBALS['TSFE']->getStorageSiterootPids();
				$aInsert = array(
					'pid' => $iStorage['_STORAGE_PID'],
					'user' => $GLOBALS['TSFE']->fe_user->user['uid'],
					'workspace' =>  $iSpace,
					'tstamp' =>  time(),
					'crdate' =>  time()
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_rendocuments_subscriptions',
					$aInsert
				);
			}
		}
		else
		{
			if(!empty($aSubscriptions))
			{
				$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tx_rendocuments_subscriptions',
					'user = '.$GLOBALS['TSFE']->fe_user->user['uid'].' AND workspace = '.$iSpace
				);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_documents/pi1/class.tx_rendocuments_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_documents/pi1/class.tx_rendocuments_pi1.php']);
}

?>