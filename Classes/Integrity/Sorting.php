<?php

declare(strict_types=1);

namespace Team23\T23InlineContainer\Integrity;

use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Sorting extends \B13\Container\Integrity\Sorting
{
    public function runForSingleContainer($containerRecord, $cType): void
    {
        $columns = $this->tcaRegistry->getAvailableColumns($cType);
        $colPosByCType[$cType] = [];
        foreach ($columns as $column) {
            $colPosByCType[$cType][] = $column['colPos'];
        }
        $this->unsetContentDefenderConfiguration($cType);
        $this->fixChildrenSorting([$containerRecord], $colPosByCType, false, false);
    }

    /**
     * The function is mostly a copy from EXT:container. Just the colPos ist fixed
     * in the DataHandler to prevent localization issues.
     *
     * @param array $containerRecords
     * @param array $colPosByCType
     * @param bool $dryRun
     * @return void
     */
    protected function fixChildrenSorting(array $containerRecords, array $colPosByCType, bool $dryRun, bool $enableLogging): void
    {
        $datahandler = GeneralUtility::makeInstance(DataHandler::class);
        $datahandler->enableLogging = $enableLogging;
        foreach ($containerRecords as $containerRecord) {
            try {
                $container = $this->containerFactory->buildContainer((int)$containerRecord['uid']);
            } catch (Exception $e) {
                // should not happend
                continue;
            }
            if ($this->fixChildrenSortingUpdateRequired($container, $colPosByCType) === false || $dryRun === true) {
                continue;
            }
            $prevChild = null;
            foreach ($colPosByCType[$containerRecord['CType']] as $colPos) {
                $children = $container->getChildrenByColPos($colPos);
                if (empty($children)) {
                    continue;
                }
                foreach ($children as $child) {
                    if ($prevChild === null) {
                        $cmdmap = [
                            'tt_content' => [
                                $child['uid'] => [
                                    'move' => [
                                        'action' => 'paste',
                                        'target' => $container->getPid(),
                                        'update' => [
                                            'colPos' => $containerRecord['uid'] . '-' . $child['colPos'],
                                            'sys_language_uid' => $containerRecord['sys_language_uid'],

                                        ],
                                    ],
                                ],
                            ],
                        ];
                        $datahandler->start([], $cmdmap);
                        $datahandler->process_datamap();
                        $datahandler->process_cmdmap();
                    } else {
                        $cmdmap = [
                            'tt_content' => [
                                $child['uid'] => [
                                    'move' => [
                                        'action' => 'paste',
                                        'target' => -$prevChild['uid'],
                                        'update' => [
                                            'colPos' => $containerRecord['uid'] . '-' . $child['colPos'],
                                            'sys_language_uid' => $containerRecord['sys_language_uid'],

                                        ],
                                    ],
                                ],
                            ],
                        ];
                        $datahandler->start([], $cmdmap);
                        $datahandler->process_datamap();
                        $datahandler->process_cmdmap();
                    }
                    $prevChild = $child;
                }
            }
        }
    }
}