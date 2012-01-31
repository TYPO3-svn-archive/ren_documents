<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_rendocuments_docs'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY published DESC',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'fe_group' => 'fe_group',
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_rendocuments_docs.gif',
		'requestUpdate' => 'workspace',
	),
);

$TCA['tx_rendocuments_workspaces'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_workspaces',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_rendocuments_workspaces.gif',
	),
);

$TCA['tx_rendocuments_subscriptions'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_subscriptions',        
        'label'     => 'user',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_rendocuments_subscriptions.gif',
    ),
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:ren_documents/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:' . $_EXTKEY . '/flexform_ds_pi1.xml');
t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/', 'Documents display');
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] = 'EXT:ren_documents/lib/class.tx_rendocuments_dynflex.php:&tx_rendocuments_dynflex';


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_rendocuments_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_rendocuments_pi1_wizicon.php';
}


if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('txdamM1_txrendocumentsM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');

	t3lib_extMgm::addModule('txdamM1', 'txrendocumentsM1', 'top', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

$TCA['pages']['columns']['module']['config']['items'][] = array('Documents', 'ren_docs');

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_rendocuments_filemanager';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_rendocuments_alert';
?>
