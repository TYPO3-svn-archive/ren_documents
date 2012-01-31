<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 DSIT Ville de Rennes <dsit@ville-rennes.fr>
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

/**
 * @desc Fourni dees fonction d'aide pour les fichiers dees documents
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_rendocuments_filehelper
{
	public function renderFilename($pi_sContent, $pi_aConf)
	{
		$sDocId = $this->cObj->data['uid'];
		return self::renderFilenameInternal($pi_sContent, intval($sDocId));
	}

	protected function renderFilenameInternal($pi_sFilename, $pi_iUid)
	{
		$sFilename = basename($pi_sFilename);
		if (preg_match('/^' . $pi_iUid . '-(.*)$/', $sFilename, $aMatches))
			$sFilename = $aMatches[1];
		return $sFilename;
	}

	public function documentListItemsProcFunc(& $pi_aParams, &$pi_oObj) {
		// $params=array();
		// $params['items'] = &$items;
		// $params['config'] = $config;
		// $params['TSconfig'] = $iArray;
		// $params['table'] = $table;
		// $params['row'] = $row;
		// $params['field'] = $field;
		// Columns: 1: label; 2:value; 3:icon; 4:description; 5:keyword
		if (isset($pi_aParams['config']['itemsProcFuncParams']['sort'])) {
			switch ($pi_aParams['config']['itemsProcFuncParams']['sort']) {
				case 'label':
					$iSortColumn = 0;
					break;
				case 'value':
					$iSortColumn = 1;
					break;
			}
		}
		foreach (array_keys($pi_aParams['items']) as $key) {
			$pi_aParams['items'][$key][0] = self::renderFilenameInternal($pi_aParams['items'][$key][0], $pi_aParams['items'][$key][1]);
		}
		if (isset($iSortColumn)) {
			switch ($iSortColumn) {
				case 0:
					$function = create_function('$v, $w', 'return strcasecmp($v[0], $w[0]);');
					break;
				case 1:
					$function = create_function('$v, $w', 'return intval($v[1]) < intval($w[1]);');
					break;
			}
			usort($pi_aParams['items'], $function);
		}
	}
}