<?php

namespace Team23\T23InlineContainer\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Integrity\Database;
use B13\Container\Tca\Registry;
use Team23\T23InlineContainer\Integrity\Sorting;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DataHandler implements SingletonInterface
{
    /**
     * @var array<,int>
     */
    private $postProcessContainerUidList = [];

    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        if (is_array($dataHandler->datamap['tt_content'] ?? null)) {
            foreach ($dataHandler->datamap['tt_content'] as $id => $values) {
                if (!empty($values['tx_t23inlinecontainer_elements']) && MathUtility::canBeInterpretedAsInteger($id)) {
                    $containerUid = (int) $id;
                    $this->postProcessContainerUidList[$containerUid] = $containerUid;
                }
            }
        }
    }

    public function processCmdmap_preProcess($command, $table, $id, $value, $pObj, $pasteUpdate): void
    {
        if (in_array($command, ['copy', 'localize']) && $table === 'tt_content') {
            $GLOBALS['TCA']['tt_content']['columns']['tx_t23inlinecontainer_elements']['config']['type'] = 'none';
        }
    }

    public function processCmdmap_postProcess($command, $table, $id, $value, $pObj, $pasteUpdate, $pasteDatamap): void
    {
        if (in_array($command, ['copy', 'localize']) && $table === 'tt_content') {
            $GLOBALS['TCA']['tt_content']['columns']['tx_t23inlinecontainer_elements']['config']['type'] = 'tx_t23inlinecontainer_elements';
        }
    }

    /**
     * Fix container inline elements sorting after everything else has been processes
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return void
     */
    public function processDatamap_afterAllOperations(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        // Make sure that container sorting is only update once per container element
        // => Only run sorting update after all operations have been finished
        if (!empty($this->postProcessContainerUidList) && $dataHandler->isOuterMostInstance()) {
            $integrityDatabase = GeneralUtility::makeInstance(Database::class);
            $dataHandlerDatabase = GeneralUtility::makeInstance(\B13\Container\Hooks\Datahandler\Database::class);
            $registry = GeneralUtility::makeInstance(Registry::class);
            $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
            $containerService = GeneralUtility::makeInstance(ContainerService::class);
            $sorting = GeneralUtility::makeInstance(Sorting::class, $integrityDatabase, $registry, $containerFactory, $containerService);
            foreach ($this->postProcessContainerUidList as $containerRecordUid) {
                $containerRecord = $dataHandlerDatabase->fetchOneRecord($containerRecordUid);
                if (!empty($containerRecord)) {
                    $sorting->runForSingleContainer($containerRecord, $containerRecord['CType']);
                }
            }
        }
    }
}
