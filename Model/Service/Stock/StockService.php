<?php
/**
 * Copyright (c) 2019, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2019 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Model\Service\Stock;

use Magento\Bundle\Model\Product\Type as Bundled;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\Store;
use Nosto\Tagging\Model\Service\Stock\Provider\StockProviderInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Inventory\Model\ResourceModel\IsProductAssignedToStock;
use Magento\InventoryCatalog\Model\GetSourceItemsBySkuAndSourceCodes;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;

/**
 * StockService helper used for product inventory level related tasks.
 */
class StockService
{
    /** @var GetStockIdForCurrentWebsite */
    private $getStockIdForCurrentWebsite;

    private $stockProvider;
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;
    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;
    /**
     * @var IsProductAssignedToStock
     */
    private $isProductAssignedToStock;
    /**
     * @var GetSourceItemsBySkuAndSourceCodes
     */
    private $getSourceItemsBySkuAndSourceCodes;
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * Constructor.
     *
     * @param StockProviderInterface $stockProvider
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductSalableInterface $isProductSalable
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRepositoryInterface $stockRepository
     * @param IsProductAssignedToStock $isProductAssignedToStock
     * @param GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        StockProviderInterface $stockProvider,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductSalableInterface $isProductSalable,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        StockRepositoryInterface $stockRepository,
        IsProductAssignedToStock $isProductAssignedToStock,
        GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->stockProvider = $stockProvider;
        $this->isProductSalable = $isProductSalable;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRepository = $stockRepository;
        $this->isProductAssignedToStock = $isProductAssignedToStock;
        $this->getSourceItemsBySkuAndSourceCodes = $getSourceItemsBySkuAndSourceCodes;
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * Calculates the total qty in stock. If the product is configurable the
     * the sum of associated products will be calculated.
     *
     * @param Product $product
     * @return int
     * @suppress PhanUndeclaredMethod
     * @suppress PhanDeprecatedFunction
     */
    public function getQuantity(Product $product)
    {
        $qty = 0;
        switch ($product->getTypeId()) {
            case ProductType::TYPE_BUNDLE:
                /** @var Bundled $productType */
                $productType = $product->getTypeInstance();
                $bundledItemIds = $productType->getChildrenIds($product->getId(), $required = true);
                $productIds = [];
                foreach ($bundledItemIds as $variants) {
                    if (is_array($variants) && count($variants) > 0) { // @codingStandardsIgnoreLine
                        foreach ($variants as $productId) {
                            $productIds[] = $productId;
                        }
                    }
                }
                $qty = $this->getMinQty($productIds);
                break;
            case Grouped::TYPE_CODE:
                $productType = $product->getTypeInstance();
                if ($productType instanceof Grouped) {
                    $products = $productType->getAssociatedProductIds($product);
                    $qty = $this->getMinQty($products);
                }
                break;
            case Configurable::TYPE_CODE:
                $productType = $product->getTypeInstance();
                if ($productType instanceof Configurable) {
                    $productIds = $productType->getChildrenIds($product->getId());
                    if (isset($productIds[0]) && is_array($productIds[0])) {
                        $productIds = $productIds[0];
                    }
                    $qty = $this->getQtySum($productIds);
                }
                break;
            default:
                $qty += $this->stockProvider->getStockStatus($product->getId())->getQty();
                break;
        }

        return $qty;
    }

    /**
     * Searches the minimum quantity from the products collection
     *
     * @param int[] $productIds
     * @return int|mixed
     */
    private function getMinQty(array $productIds)
    {
        $quantities = [];
        $stockItems = $this->stockProvider->getStockStatuses($productIds);
        $minQty = 0;
        /* @var Product $product */
        foreach ($stockItems as $stockItem) {
            $quantities[] = $stockItem->getQty();
        }
        if (!empty($quantities)) {
            rsort($quantities, SORT_NUMERIC);
            $minQty = array_pop($quantities);
        }
        return $minQty;
    }

    /**
     * Sums quantities for all product ids in array
     *
     * @param int[] $productIds
     * @return int
     */
    private function getQtySum($productIds)
    {
        $qty = 0;
        $stockItems = $this->stockProvider->getStockStatuses($productIds);
        foreach ($stockItems as $item) {
            $qty += $item->getQty();
        }
        return $qty;
    }

    /**
     * Sums quantities for all product ids in array
     *
     * @param Product $product
     * @param Store $store
     * @return bool
     */
    public function isInStock(Product $product, Store $store)
    {
//        $this->getStockIdForCurrentWebsite->execute();
        $test = $this->getStockStatus($product, $store);
        return (bool)$this->stockProvider->getStockItem(
            $product->getId(),
            $store->getWebsiteId()
        )->getIsInStock();
    }

    /**
     * @param Product $product
     * @param Store $store
     * @return int
     */
    public function getStockStatus(Product $product, Store $store)
    {
//        $stock = $this->stockResolver->execute(
//            \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
//            $store->getWebsite()->getCode()
//        );
//        $stockId = (int)$stock->getStockId();
        $stockId = 2;

        $inventoryShit = $this->getSourceItemsBySkuAndSourceCodes->execute($product->getSku(), ['second-source']);
        if ($this->isSingleSourceMode->execute()){
            // Use regular registry
        } else {
            // Use new MSI stock
        }

        $test = $this->stockRepository->get($stockId);
        $status = $this->isProductSalable->execute($product->getId(), $stockId);
        $skuShit = $this->isProductAssignedToStock->execute('WS12-XS-Blue', $stockId);
        if (!$status) {
            $websiteId = $store->getWebsiteId();
            $stockItem = $this->stockRegistryProvider->getStockItem($product->getId(), $websiteId);
            $inStock = $stockItem->getIsInStock();
            $stockItem = $this->stockRegistryProvider->getStockItem(
                $product->getId(),
                $this->stockConfiguration->getDefaultScopeId()
            );
            $status = $stockItem->getIsInStock();
            $this->stockRegistryProvider->getStockItem($product->getId(), 2);
        }
        return $status;
    }
}
