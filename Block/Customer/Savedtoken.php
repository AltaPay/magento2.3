<?php
/**
 * Valitor Module for Magento 2.x.
 *
 * Copyright © 2020 Valitor. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SDM\Valitor\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use SDM\Valitor\Model\TokenFactory;

class Savedtoken extends Template
{
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var TokenFactory
     */
    private $dataToken;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        TokenFactory $dataToken
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->dataToken       = $dataToken;
    }

    public function getCustomerTokens()
    {
        $tokens = false;
        if ($this->checkoutSession->isLoggedIn()) {
            $tokens = $this->dataToken->create()->getCollection()
                                      ->addFieldToFilter('customer_id', $this->checkoutSession->getCustomer()->getId());
        }

        return $tokens;
    }

    public function getAjaxUrl()
    {
        return $this->getUrl("sdmvalitor/customer/index"); // Controller Url
    }
}