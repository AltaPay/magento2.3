<?php
namespace SDM\Valitor\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use SDM\Valitor\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface; 

class Open extends Index implements CsrfAwareActionInterface
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
        return $this->_redirect('checkout/onepage/success');
    }

}
