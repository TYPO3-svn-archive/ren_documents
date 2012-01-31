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
/*
 * Remarque : certaines parties du code proviennent du DAM. Notemment la fonction init(), main() et printContent() avec quelques modifications.
 * Le norme de codage n'est donc pas consistante pour indiquer les parties rajoutées.
 */

unset($MCONF);
include ('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:ren_documents/mod1/locallang.xml');

/**
 * Module 'Documents' for the 'ren_documents' extension.
 * 
 * @author	DSIT Ville de Rennes <dsit@ville-rennes.fr>
 * @package	TYPO3
 * @subpackage	tx_rendocuments
 */
class  tx_rendocuments_navframe {

	var $doc;
	var $content;

		// Internal, static: _GP
	var $currentSubScript;

	var $mainModule = 'txdamM1';


		// Constructor:
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS;

		$this->include_once[] = t3lib_extMgm::extPath('ren_documents') . 'lib/class.tx_rendocuments_sysfolder.php';
		
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->setModuleTemplate(t3lib_extMgm::extRelPath('ren_documents') . 'mod1/mod_navframe.html');
		//$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('dam') . 'res/css/stylesheet.css';
		$this->doc->docType  = 'xhtml_trans';


		$this->currentSubScript = t3lib_div::_GP('currentSubScript');

			// Setting highlight mode:
		$this->doHighlight = !$BE_USER->getTSConfigVal('options.pageTree.disableTitleHighlight');
		$this->doc->JScode='';
		$hlClass = 'active';

			// Setting JavaScript for menu.
		$this->doc->JScode=$this->doc->wrapScriptTags(
			($this->currentSubScript?'top.currentSubScript=unescape("'.rawurlencode($this->currentSubScript).'");':'').'
			// setting prefs for pagetree and drag & drop
			'.($this->doHighlight ? 'Tree.highlightClass = "'.$hlClass.'";' : '').'

			function jumpTo(id,linkObj,highlightID,bank)	{
				var theUrl = top.TS.PATH_typo3 + top.currentSubScript ;
				if (theUrl.indexOf("?") != -1) {
					theUrl += "&id=" + id
				} else {
					theUrl += "?id=" + id
				}
				top.fsMod.currentBank = bank;

				if (top.condensedMode)	{
					top.content.document.location.href=theUrl;
				} else {
					parent.list_frame.document.location.href=theUrl;
				}
				'.($this->doHighlight ? 'Tree.highlightActiveItem("web", highlightID + "_" + bank);' : '').'
				'.(!$GLOBALS['CLIENT']['FORMSTYLE'] ? '' : 'if (linkObj) {linkObj.blur();}').'
				return false;
			}


				// Call this function, refresh_nav(), from another script in the backend if you want to refresh the navigation frame (eg. after having changed a page title or moved pages etc.)
				// See t3lib_BEfunc::getSetUpdateSignal()
			function refresh_nav()	{
				window.setTimeout("_refresh_nav();",0);
			}


			function _refresh_nav()	{
				document.location.href="'.htmlspecialchars(t3lib_div::linkThisScript(array('unique' => time()))).'";
			}
		');
	}




	/**
	 * Main function, rendering the browsable page tree
	 *
	 * @return	void
	 */
	function main()	{
		global $LANG, $TYPO3_CONF_VARS;
		
		if (!$GLOBALS['BE_USER']->check('tables_select', 'tx_rendocuments_workspaces'))
		{
			$oMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage', 
				$LANG->getLL('error_noselect'), 
				'', 
				t3lib_FlashMessage::ERROR
			);
			t3lib_FlashMessageQueue::addMessage($oMessage);
			return;
		}
		
		$pid = tx_rendocuments_sysfolder::getPid();
		
		$this->doc->getDragDropCode();
		$this->doc->getContextMenuCode();
		$this->content = '';
		$this->content = $this->getWorkspaces();

