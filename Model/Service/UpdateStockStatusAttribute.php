<?php

declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Buhmann\StockStatus\Api\Data\StockStatusInterface;
use Buhmann\StockStatus\Helper\Data as HelperData;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;

class UpdateStockStatusAttribute
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var Action
     */
    private Action $productAction;

    /**
     * @var AreProductsSalableInterface
     */
    private AreProductsSalableInterface $areProductsSalable;

    /**
     * @var HelperData
     */
    private HelperData $helperData;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param Action $productAction
     * @param AreProductsSalableInterface $areProductsSalable
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        Action $productAction,
        AreProductsSalableInterface $areProductsSalable,
        HelperData $helperData,
        LoggerInterface $logger
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAction = $productAction;
        $this->areProductsSalable = $areProductsSalable;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * Run mass update for stock status filter attribute
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $stockId = $this->helperData->getCurrentStockId();

            $pageSize = 500;

            $countCollection = $this->productCollectionFactory->create();
            $totalProducts = $countCollection->getSize();

            if ($totalProducts === 0) {
                return;
            }

            $totalPages = (int)ceil($totalProducts / $pageSize);

            for ($page = 1; $page <= $totalPages; $page++) {
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToSelect(['sku', StockStatusInterface::STOCK_STATUS_FILTER_ATTRIBUTE]);
                $productCollection->setPageSize($pageSize);
                $productCollection->setCurPage($page);

                $skusToVerify = [];
                $productMap = [];

                foreach ($productCollection as $product) {
                    if ($product->getData(StockStatusInterface::STOCK_STATUS_FILTER_ATTRIBUTE)) {
                        $skusToVerify[] = $product->getSku();
                        $productMap[$product->getSku()] = $product->getId();
                    }
                }

                if (!empty($skusToVerify)) {
                    $salableResults = $this->areProductsSalable->execute($skusToVerify, $stockId);
                    $inStockProductIds = [];
                    $outOfStockProductIds = [];

                    foreach ($salableResults as $result) {
                        $sku = $result->getSku();
                        $productId = $productMap[$sku] ?? null;

                        if ($productId) {
                            if ($result->isSalable()) {
                                $inStockProductIds[] = $productId;
                            } else {
                                $outOfStockProductIds[] = $productId;
                            }
                        }
                    }

                    if (!empty($inStockProductIds)) {
                        $this->productAction->updateAttributes($inStockProductIds, [StockStatusInterface::STOCK_STATUS_FILTER_ATTRIBUTE => 1], 0);
                    }
                    if (!empty($outOfStockProductIds)) {
                        $this->productAction->updateAttributes($outOfStockProductIds, [StockStatusInterface::STOCK_STATUS_FILTER_ATTRIBUTE => ''], 0);
                    }
                }

                // Unset collection and clear memory immediately after page processing
                $productCollection->clear();
                unset($productCollection);
            }
        } catch (Exception $e) {
            $this->logger->error('Buhmann_StockStatus (Service): ' . $e->getMessage());
        }
    }
}
