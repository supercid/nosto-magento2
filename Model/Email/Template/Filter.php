<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
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
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Model\Email\Template;

use Magento\Email\Model\Template\Filter as MagentoFilter;
use Nosto\Tagging\Helper\Account;

/**
 */
class Filter extends MagentoFilter
{
    private $nostoAccountHelper;

    /**
     *
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Variable\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Pelago\Emogrifier $emogrifier
     * @param \Magento\Email\Model\Source\Variables $configVariables
     * @param array $variables
     * @param \Magento\Framework\Css\PreProcessor\Adapter\CssInliner|null $cssInliner
     *
     * @param Account $nostoAccountHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Variable\Model\VariableFactory $coreVariableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\UrlInterface $urlModel,
        \Pelago\Emogrifier $emogrifier,
        \Magento\Email\Model\Source\Variables $configVariables,
        $variables = [],
        \Magento\Framework\Css\PreProcessor\Adapter\CssInliner $cssInliner = null,
        Account $nostoAccountHelper
    )
    {
        parent::__construct(
            $string,
            $logger,
            $escaper,
            $assetRepo,
            $scopeConfig,
            $coreVariableFactory,
            $storeManager,
            $layout,
            $layoutFactory,
            $appState,
            $urlModel,
            $emogrifier,
            $configVariables,
            $variables,
            $cssInliner
        );

        $this->nostoAccountHelper = $nostoAccountHelper;
    }


    public function nostoAccountDirective($components)
    {
        $account = $this->nostoAccountHelper->findAccount($this->_storeManager->getStore());
        return $account->getName();
    }
}
