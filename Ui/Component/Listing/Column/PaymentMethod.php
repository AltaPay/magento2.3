<?php

namespace SDM\Altapay\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class PaymentMethod extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => &$items) {
				$order_id = $items["entity_id"];
				if(isset($items["order_id"])){
				  $order_id = $items["order_id"];
				}
                $order  = $this->_orderRepository->get($order_id);
                $dataSource['data']['items'][$key]['payment_method_title'] = $order->getPayment()->getMethodInstance()->getConfigData('title', $order->getStoreId());
            }
        }
        return $dataSource;
    }
}
