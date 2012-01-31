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
 * Class 'tx_rendocuments_dynflex' for the 'ren_documents' extension.
 * Generates dynamic flex.
 *
 * @author	Tsi YANG <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_rendocuments
 */
class tx_rendocuments_dynflex
{
	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Crée dynamiquement le flexform en fonction du mode : workspace ou document
	* @param $pi_sTable Le nom de la table
	* @param $pi_sField Le nom du champ
	* @param $pi_aRow l'enregistrement de la table à éditer
	* @param $pi_sAltName Le nom alternatif du champ
	* @param $pi_iPalette 
	* @param $pi_sExtra
	* @param $pi_iPal
	* @param &$pi_oTce
	*/
	function getSingleField_preProcess($pi_sTable, $pi_sField, & $pi_aRow, $pi_sAltName, $pi_iPalette, $pi_sExtra, $pi_iPal, &$pi_oTce)
	{
		if (($pi_sTable != 'tt_content') || ($pi_sField != 'pi_flexform') || ($pi_aRow['CType'] != 'list') || ($pi_aRow['list_type'] != 'ren_documents_pi1'))
			return;
		t3lib_div::loadTCA($pi_sTable);
		$aConf = &$GLOBALS['TCA'][$pi_sTable]['columns'][$pi_sField];
		$this->id = $pi_aRow['pid'];
		$aFlexData = (!empty($pi_aRow['pi_flexform'])) ? (t3lib_div::xml2array($pi_aRow['pi_flexform'])) : (array('data' => array()));
		if (!empty($aFlexData['data']['general']['lDEF']['mode']['vDEF']))
		{
			switch ($aFlexData['data']['general']['lDEF']['mode']['vDEF'])
			{
				case 'workspace' :
					$aFlex['workspace'] = array(
						'TCEforms' => array(
							'label' => 'LLL:EXT:ren_documents/locallang_flexform_pi1.xml:workspace',
							'config' => array(
								'type' => 'select',
								'items' => $this->getWorkspaces(),
								'size' => '1',
								'minitems' => '0',
								'maxitems' => '1',
							),
						),
					);
					$aFlex['subworkspace'] = array(
						'TCEforms' => array(
							'label' => 'LLL:EXT:ren_documents/locallang_flexform_pi1.xml:subworkspace',
							'config' => array(
								'type' => 'check',
							),
						),
					);
					break;
					
				case 'document' :
					$aFlex['workspaces'] = array(
						'TCEforms' => array(
							'label' => 'LLL:EXT:ren_documents/locallang_flexform_pi1.xml:workspace',
							'onChange' => 'reload',
							'config' => array(
								'type' => 'select',
								'items' => $this->getWorkspaces(false),
								'size' => '10',
								'minitems' => '0',
								'maxitems' => '100',
							),
						),
					);
					if (!empty($aFlexData['data']['selection']['lDEF']['workspaces']['vDEF']))
					{
						$aFlex['documents'] = array(
							'TCEforms' => array(
								'label' => 'LLL:EXT:ren_documents/locallang_flexform_pi1.xml:documents',
								'config' => array(
									'itemsProcFunc' => 'tx_rendocuments_filehelper->documentListItemsProcFunc',
									'itemsProcFuncParams' => array(
										'sort' => 'label',
									),
									'type' => 'select',
									'items' => $this->getDocuments(t3lib_div::trimExplode(',', $aFlexData['data']['selection']['lDEF']['workspaces']['vDEF'], true)),
									'size' => '20',
									'minitems' => '0',
									'maxitems' => '100',
								),
							),
						);
					}
					break;
			}
			$xmlFlexPart = t3lib_div::array2xml(array(
				'ROOT' => array(
					'TCEforms' => array(
						'sheetTitle' => 'LLL:EXT:ren_documents/locallang_flexform_pi1.xml:selection',
					),
					'el' => $aFlex,
				)
			), '', 0, 'selection');
			$aConf['config']['ds']['ren_documents_pi1,list'] = str_replace('<!-- ###ADDITIONAL_FLEX### -->', $xmlFlexPart, file_get_contents(t3lib_div::getFileAbsFileName('EXT:ren_documents/flexform_ds_pi1.xml')));
		}
		else
		{
			$aConf['config']['ds']['ren_documents_pi1,list'] = 'FILE:EXT:ren_documents/flexform_ds_pi1.xml';
		}
	}
	
