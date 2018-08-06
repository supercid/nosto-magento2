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

namespace Nosto\Tagging\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Nosto\AbstractObject;
use Nosto\Tagging\Helper\Account as NostoHelperAccount;
use Nosto\Tagging\Helper\Data as NostoHelperData;
use Nosto\Tagging\Helper\Scope as NostoHelperScope;
use Nosto\Object\Signup\Account as NostoSignupAccount;
use Nosto\Nosto;

/**
 * Meta data block for outputting <meta> elements in the page <head>.
 * This block should be included on all pages.
 */
class Email extends Template
{
    const EMAIL_WIDGET_VERSION = '2.8.0';

    use TaggingTrait {
        TaggingTrait::__construct as taggingConstruct; // @codingStandardsIgnoreLine
    }

    private $nostoHelperData;
    private $accountHelper;
    private $storeManager;

    /**
     * Constructor.
     *
     * @param Context $context the context.
     * @param NostoHelperData $nostoHelperData the data helper.
     * @param NostoHelperAccount $nostoHelperAccount
     * @param NostoHelperScope $nostoHelperScope
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        NostoHelperData $nostoHelperData,
        NostoHelperAccount $nostoHelperAccount,
        NostoHelperScope $nostoHelperScope,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->taggingConstruct($nostoHelperAccount, $nostoHelperScope);
        $this->nostoHelperData = $nostoHelperData;
        $this->accountHelper = $nostoHelperAccount;
        $this->storeManager = $context->getStoreManager();
    }

    /**
     * Returns the nosto account name
     *
     * @return string|null
     */
    public function getNostoAccountName()
    {
        $account = $this->nostoHelperAccount->findAccount($this->storeManager->getStore());
        if ($account instanceof NostoSignupAccount) {
            return $account->getName();
        }

        return null;
    }

    /**
     * Returns the recipient email address
     *
     * @return string|null
     */
    public function getRecipientEmailAddress()
    {
        $email = $this->getData('email');
        if ($email) {
            return $email;
        }

        return null;
    }

    /**
     * Returns the recommendation type
     *
     * @return string|null
     */
    public function getRecommendationType()
    {
        $type = $this->getData('recotype');
        if ($type) {
            return $type;
        }

        return null;
    }

    /**
     * Returns the amount products to display in reco
     *
     * @return string|null
     */
    public function getProductAmount()
    {
        $amount = $this->getData('amount');
        if ($amount) {
            return $amount;
        }

        return 4;
    }

    /**
     * Returns the heading for the reco
     *
     * @return string|null
     */
    public function getHeading()
    {
        $heading = $this->getData('heading');
        if ($heading) {
            return $heading;
        }

        return "Recommended for you";
    }

    /**
     * Rerturns the image source for recommendations
     *
     * @param $number
     * @return string
     */
    public function getRecommendationImageSource($number)
    {
        $src = sprintf('http://%s/image/v1/%s/%s/%d/?uid=%s&amp;version=%s',
            Nosto::getServerUrl(),
            $this->getNostoAccountName(),
            $this->getRecommendationType(),
            $number,
            $this->getRecipientEmailAddress(),
            self::EMAIL_WIDGET_VERSION
        );

        return $src;
    }

    /**
     * Returns the description for the recommendation
     *
     * @param $number
     * @return string
     */
    public function getRecommendationDescriptionSource($number)
    {
        $src = sprintf('http://%s/image/v1/%s/%s/%d/desc?uid=%s&amp;version=%s',
            Nosto::getServerUrl(),
            $this->getNostoAccountName(),
            $this->getRecommendationType(),
            $number,
            $this->getRecipientEmailAddress(),
            self::EMAIL_WIDGET_VERSION
        );

        return $src;
    }

    /**
     * Returns the link for recommendations
     *
     * @param $number
     * @return string
     */
    public function getRecommendationLink($number)
    {
        $src = sprintf('http://%s/image/v1/%s/%s/%d/go?uid=%s&amp;version=%s',
            Nosto::getServerUrl(),
            $this->getNostoAccountName(),
            $this->getRecommendationType(),
            $number,
            $this->getRecipientEmailAddress(),
            self::EMAIL_WIDGET_VERSION
        );

        return $src;
    }

    /**
     * Checks is all mandatory variables are defined and recommendations can be
     * displayed
     *
     * @return bool
     */
    public function canRenderRecos()
    {
        return $this->getNostoAccountName() && $this->getRecommendationType();
    }

    /**
     * @return null
     */
    public function getAbstractObject()
    {
        return null;
    }
}
