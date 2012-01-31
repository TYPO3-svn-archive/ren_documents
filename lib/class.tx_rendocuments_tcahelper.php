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

/**
 * @desc Ensemble de méthodes d'aide au rendu des contrôles d'édition des enregistrement.
 *
 * @author	Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_rendocuments_tcahelper
{
	/**
	 * Replaces any dynamic markers in a SQL statement.
	 *
	 * @param	string		The SQL statement with dynamic markers.
	 * @param	string		Name of the table.
	 * @param	array		row from table.
	 * @return	string		SQL query with dynamic markers subsituted.
	 * @remarks Code originel importé de l'extension static_info_tables
	 */
	static function replaceMarkersInSQL ($sql, $table, $row)	{

		$TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig($table, $row);

		/* Replace references to specific fields with value of that field */
		if (strstr($sql,'###REC_FIELD_'))	{
			$sql_parts = explode('###REC_FIELD_',$sql);
			while(list($kk,$vv)=each($sql_parts))	{
				if ($kk)	{
					$sql_subpart = explode('###',$vv,2);
					$sql_parts[$kk]=$TSconfig['_THIS_ROW'][$sql_subpart[0]].$sql_subpart[1];
				}
			}
			$sql = implode('',$sql_parts);
		}

		/* Replace markers with TSConfig values */
		$sql = str_replace('###THIS_UID###',intval($TSconfig['_THIS_UID']),$sql);
		$sql = str_replace('###THIS_CID###',intval($TSconfig['_THIS_CID']),$sql);
		$sql = str_replace('###SITEROOT###',intval($TSconfig['_SITEROOT']),$sql);
		$sql = str_replace('###PAGE_TSCONFIG_ID###',intval($TSconfig[$field]['PAGE_TSCONFIG_ID']),$sql);
		$sql = str_replace('###PAGE_TSCONFIG_IDLIST###',$GLOBALS['TYPO3_DB']->cleanIntList($TSconfig[$field]['PAGE_TSCONFIG_IDLIST']),$sql);
		$sql = str_replace('###PAGE_TSCONFIG_STR###',$GLOBALS['TYPO3_DB']->quoteStr($TSconfig[$field]['PAGE_TSCONFIG_STR'], $table),$sql);
		$sql = str_replace('###CURRENT_PID###', intval($TSconfig['_CURRENT_PID']), $sql);

		return $sql;
	}


	/**
	 * Function to use in own TCA definitions
	 * Adds additional select items
	 *
	 * 			items		reference to the array of items (label,value,icon)
	 * 			config		The config array for the field.
	 * 			TSconfig	The "itemsProcFunc." from fieldTSconfig of the field.
	 * 			table		Table name
	 * 			row		Record row
	 * 			field		Field name
	 *
	 * @param	array		itemsProcFunc data array:
	 * @return	void		The $items array may have been modified
	 * @remarks Code originel importé de l'extension static_info_tables
	 */
	static function selectItemsTCA ($params) {
		global $TCA;

		$where = '';
		$config = &$params['config'];
		$table = $config['itemsProcFunc.']['table'];
		$tcaWhere = $config['itemsProcFunc.']['where'];
		if ($tcaWhere)	{
			$where = self::replaceMarkersInSQL($tcaWhere, $params['table'], $params['row']);
		}

		if ($table) {
			$indexField = $config['itemsProcFunc.']['indexField'];
			$indexField = $indexField ? $indexField : 'uid';
			$titleField = $config['itemsProcFunc.']['titleField'];
			$titleField = $titleField ? $titleField : 'uid';
			$orderField = $config['itemsProcFunc.']['orderField'];
			$orderField = $orderField ? $orderField : $titleField;

			$fields = $table.'.'.$indexField.',' . $table . '.' . $titleField;
			
			if ($config['itemsProcFunc.']['beAccessField']) {
				if ($GLOBALS['BE_USER']->isAdmin())
					$sGroupLookup = '1';
				else
				{
					$aGroupLookup = array();
					$aGroupLookup[] = $config['itemsProcFunc.']['beAccessField'] . ' = \'\'';
					foreach ($GLOBALS['BE_USER']->userGroupsUID as $group)
						$aGroupLookup[] = $GLOBALS['TYPO3_DB']->listQuery($config['itemsProcFunc.']['beAccessField'], $group, $table);
					$sGroupLookup = implode(' OR ', $aGroupLookup);
				}
				$where .= ' AND ' . $sGroupLookup . ' ';
			}

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, '1=1 '.$where.t3lib_BEfunc::deleteClause($table), '', $orderField);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($row[$titleField]) {
					$params['items'][] = array($row[$titleField], $row[$indexField], '');
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
	}
}
