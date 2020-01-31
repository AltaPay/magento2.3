<?php

namespace SDM\Valitor\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use SDM\Valitor\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Fail extends Index implements CsrfAwareActionInterface
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
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->writeLog();
        $responseStatus = '';
        try {
            $this->generator->restoreOrderFromRequest($this->getRequest());
            $post             = $this->getRequest()->getPostValue();
            $merchantErrorMsg = '';
            $responseStatus   = strtolower($post['status']);
            if (isset($post['error_message'])) {
                $msg = $post['error_message'];
                if ($post['error_message'] != $post['merchant_error_message']) {
                    $merchantErrorMsg = $post['merchant_error_message'];
                }
            } else {
                $msg = 'Unknown response';
            }

            //Set order status, if available from the payment gateway
            switch ($responseStatus) {
                case 'cancelled':
                    //TODO: Overwrite the message
                    $msg = "Payment canceled";
                    $this->generator->handleCancelStatusAction($this->getRequest(), $responseStatus);
                    break;
                case "failed":
                case "error":
                    $this->generator->handleFailedStatusAction($this->getRequest(), $msg, $merchantErrorMsg, $responseStatus);
                    break;
                default:
                    $this->generator->handleOrderStateAction($this->getRequest());
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        if ($responseStatus == 'failed' || $responseStatus == 'error') {
            $resultRedirect = $this->prepareRedirect('checkout/cart', array(), $msg);
        } else {
            $resultRedirect = $this->prepareRedirect('checkout', array('_fragment' => 'payment'), $msg);
        }

        return $resultRedirect;
    }

    protected function prepareRedirect($routePath, $routeParams = null, $message = '')
    {
        if ($message != '') {
            $this->messageManager->addErrorMessage(__($message));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->_url->getUrl($routePath, $routeParams));

        return $resultRedirect;
    }
}
