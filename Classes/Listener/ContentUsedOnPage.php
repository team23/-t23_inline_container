<?php

declare(strict_types=1);

namespace Team23\T23InlineContainer\Listener;

use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;

final class ContentUsedOnPage
{
    protected Registry $tcaRegistry;
    protected ContainerFactory $containerFactory;

    public function __construct(ContainerFactory $containerFactory, Registry $tcaRegistry)
    {
        $this->containerFactory = $containerFactory;
        $this->tcaRegistry = $tcaRegistry;
    }

    public function __invoke(IsContentUsedOnPageLayoutEvent $event): void
    {
        $record = $event->getRecord();
        if ($record['tx_container_parent'] > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$record['tx_container_parent']);
                $columns = $this->tcaRegistry->getAvailableColumns($container->getCType());
                foreach ($columns as $column) {
                    if ($column['colPos'] === (int)$record['colPos']) {
                        if ($record['sys_language_uid'] > 0 && $container->isConnectedMode()) {
                            $used = ($container->hasChildInColPos((int)$record['colPos'], (int)$record['l18n_parent'])
                                || $container->hasChildInColPos((int)$record['colPos'], (int)$record['uid']));
                            $event->setUsed($used);
                            return;
                        }
                        $used = $container->hasChildInColPos((int)$record['colPos'], (int)$record['uid']);
                        $event->setUsed($used);
                        return;
                    }
                }
            } catch (Exception $e) {
            }
        }
    }
}