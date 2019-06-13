<?php
namespace SDM\Altapay\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use SDM\Altapay\Controller\Index;

class Redirect extends Index implements CsrfAwareActionInterface
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

        $page = $this->pageFactory->create();
        return $page;
    }

}
