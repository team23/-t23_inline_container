<?php

defined('TYPO3') || die();

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

call_user_func(function ($extKey, $table) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table,
        [
            'tx_t23inlinecontainer_elements' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:t23_inline_container/Resources/Language/locallang_db.xlf:tt_content.contentelements',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tt_content',
                    'foreign_field' => 'tx_container_parent',
                    'appearance' => [
                        'collapseAll' => true,
                        'expandSingle' => true,
                        'levelLinksPosition' => 'bottom',
                        'useSortable' => true,
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                        'enabledControls' => [
                            'info' => false,
                        ]
                    ],
                    'overrideChildTca' => [
                        'columns' => [
                            'colPos' => [
                                'config' => [
                                    'itemsProcFunc' => \Team23\T23InlineContainer\Tca\ItemProcFunc::class . '->colPos'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ]
    );
}, 't23_inline_container', 'tt_content');
