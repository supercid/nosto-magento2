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

namespace Nosto\Tagging\Model\Service;

use Magento\Store\Model\Store;
use Nosto\Tagging\Model\Product\Index\IndexRepository;
use Nosto\Tagging\Api\Data\ProductIndexInterface;
use Nosto\Tagging\Model\Product\Index\Builder;
use Magento\Catalog\Model\ProductRepository;

class Index
{
    /** @var IndexRepository */
    private $indexRepository;

    /** @var Builder */
    private $indexBuilder;

    /** @var ProductRepository */
    private $productRepository;

    /**
     * Index constructor.
     * @param IndexRepository $indexRepository
     * @param Builder $indexBuilder
     * @param ProductRepository $productRepository
     */
    public function __construct(IndexRepository $indexRepository, Builder $indexBuilder, ProductRepository $productRepository)
    {
        $this->indexRepository = $indexRepository;
        $this->indexBuilder = $indexBuilder;
        $this->productRepository = $productRepository;
    }

    /**
     * @param int $productId
     * @param Store $store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function handleProductChange(int $productId, Store $store)
    {
        $indexedProduct = $this->indexRepository->getByProductIdAndStoreId($productId, $store->getId());
        if ($indexedProduct instanceof ProductIndexInterface) {
            $indexedProduct->setIsDirty(true);
            $indexedProduct->setUpdatedAt(new \DateTime('now'));
        } else {
            $product = $this->productRepository->getById($productId);
            $indexedProduct = $this->indexBuilder->build($product, $store);
        }
        $this->indexRepository->save($indexedProduct);
    }
}