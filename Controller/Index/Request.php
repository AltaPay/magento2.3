<?php
namespace SDM\Altapay\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject;
use SDM\Altapay\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Request extends Index 
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

        if ($this->checkPost()) {
            $params = $this->generator->createRequest(
                $this->getRequest()->getParam('paytype'),
                $this->getRequest()->getParam('orderid')
            );

            $result = new DataObject();
            $response = $this->getResponse();
            $result->addData($params);
            return $response->representJson($result->toJson());
        }

        die('No post!?');

    }
}