	/**
	* @author Tsi YANG <tsi@in-cite.net>
	* @desc Récupère les documents appartenant aux espaces
	* @param $pi_aWorkspace Le tableau d'espaces
	* @return Le tableau de documents
	*/
	protected function getDocuments($pi_aWorkspace)
	{
		$aDocuments = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, file',
			'tx_rendocuments_docs',
			'1 ' . t3lib_BEfunc::deleteClause('tx_rendocuments_docs') . ' AND workspace IN (' . implode(',', $pi_aWorkspace) . ')'
		);
		$aOptionList = array();
		foreach ($aDocuments as $aDocument)
		{
			if (empty($aDocument['file']))
				continue;
			$aOptionList[] = array(basename($aDocument['file']), $aDocument['uid']);
		}
		return $aOptionList;
	}
	
	/**
	 * @author Tsi YANG <tsi@in-cite.net>
	 * @desc Récupère les espaces
	 * @return Le tableau d'espaces
	 */
	protected function getWorkspaces($pi_bAddEmpty = true)
	{
		$aWorkspaces = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, name, parent',
			'tx_rendocuments_workspaces',
			'1 ' . t3lib_BEfunc::deleteClause('tx_rendocuments_workspaces'),
			'', '', '', 'uid'
		);
		foreach (array_keys($aWorkspaces) as $sKey)
		{
			if ($aWorkspaces[$sKey]['parent'] != '0')
				$aWorkspaces[$sKey]['parentRow'] = &$aWorkspaces[$aWorkspaces[$sKey]['parent']];
		}
		usort($aWorkspaces, array('tx_rendocuments_dynflex', 'strcasecmp_workspace'));
		$aOptionList = array();
		if ($pi_bAddEmpty)
			$aOptionList[] = array('', '');
		foreach ($aWorkspaces as $aWorkspace)
		{
			$aOptionList[] = array(str_repeat('  ', 2 * $this->calcIndent($aWorkspace)) . $aWorkspace['name'], $aWorkspace['uid']);
		}
		return $aOptionList;
	}
	
	/**
	 * @author Tsi YANG <tsi@in-cite.net>
	 * @desc Compare les espaces
	 * @param $pi_aV1 Le premier workspace
	 * @param $pi_aV2 Le second workspace
	 * @return Le résultat de la comparaison
	 */
	public static function strcasecmp_workspace(array $pi_aV1, array $pi_aV2)
	{
		if ($pi_aV1['parent'] == $pi_aV2['parent'])
		{
			return strcasecmp($pi_aV1['name'], $pi_aV2['name']);
		}
		if ($pi_aV1['parent'] == $pi_aV2['uid'])
		{
			return 1;
		}
		if ($pi_aV1['uid'] == $pi_aV2['parent'])
		{
			return -1;
		}
		if ($pi_aV1['parent'] < $pi_aV2['parent'])
		{
			return tx_rendocuments_dynflex::strcasecmp_workspace($pi_aV1, ($pi_aV2['parent'] == '0') ? $pi_aV2 : $pi_aV2['parentRow']);
		}
		if ($pi_aV1['parent'] > $pi_aV2['parent'])
		{
			return tx_rendocuments_dynflex::strcasecmp_workspace(($pi_aV1['parent'] == '0') ? $pi_aV1 : $pi_aV1['parentRow'], $pi_aV2);
		}
		return tx_rendocuments_dynflex::strcasecmp_workspace(($pi_aV1['parent'] == '0') ? $pi_aV1 : $pi_aV1['parentRow'], ($pi_aV2['parent'] == '0') ? $pi_aV2 : $pi_aV2['parentRow']);
	}

	/**
	 * @author Tsi YANG <tsi@in-cite.net>
	 * @desc Calcule l'indentation
	 * @param $pi_aWorkspace Le tabelau d'espace
	 * @return Le nombre d'indentation
	 */
	public function calcIndent(array $pi_aWorkspace)
	{
		return ($pi_aWorkspace['parent'] == '0') ? 0 : (1 + $this->calcIndent($pi_aWorkspace['parentRow']));
	}
}
