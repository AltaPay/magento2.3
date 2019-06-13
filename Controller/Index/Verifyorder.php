<?php
namespace SDM\Altapay\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use SDM\Altapay\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Verifyorder extends Index implements CsrfAwareActionInterface
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
        // @todo verify order
        echo 'OKAY';
        exit;
    }

}
