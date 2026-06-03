<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Console\Command;

use Buhmann\StockStatus\Model\Service\UpdateStockStatusAttribute as UpdateService;
use Exception;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StockStatus extends Command
{
    /**
     * @var AppState
     */
    protected AppState $_appState;

    /**
     * @var UpdateService
     */
    protected UpdateService $updateService;

    /**
     * @param AppState $appState
     * @param UpdateService $updateService
     */
    public function __construct(
        AppState $appState,
        UpdateService $updateService
    ) {
        $this->_appState = $appState;
        $this->updateService = $updateService;
        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('buhmann:init:stockStatus')
            ->setDescription('Init Stock Status Update');

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            try {
                if (!$this->_appState->getAreaCode()) {
                    $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
                }
            } catch (LocalizedException) {

            }

            $output->writeln("<info>" . __('Start updateStockStatusFilterAttribute ') . "</info>");
            $this->updateService->execute();

            return Cli::RETURN_SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
        return Cli::RETURN_FAILURE;
    }
}
