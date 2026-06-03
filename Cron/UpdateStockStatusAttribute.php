<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Cron;

use Buhmann\StockStatus\Model\Service\UpdateStockStatusAttribute as UpdateService;
use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Exception;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class UpdateStockStatusAttribute
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var ConfigProvider
     */
    protected ConfigProvider $configProvider;

    /**
     * @var UpdateService
     */
    protected UpdateService $updateService;

    /**
     * @var AppState
     */
    protected AppState $appState;

    /**
     * UpdateStockStatusAttribute constructor.
     *
     * @param LoggerInterface $logger
     * @param ConfigProvider $configProvider
     * @param UpdateService $updateService
     * @param AppState $appState
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigProvider $configProvider,
        UpdateService $updateService,
        AppState $appState
    ) {
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->updateService = $updateService;
        $this->appState = $appState;
    }

    /**
     * Executes Cronjob for updating stock_status_filter attribute
     *
     * @return $this
     */
    public function execute(): static
    {
        if ($this->configProvider->isStockFilterEnabled()) {
            try {
                try {
                    if (!$this->appState->getAreaCode()) {
                        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
                    }
                } catch (LocalizedException) {

                }

                $this->updateService->execute();
            } catch (Exception $e) {
                $this->logger->error('Buhmann_StockStatus Cron Exception: ' . $e->getMessage());
            }
        }
        return $this;
    }
}
