<?php
/**
 * Valitor Module for Magento 2.x.
 *
 * Copyright © 2020 Valitor. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SDM\Valitor\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use SDM\Valitor\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Ok extends Index implements CsrfAwareActionInterface
{
    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->writeLog();
        $checkAvs = false;
        $post    = $this->getRequest()->getPostValue();
        if(isset($post['avs_code']) && isset($post['avs_text'])){
            $checkAvs = $this->generator->avsCheck($this->getRequest(), 
                                                strtolower($post['avs_code']), 
                                                strtolower($post['avs_text'])
                                            );
        }
        if ($this->checkPost() && $checkAvs == false) {
            $this->generator->handleOkAction($this->getRequest());

            return $this->_redirect('checkout/onepage/success');

        } else {
            $this->_eventManager->dispatch('order_cancel_after', ['order' => $this->order]);
            $this->generator->restoreOrderFromRequest($this->getRequest());

            return $this->_redirect('checkout');
        }
    }
}
