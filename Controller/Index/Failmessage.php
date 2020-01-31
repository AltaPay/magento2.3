<?php
namespace SDM\Valitor\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use SDM\Valitor\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Failmessage extends Index implements CsrfAwareActionInterface
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
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

    public function execute()
    {
        $this->writeLog();
        $msg = $this->getRequest()->getParam('msg');
	    $this->logger->debug('messageManager - Error message: ' . $msg);
        $this->messageManager->addErrorMessage($msg);

	    return $this->_redirect('checkout', ['_fragment' => 'payment']);
    }
}
