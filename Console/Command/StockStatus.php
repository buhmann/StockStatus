<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Console\Command;

use Buhmann\StockStatus\Helper\Data as StockHelper;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StockStatus extends Command
{
    /**
     * @var AppState
     */
    protected $_appState;

    /**
     * @var StockHelper
     */
    protected $_stockHelper;

    /**
     * @param AppState $appState
     * @param StockHelper $stockHelper
     */
    public function __construct(
        AppState $appState,
        StockHelper $stockHelper
    ) {
        $this->_appState = $appState;
        $this->_stockHelper = $stockHelper;
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
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            $output->writeln("<info>" . __('Start updateStockStatusFilterAttribute ') . "</info>");
            $this->_stockHelper->updateStockStatusFilterAttribute();
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
        return Cli::RETURN_FAILURE;
    }
}
