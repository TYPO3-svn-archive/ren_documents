<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_rendocuments_docs'] = array (
	'ctrl' => $TCA['tx_rendocuments_docs']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,fe_group,title,file,description,published,author,service,themes,keywords,workspace'
	),
	'feInterface' => $TCA['tx_rendocuments_docs']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '1'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'file' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.file',		
			'displayCond' => 'FIELD:workspace:REQ:true',
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_rendocuments_filecontrol->makeControl',
				'internal_type' => 'file_reference',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'uploadfolder' => 'uploads/tx_rendocuments',
				'max_size' => 100000,	
				'show_thumbs' => 1,	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
				'disable_controls' => 'browser,list',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
				'eval' => 'required',
			)
		),
		'published' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.published',		
			'config' => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date,required',
				'default'  => time(),
			)
		),
		'author' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.author',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		/*'service' => array (        
			'exclude' => 0,        
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.service',        
			'config' => array (
				'type' => 'select',    
				'foreign_table' => 'tx_renlocaliser_services',    
				'foreign_table_where' => 'ORDER BY tx_renlocaliser_services.sigle',    
				'size' => 1,    
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'themes' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.themes',		
			'config' => array (
				'type' => 'select',    
				'foreign_table' => 'tx_icscategories_categories',    
				'foreign_table_where' => 'ORDER BY tx_icscategories_categories.title',    
				'size' => 5,    
				'minitems' => 0,
				'maxitems' => 10,
			)
		),*/
		'keywords' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.keywords',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'changedby' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.changedby',		
			'config' => array (
				'type' => 'select',	
				'readOnly' => 1,
				'foreign_table' => 'be_users',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'workspace' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.workspace',		
			'config' => array (
				'type' => 'select',	
				'items' => array (
					array('',0),
				),
				#'foreign_table' => 'tx_rendocuments_workspaces',	
				#'foreign_table_where' => 'AND tx_rendocuments_workspaces.pid=###CURRENT_PID### ORDER BY tx_rendocuments_workspaces.uid',	
				'itemsProcFunc' => 'tx_rendocuments_tcahelper->selectItemsTCA',
				'itemsProcFunc.' => array(
					'table' => 'tx_rendocuments_workspaces',
					// 'where' => 'AND tx_rendocuments_workspaces.pid=###CURRENT_PID###',	
					'indexField' => 'uid',
					'titleField' => 'name',
					'orderField' => 'uid',
					'beAccessField' => 'access',
				),
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'cruser_id' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_docs.createdby',		
			'config' => array (
				'type' => 'select',	
				'readOnly' => 1,
				'foreign_table' => 'be_users',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, file;;;;3-3-3, description, published, author, service, themes, keywords, workspace, fe_group;;;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_rendocuments_workspaces'] = array (
	'ctrl' => $TCA['tx_rendocuments_workspaces']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,name,groups,access,parent,shy'
	),
	'feInterface' => $TCA['tx_rendocuments_workspaces']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_workspaces.name',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'groups' => array (        
			'exclude' => 1,        
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_workspaces.groups',        
			'config' => array (
				'type' => 'select',    
				'foreign_table' => 'fe_groups',    
				'size' => 5,    
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'access' => array (        
			'exclude' => 1,        
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_workspaces.access',        
			'config' => array (
				'type' => 'select',    
				'foreign_table' => 'be_groups',    
				'foreign_table_where' => 'ORDER BY be_groups.title',    
				'size' => 5,    
				'minitems' => 0,
				'maxitems' => 20,    
				'wizards' => array(
					'_PADDING'  => 2,
					'_VERTICAL' => 1,
					'add' => array(
						'type'   => 'script',
						'title'  => 'Create new record',
						'icon'   => 'add.gif',
						'params' => array(
							'table'    => 'be_groups',
							'pid'      => '0',
							'setValue' => 'prepend'
						),
						'script' => 'wizard_add.php',
					),
					'list' => array(
						'type'   => 'script',
						'title'  => 'List',
						'icon'   => 'list.gif',
						'params' => array(
							'table' => 'be_groups',
							'pid'   => '0',
						),
						'script' => 'wizard_list.php',
					),
					'edit' => array(
						'type'                     => 'popup',
						'title'                    => 'Edit',
						'script'                   => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon'                     => 'edit2.gif',
						'JSopenParams'             => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					),
				),
			)
		),
		'parent' => array (        
			'exclude' => 1,        
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_workspaces.parent',        
			'config' => array (
				'type' => 'select',    
				'items' => array (
					array('',0),
				),
				'foreign_table' => 'tx_rendocuments_workspaces',    
				'foreign_table_where' => 'ORDER BY tx_rendocuments_workspaces.uid',    
				'size' => 1,    
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'shy' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_workspaces.shy',        
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, name, groups, access, parent,shy')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);


$TCA['tx_rendocuments_subscriptions'] = array (
    'ctrl' => $TCA['tx_rendocuments_subscriptions']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'hidden,user,workspace'
    ),
    'feInterface' => $TCA['tx_rendocuments_subscriptions']['feInterface'],
    'columns' => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'user' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_subscriptions.user',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'fe_users',    
                'foreign_table_where' => 'ORDER BY fe_users.username',    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'workspace' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:ren_documents/locallang_db.xml:tx_rendocuments_subscriptions.workspace',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'tx_rendocuments_workspaces',    
                'foreign_table_where' => 'ORDER BY tx_rendocuments_workspaces.name',    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => 'hidden;;1;;1-1-1, user, workspace')
    ),
    'palettes' => array (
        '1' => array('showitem' => '')
    )
);
?>