		$this->markers['REFRESH'] = '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('unique' => uniqid('tx_dam_navframe')))).'">'.
				'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.refresh',1).'" alt="" /></a>';
		
		$sOnclickNewWorkspace = 'top.content.list_frame.location.href=top.TS.PATH_typo3+\'alt_doc.php?edit[tx_rendocuments_workspaces][' . $pid . ']=new&returnUrl=\' + encodeURIComponent(top.content.list_frame.location.href);';
		$this->markers['NEW_PAGE'] = '<a href="#" onclick="' . htmlspecialchars($sOnclickNewWorkspace) . '"><img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/new_el.gif') . ' title="' . $LANG->sL('LLL:EXT:ren_documents/mod1/locallang.xml:new_workspace', 1) . '" alt="" /></a>';

			// Adding highlight - JavaScript
		if ($this->doHighlight)	$this->content .= $this->doc->wrapScriptTags('
			hilight_row("",top.fsMod.navFrameHighlightedID["web"]);
		');
	}
	
	function getWorkspaces()
	{
		if ($GLOBALS['BE_USER']->isAdmin())
			$this->sGroupLookup = '1';
		else
		{
			$aGroupLookup = array();
			$aGroupLookup[] = 'access = \'\'';
			foreach ($GLOBALS['BE_USER']->userGroupsUID as $group)
				$aGroupLookup[] = $GLOBALS['TYPO3_DB']->listQuery('access', $group, 'tx_rendocuments_workspaces');
			$this->sGroupLookup = implode(' OR ', $aGroupLookup);
		}
		return '
			<div id="PageTreeDiv">
				<!-- TYPO3 tree structure. -->
				<ul class="tree" id="treeRoot">' . $this->getWorkspaceElements(0) . '
				</ul>
			</div>';
	}
	
	function getWorkspaceElements($pi_iParent, $pi_sIndent = "\t\t\t\t\t")
	{
		global $TYPO3_DB;
		$sTable = 'tx_rendocuments_workspaces';
		$rRes = $TYPO3_DB->exec_SELECTquery('*, IF(' . $this->sGroupLookup . ', 1, 0) AS hasAccess', $sTable, 'parent = ' . $pi_iParent . ' ' . t3lib_BEfunc::deleteClause($sTable), '', 'name');
		$aList = array();
		while ($aRow = $TYPO3_DB->sql_fetch_assoc($rRes))
		{
			$sAltText = t3lib_BEfunc::getRecordIconAltText($aRow, $sTable);
			$sIconImg = t3lib_iconWorks::getIconImage($sTable, $aRow, $this->doc->backPath, 'title="'.htmlspecialchars($sAltText).'"');
			$sTheIcon = ($aRow['hasAccess'] == '1') ? ($this->doc->wrapClickMenuOnIcon($sIconImg,$sTable,$aRow['uid'])) : ($sIconImg);
			$sRecTitle = t3lib_BEfunc::getRecordTitle($sTable, $aRow, FALSE, TRUE);
			$sRecLink = ($aRow['hasAccess'] == '1') ? ('<a href="#" onclick="jumpTo(\'' . $aRow['uid'] . '\', this,\'' . addslashes($sTable) . $aRow['uid'] . '\', 0);">' . htmlspecialchars($sRecTitle) . '</a>') : ($sRecTitle);
			$sInner = $this->getWorkspaceElements($aRow['uid'], $pi_sIndent . "\t\t");
			if ($sInner)
				$sInner = chr(10) . $pi_sIndent . "\t" . '<ul>' . $sInner . chr(10) . $pi_sIndent . "\t" . '</ul>' . chr(10) . $pi_sIndent;
			$aList[] = chr(10) . $pi_sIndent . '<li id="' . htmlspecialchars($sTable) . $aRow['uid'] . '_0"><div class="treeLinkItem">' . $sTheIcon . $sRecLink . '</div>' . $sInner . '</li>';
		}
		return implode('', $aList);
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		global $LANG;
		// Null out markers:
		$docHeaderButtons = array(
			'new_page' => '',
			'csh' => '',
			'refresh' => '',
		);
		$this->markers['WORKSPACEINFO'] = '';

		$this->markers['CONTENT'] = $this->content;
		$subparts['###SECOND_ROW###'] = ''; 
		$docHeaderButtons['refresh'] = $this->markers['REFRESH'];
		$docHeaderButtons['new_page'] = $this->markers['NEW_PAGE'];

		$this->content = $this->doc->startPage($LANG->sL('LLL:EXT:ren_documents/mod1/locallang_mod.xml:mlang_labels_tablabel',1));
		$this->content.= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $this->markers, $subparts);
		$this->content.= $this->doc->endPage();

		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_documents/mod1/tx_rendocuments_navframe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ren_documents/mod1/tx_rendocuments_navframe.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_rendocuments_navframe');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>