<?php
/**
 * Valitor Module for Magento 2.x.
 *
 * Copyright © 2020 Valitor. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SDM\Valitor\Model;

use Valitor\Api\Ecommerce\Callback;
use Valitor\Api\Ecommerce\PaymentRequest;
use Valitor\Api\Test\TestAuthentication;
use Valitor\Exceptions\ClientException;
use Valitor\Exceptions\ResponseHeaderException;
use Valitor\Exceptions\ResponseMessageException;
use Valitor\Request\Address;
use Valitor\Request\Config;
use Valitor\Request\Customer;
use Valitor\Request\OrderLine;
use Valitor\Response\CallbackResponse;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Catalog\Helper\Data as Taxhelper;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use SDM\Valitor\Model\ConstantConfig;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use SDM\Valitor\Helper\Data;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
use Magento\Tax\Model\Config as taxConfig;
use Magento\Customer\Api\CustomerRepositoryInterface;
use SDM\Valitor\Model\TokenFactory;
use Magento\Sales\Model\OrderFactory;

class Generator
{
    const MODULE_CODE = 'SDM_Valitor';
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Helper Data
     */
    private $helper;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var Quote
     */
    private $quote;
    /**
     * @var UrlInterface
     */
    private $urlInterface;
    /**
     * @var Taxhelper
     */
    private $taxHelper;
    /**
     * @var PaymentData
     */
    private $paymentData;
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Http
     */
    private $request;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;
    /**
     * @var SystemConfig
     */
    private $systemConfig;
    /**
     * @var Monolog
     */
    private $_logger;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $_invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $_transaction;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $_orderRepository;
    /**
     * The ShipmentNotifier class is used to send a notification email to the customer.
     *
     * @var ShipmentNotifier
     */
    private $_shipmentNotifier;
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    private $transactionFactory;
    /**
     * @var rule
     */
    protected $rule;
    /**
     * @var taxItem
     */
    protected $taxItem;
    /**
     * @var taxConfig
     */
    private $taxConfig;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;
    /**
     * @var TokenFactory
     */
    private $dataToken;
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     *
     * @param Quote                       $quote
     * @param UrlInterface                $urlInterface
     * @param PaymentData                 $paymentData
     * @param Session                     $checkoutSession
     * @param Http                        $request
     * @param Order                       $order
     * @param OrderSender                 $orderSender
     * @param InvoiceSender               $invoiceSender
     * @param SystemConfig                $systemConfig
     * @param Monolog                     $_logger
     * @param OrderFactory                $orderFactory
     * @param ModuleListInterface         $moduleList
     * @param ProductMetadataInterface    $productMetadata
     * @param InvoiceService              $invoiceService
     * @param Transaction                 $transaction
     * @param OrderRepositoryInterface    $orderRepository
     * @param ShipmentNotifier            $shipmentNotifier
     * @param TransactionFactory          $transactionFactory
     * @param Taxhelper                   $taxHelper
     * @param ScopeConfigInterface        $scopeConfig
     * @param Data                        $helper
     * @param RuleFactory                 $rule
     * @param Item                        $taxItem
     * @param taxConfig                   $taxConfig
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param TokenFactory                $dataToken
     */
    public function __construct(
        Quote $quote,
        UrlInterface $urlInterface,
        PaymentData $paymentData,
        Session $checkoutSession,
        Http $request,
        Order $order,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        SystemConfig $systemConfig,
        Monolog $_logger,
        OrderFactory $orderFactory,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
        InvoiceService $invoiceService,
        Transaction $transaction,
        OrderRepositoryInterface $orderRepository,
        ShipmentNotifier $shipmentNotifier,
        TransactionFactory $transactionFactory,
        Taxhelper $taxHelper,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        RuleFactory $rule,
        Item $taxItem,
        taxConfig $taxConfig,
        CustomerRepositoryInterface $customerRepositoryInterface,
        TokenFactory $dataToken
    ) {
        $this->quote                       = $quote;
        $this->urlInterface                = $urlInterface;
        $this->paymentData                 = $paymentData;
        $this->checkoutSession             = $checkoutSession;
        $this->request                     = $request;
        $this->order                       = $order;
        $this->orderSender                 = $orderSender;
        $this->systemConfig                = $systemConfig;
        $this->_logger                     = $_logger;
        $this->orderFactory                = $orderFactory;
        $this->moduleList                  = $moduleList;
        $this->productMetadata             = $productMetadata;
        $this->_invoiceService             = $invoiceService;
        $this->_transaction                = $transaction;
        $this->_orderRepository            = $orderRepository;
        $this->invoiceSender               = $invoiceSender;
        $this->_shipmentNotifier           = $shipmentNotifier;
        $this->transactionFactory          = $transactionFactory;
        $this->taxHelper                   = $taxHelper;
        $this->scopeConfig                 = $scopeConfig;
        $this->helper                      = $helper;
        $this->rule                        = $rule;
        $this->taxItem                     = $taxItem;
        $this->taxConfig                   = $taxConfig;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->dataToken                   = $dataToken;
    }

    /**
     * Generate parameters
     *
     * @param int    $terminalId
     * @param string $orderId
     *
     * @return array
     * @throws \Exception
     */
    public function createRequest($terminalId, $orderId)
    {
        $order = $this->order->load($orderId);
        if ($order->getId()) {
            $storeScope       = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $storePriceIncTax = $this->storePriceIncTax();
            $storeCode        = $order->getStore()->getCode();
            $store            = $order->getStore();
            $couponCode       = $order->getDiscountDescription();
            $appliedRule      = $order->getAppliedRuleIds();
            $couponCodeAmount = number_format($order->getDiscountAmount(), 2, '.', '');
            //Test the conn with the Payment Gateway
            $auth     = $this->systemConfig->getAuth($storeCode);
            $api      = new TestAuthentication($auth);
            $response = $api->call();

            $terminalName = $this->systemConfig->getTerminalConfig(
                $terminalId,
                'terminalname',
                $storeScope,
                $storeCode
            );
            if (!$response) {
                $this->restoreOrderFromOrderId($order->getIncrementId());
                $requestParams['result']  = __(ConstantConfig::ERROR);
                $requestParams['message'] = __(ConstantConfig::AUTH_MESSAGE);

                return $requestParams;
            }
            //Transaction Info
            $transactionDetail = $this->helper->transactionDetail($orderId);

            $request = new PaymentRequest($auth);
            $request->setTerminal($terminalName)
                    ->setShopOrderId($order->getIncrementId())
                    ->setAmount((float)number_format($order->getGrandTotal(), 2, '.', ''))
                    ->setCurrency($order->getOrderCurrencyCode())
                    ->setCustomerInfo($this->setCustomer($order))
                    ->setConfig($this->setConfig())
                    ->setTransactionInfo($transactionDetail)
                    ->setSalesTax((float)number_format($order->getTaxAmount(), 2, '.', ''))
                    ->setCookie($_SERVER['HTTP_COOKIE']);

            $post = $this->request->getPostValue();

            if (isset($post['tokenid'])) {
                $model      = $this->dataToken->create();
                $collection = $model->getCollection()->addFieldToFilter('id', $post['tokenid'])->getFirstItem();
                $data       = $collection->getData();
                if (!empty($data)) {
                    $token = $data['token'];
                    $request->setCcToken($token);
                }
            }

            if ($fraud = $this->systemConfig->getTerminalConfig($terminalId, 'fraud', $storeScope, $storeCode)) {
                $request->setFraudService($fraud);
            }

            if ($lang = $this->systemConfig->getTerminalConfig($terminalId, 'language', $storeScope, $storeCode)) {
                $langArr = explode('_', $lang, 2);
                if (isset($langArr[0])) {
                    $language = $langArr[0];
                    $request->setLanguage($language);
                }
            }

            $autoCaptureEnable = $this->systemConfig->getTerminalConfig(
                $terminalId,
                'capture',
                $storeScope,
                $storeCode
            );
            if ($autoCaptureEnable) {
                $request->setType('paymentAndCapture');
            }

            $orderlines   = [];
            $sendShipment = false;
            //get shipping information
            $compAmount         = $order->getShippingDiscountTaxCompensationAmount();
            $shippingTax        = $order->getShippingTaxAmount();
            $shippingAmount     = $order->getShippingAmount();
            $shippingTaxPercent = $this->getOrderShippingTax($order->getId());
            $beforeDiscountComp = false;
            $discountOnAllItems = $this->allItemsHaveDiscount($order->getAllVisibleItems());

            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllItems() as $item) {
                $productType          = $item->getProductType();
                $productOriginalPrice = number_format($item->getBaseOriginalPrice(), 2, '.', '');
                $taxPercent           = $item->getTaxPercent();
                $taxRate              = (1 + $taxPercent / 100);
                $quantity             = $item->getQtyOrdered();
                $appliedRule          = $item->getAppliedRuleIds();
                $itemDiscount         = 0;
                $parentItem           = $item->getParentItem();
                $itemName             = $item->getName();
                $discountAmount       = $item->getBaseDiscountAmount();
                $parentItemType       = "";

                if ($parentItem) {
                    $parentItemType = $parentItem->getProductType();
                    if ($parentItemType == "bundle") {
                        $appliedRule = $parentItem->getAppliedRuleIds();
                    }
                }

                if ($productType == "configurable") {
                    $options = $item->getProductOptions();
                    if (isset($options["simple_name"])) {
                        $itemName = $options["simple_name"];
                    }
                }

                if ($productType != "bundle" && $parentItemType != "configurable") {
                    if ($productOriginalPrice == 0) {
                        $productOriginalPrice = $item->getPriceInclTax();
                    }

                    if ($storePriceIncTax) {
                        $unitPriceWithoutTax = $productOriginalPrice / $taxRate;
                        $unitPrice           = bcdiv($unitPriceWithoutTax, 1, 2);
                    } else {
                        $unitPrice           = $productOriginalPrice;
                        $unitPriceWithoutTax = $productOriginalPrice;
                    }
                    
                    if ($discountAmount > 0) {
                        $itemDiscount = ($discountAmount * 100) / ($productOriginalPrice * $quantity);
                    } else {
                        $itemDiscount = 0;
                    }

                    $orderline = new OrderLine(
                        $itemName,
                        $item->getItemId(),
                        $item->getQtyOrdered(),
                        $unitPrice
                    );
                    if ($productType != 'virtual' && $productType != 'downloadable') {
                        $sendShipment = true;
                    }
                    $orderline->setGoodsType('item');
                    //in case of cart rule discount, send tax after discount
                    $dataForPrice = $this->dataForPrice($item, $unitPrice, $couponCode, $itemDiscount);
                    $taxAmount    = number_format($dataForPrice["rawTaxAmount"], 2, '.', '');

                    if ($discountOnAllItems) {
                        $discountedAmount = 0;
                    } else {
                        $discountedAmount = $dataForPrice["discount"];
                    }
                    $catalogDiscountCheck = $dataForPrice["catalogDiscount"];

                    $discountedAmount           = number_format($discountedAmount, 2, '.', '');
                    $orderline->discount        = $discountedAmount;
                    $roundingCompensationAmount = $this->compensationAmountCal(
                        $item,
                        $unitPrice,
                        $unitPriceWithoutTax,
                        $taxAmount,
                        $discountedAmount,
                        $couponCodeAmount,
                        $storePriceIncTax,
                        $catalogDiscountCheck
                    );

                    $orderline->taxAmount  = $taxAmount + $item->getWeeeTaxAppliedRowAmount();
                    $orderline->taxPercent = $taxPercent;
                    $orderline->productUrl = $item->getProduct()->getProductUrl();
                    $productThumb          = $item->getProduct()->getThumbnail();
                    if (!empty($productThumb) && $productThumb !== 'no_selection') {
                        $orderline->imageUrl = $this->getProductImageUrl($order, $productThumb);
                    }
                    if ($quantity > 1) {
                        $orderline->unitCode = "units";
                    } else {
                        $orderline->unitCode = "unit";
                    }

                    $orderlines[] = $orderline;
                    if ($roundingCompensationAmount > 0 || $roundingCompensationAmount < 0) {
                        $orderline             = new OrderLine(
                            "Compensation Amount",
                            "comp-" . $item->getItemId(),
                            1,
                            $roundingCompensationAmount
                        );
                        $orderline->taxAmount  = 0;
                        $orderline->taxPercent = 0;
                        $orderline->unitCode   = "unit";
                        $orderline->discount   = 0;
                        $orderlines[]          = $orderline;
                    }
                }
            }

            /* Code for shipment */
            if ($sendShipment) {
                $shippingaddress = $order->getShippingMethod(true);
                $method          = isset($shippingaddress['method']) ? $shippingaddress['method'] : '';
                $carrier_code    = isset($shippingaddress['carrier_code']) ? $shippingaddress['carrier_code'] : '';
                //after discount tax case
                if (!empty($shippingaddress)) {
                    $orderline = new OrderLine(
                        $method,
                        $carrier_code,
                        1,
                        $shippingAmount
                    );
                    if ($discountOnAllItems) {
                        $orderline->discount = 0;
                    } else {
                        $orderline->discount = ($order->getShippingDiscountAmount() / $order->getShippingAmount()) * 100;
                    }
                    if ($shippingTaxPercent > 0) {
                        $shippingTax = $shippingAmount * ($shippingTaxPercent / 100);
                        $shippingTax = number_format($shippingTax, 2, '.', '');
                    }
                    $orderline->taxAmount  = $shippingTax;
                    $orderline->taxPercent = $shippingTaxPercent;
                    $orderline->setGoodsType('shipment');
                    $orderlines[] = $orderline;

                    if ($compAmount > 0 && $discountOnAllItems == false) {
                        /*Add tax percentage in compensation amount*/
                        $compAmount = $compAmount + ($compAmount * ($shippingTaxPercent / 100));

                        $orderline    = new OrderLine(
                            "Shipping compensation",
                            "comp-ship",
                            1,
                            $compAmount
                        );
                        $orderlines[] = $orderline;
                    }
                }
            }

            if ($discountOnAllItems == true && ((abs($couponCodeAmount) > 0) || !(empty($appliedRules)))) {
                if (empty($couponCode)) {
                    $couponCode = 'Cart Price Rule';
                }
                // Handling price reductions
                $orderline = new OrderLine(
                    $couponCode,
                    'discount',
                    1,
                    $couponCodeAmount
                );
                $orderline->setGoodsType('handling');
                $orderlines[] = $orderline;
            }

            $request->setOrderLines($orderlines);

            try {
                /** @var \Valitor\Response\PaymentRequestResponse $response */
                $response                 = $request->call();
                $requestParams['result']  = __(ConstantConfig::SUCCESS);
                $requestParams['formurl'] = $response->Url;
                // set before payment status
                $orderStatusBefore = $this->systemConfig->getStatusConfig('before', $storeScope, $storeCode);
                if ($orderStatusBefore) {
                    $this->setCustomOrderStatus($order, Order::STATE_NEW, 'before');
                }
                // set notification
                $order->addStatusHistoryComment(__(ConstantConfig::REDIRECT_TO_VALITOR) . $response->PaymentRequestId);
                $extensionAttribute = $order->getExtensionAttributes();
                if ($extensionAttribute && $extensionAttribute->getValitorPaymentFormUrl()) {
                    $extensionAttribute->setValitorPaymentFormUrl($response->Url);
                }

                $order->setValitorPaymentFormUrl($response->Url);
                $order->setValitorPriceIncludesTax($this->storePriceIncTax());
                $order->getResource()->save($order);

                //set check when user redirect
                $this->checkoutSession->setValitorCustomerRedirect(true);

                return $requestParams;
            } catch (ClientException $e) {
                $requestParams['result']  = __(ConstantConfig::ERROR);
                $requestParams['message'] = $e->getResponse()->getBody();
            } catch (ResponseHeaderException $e) {
                $requestParams['result']  = __(ConstantConfig::ERROR);
                $requestParams['message'] = $e->getHeader()->ErrorMessage;
            } catch (ResponseMessageException $e) {
                $requestParams['result']  = __(ConstantConfig::ERROR);
                $requestParams['message'] = $e->getMessage();
            } catch (\Exception $e) {
                $requestParams['result']  = __(ConstantConfig::ERROR);
                $requestParams['message'] = $e->getMessage();
            }

            $this->restoreOrderFromOrderId($order->getIncrementId());

            return $requestParams;
        }

        $this->restoreOrderFromOrderId($order->getIncrementId());
        $requestParams['result']  = __(ConstantConfig::ERROR);
        $requestParams['message'] = __(ConstantConfig::ERROR_MESSAGE);

        return $requestParams;
    }

    /**
     * @param $item
     * @param $unitPrice
     * @param $couponCode
     * @param $itemDiscount
     *
     * @return mixed
     */
    private function dataForPrice(
        $item,
        $unitPrice,
        $couponCode,
        $itemDiscount
    ) {
        $data["catalogDiscount"] = false;
        $taxPercent              = $item->getTaxPercent();
        $quantity                = $item->getQtyOrdered();
        $originalPrice           = $item->getBaseOriginalPrice();
        if ($this->storePriceIncTax()) {
            $price = $item->getPriceInclTax();
        } else {
            $price = $item->getPrice();
        }
        $data["rawTaxAmount"] = ($unitPrice * ($taxPercent / 100)) * $quantity;
        if ($originalPrice > $price && empty($couponCode)) {
            $data["catalogDiscount"] = true;
            $discountAmount          = (($originalPrice - $price) / $originalPrice) * 100;
            $data["discount"]        = number_format($discountAmount, 2, '.', '');
            $data["rawTaxAmount"]    = (($unitPrice * $taxPercent) / 100) * $quantity;
        } else {
            $data["discount"] = $itemDiscount;
        }

        return $data;
    }

    /**
     * @param $orderId
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function restoreOrderFromOrderId($orderId)
    {
        $order = $this->loadOrderFromOrderId($orderId);
        if ($order->getId()) {
            $quote = $this->quote->loadByIdWithoutStore($order->getQuoteId());
            $quote->setIsActive(1)->setReservedOrderId(null);
            $quote->getResource()->save($quote);
            $this->checkoutSession->replaceQuote($quote);
        }
    }

    /**
     * @param      $order
     * @param bool $requireCapture
     */
    public function createInvoice($order, $requireCapture = false)
    {
        if (filter_var($requireCapture, FILTER_VALIDATE_BOOLEAN) === true) {
            $captureType = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE;
        } else {
            $captureType = \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE;
        }

        if (!$order->getInvoiceCollection()->count()) {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase($captureType);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $transaction = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
            $transaction->save();
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     * @throws \Exception
     */
    public function restoreOrderFromRequest(RequestInterface $request)
    {
        $callback = new Callback($request->getPostValue());
        $response = $callback->call();
        if ($response) {
            $order = $this->loadOrderFromCallback($response);
            if ($order->getQuoteId()) {
                if ($quote = $this->quote->loadByIdWithoutStore($order->getQuoteId())) {
                    $quote->setIsActive(1)
                          ->setReservedOrderId(null)
                          ->save();
                    $this->checkoutSession->replaceQuote($quote);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     */
    public function handleNotificationAction(RequestInterface $request)
    {
        $this->completeCheckout(__(ConstantConfig::NOTIFICATION_CALLBACK), $request);
    }

    /**
     * @param RequestInterface $request
     * @param                  $responseStatus
     *
     * @throws \Exception
     */
    public function handleCancelStatusAction(RequestInterface $request, $responseStatus)
    {
        $stateWhenRedirectCancel  = Order::STATE_CANCELED;
        $statusWhenRedirectCancel = Order::STATE_CANCELED;
        $responseComment          = __(ConstantConfig::CONSUMER_CANCEL_PAYMENT);
        if ($responseStatus != 'cancelled') {
            $responseComment = __(ConstantConfig::UNKNOWN_PAYMENT_STATUS_MERCHANT);
        }
        $historyComment = __(ConstantConfig::CANCELLED) . '|' . $responseComment;
        //TODO: fetch the MerchantErrorMessage and use it as historyComment
        $callback = new Callback($request->getPostValue());
        $response = $callback->call();
        if ($response) {
            $order             = $this->loadOrderFromCallback($response);
            $storeCode         = $order->getStore()->getCode();
            $storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $orderStatusCancel = $this->systemConfig->getStatusConfig('cancel', $storeScope, $storeCode);

            if ($orderStatusCancel) {
                $statusWhenRedirectCancel = $orderStatusCancel;
            }
            $this->handleOrderStateAction(
                $request,
                $stateWhenRedirectCancel,
                $statusWhenRedirectCancel,
                $historyComment
            );
        }
    }

    /**
     * @param RequestInterface $request
     * @param                  $msg
     * @param                  $merchantErrorMsg
     * @param                  $responseStatus
     *
     * @throws \Exception
     */
    public function handleFailedStatusAction(
        RequestInterface $request,
        $msg,
        $merchantErrorMsg,
        $responseStatus
    ) {
        $historyComment = $responseStatus . '|' . $msg;
        if (!is_null($merchantErrorMsg)) {
            $historyComment = $responseStatus . '|' . $msg . '|' . $merchantErrorMsg;
        }
        $transInfo = null;
        $callback  = new Callback($request->getPostValue());
        $response  = $callback->call();
        if ($response) {
            $order     = $this->loadOrderFromCallback($response);
            $transInfo = sprintf(
                "Transaction ID: %s - Payment ID: %s - Credit card token: %s",
                $response->transactionId,
                $response->paymentId,
                $response->creditCardToken
            );

            //check if order status set in configuration
            $stateWhenRedirectFail  = Order::STATE_CANCELED;
            $statusWhenRedirectFail = Order::STATE_CANCELED;
            $storeCode              = $order->getStore()->getCode();
            $storeScope             = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $orderStatusCancel      = $this->systemConfig->getStatusConfig('cancel', $storeScope, $storeCode);

            if ($orderStatusCancel) {
                $statusWhenRedirectFail = $orderStatusCancel;
            }

            $this->handleOrderStateAction(
                $request,
                $stateWhenRedirectFail,
                $statusWhenRedirectFail,
                $historyComment,
                $transInfo
            );
        }
    }

    /**
     * @param RequestInterface $request
     * @param string           $orderState
     * @param string           $orderStatus
     * @param string           $historyComment
     * @param null             $transactionInfo
     *
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function handleOrderStateAction(
        RequestInterface $request,
        $orderState = Order::STATE_NEW,
        $orderStatus = Order::STATE_NEW,
        $historyComment = "Order state changed",
        $transactionInfo = null
    ) {
        $callback = new Callback($request->getPostValue());
        $response = $callback->call();
        if ($response) {
            $order = $this->loadOrderFromCallback($response);
            $order->setState($orderState);
            $order->setIsNotified(false);
            if (!is_null($transactionInfo)) {
                $order->addStatusHistoryComment($transactionInfo);
            }
            $order->addStatusHistoryComment($historyComment, $orderStatus);
            $order->getResource()->save($order);

            return true;
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     */
    public function handleOkAction(RequestInterface $request)
    {
        $this->completeCheckout(__(ConstantConfig::OK_CALLBACK), $request);
    }

    /**
     * @param                  $comment
     * @param RequestInterface $request
     *
     * @throws \Exception
     */
    private function completeCheckout($comment, RequestInterface $request)
    {
        $callback       = new Callback($request->getParams());
        $response       = $callback->call();
        $paymentStatus  = $response->type;
        $requireCapture = $response->requireCapture;
        if ($response) {
            $order      = $this->loadOrderFromCallback($response);
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $storeCode  = $order->getStore()->getCode();
            if ($order->getId()) {
                $cardType = '';
                $expires  = '';
                if (isset($response->Transactions[0])) {
                    $transaction = $response->Transactions[0];
                    if (isset($transaction->CreditCardExpiry->Month) && isset($transaction->CreditCardExpiry->Year)) {
                        $expires = $transaction->CreditCardExpiry->Month . '/' . $transaction->CreditCardExpiry->Year;
                    }
                    if (isset($transaction->PaymentSchemeName)) {
                        $cardType = $transaction->PaymentSchemeName;
                    }
                }
                $payment = $order->getPayment();
                $payment->setPaymentId($response->paymentId);
                $payment->setLastTransId($response->transactionId);
                $payment->setCcTransId($response->creditCardToken);
                $payment->setAdditionalInformation('cc_token', $response->creditCardToken);
                $payment->setAdditionalInformation('masked_credit_card', $response->maskedCreditCard);
                $payment->setAdditionalInformation('expires', $expires);
                $payment->setAdditionalInformation('card_type', $cardType);
                $payment->save();
                $currentStatus        = $order->getStatus();
                $orderHistories       = $order->getStatusHistories();
                $latestHistoryComment = array_pop($orderHistories);
                $prevStatus           = $latestHistoryComment->getStatus();
                $sendMail             = true;
                if (strpos($comment, ConstantConfig::NOTIFICATION_CALLBACK) !== false
                    && $currentStatus == $prevStatus
                ) {
                    $sendMail = false;
                }
                //If the product is shipping product then check
                $shippedProduct = false;
                if (!$order->getEmailSent() && $sendMail == true) {
                    $this->orderSender->send($order);
                }
                foreach ($order->getAllVisibleItems() as $item) {
                    $productType = $item->getProductType();
                    if ($productType != 'virtual' && $productType != 'downloadable') {
                        $shippedProduct = true;
                    }
                }
                //unset redirect if success
                $this->checkoutSession->unsValitorCustomerRedirect();

                $isCaptured = false;
                foreach (SystemConfig::getTerminalCodes() as $terminalName) {
                    if ($this->systemConfig->getTerminalConfigFromTerminalName(
                            $terminalName,
                            'terminalname',
                            $storeScope,
                            $storeCode
                        ) === $response->Transactions[0]->Terminal
                    ) {
                        $isCaptured = $this->systemConfig->getTerminalConfigFromTerminalName(
                            $terminalName,
                            'capture',
                            $storeScope,
                            $storeCode
                        );
                        break;
                    }
                }
                $orderStatusAfterPayment = $this->systemConfig->getStatusConfig('process', $storeScope, $storeCode);
                $orderStatus_capture     = $this->systemConfig->getStatusConfig('autocapture', $storeScope, $storeCode);

                if ($isCaptured) {
                    if ($orderStatus_capture == "complete") {
                        if ($shippedProduct) {
                            $this->setCustomOrderStatus($order, Order::STATE_COMPLETE, 'autocapture');
                            $order->addStatusHistoryComment(__(ConstantConfig::PAYMENT_COMPLETE));
                        } else {
                            $order->addStatusToHistory($orderStatus_capture, ConstantConfig::PAYMENT_COMPLETE, false);
                        }
                    } else {
                        $this->setCustomOrderStatus($order, Order::STATE_PROCESSING, 'process');
                    }
                } else {
                    if ($orderStatusAfterPayment) {
                        $this->setCustomOrderStatus($order, $orderStatusAfterPayment, 'process');
                    } else {
                        $this->setCustomOrderStatus($order, Order::STATE_PROCESSING, 'process');
                    }
                }
                $order->addStatusHistoryComment(
                    sprintf(
                        "Transaction ID: %s - Payment ID: %s - Credit card token: %s",
                        $response->transactionId,
                        $response->paymentId,
                        $response->creditCardToken
                    )
                );
                $order->setIsNotified(false);
                $order->getResource()->save($order);
                if (strtolower($paymentStatus) == 'paymentandcapture') {
                    $this->createInvoice($order, $requireCapture);
                }
            }
        }
    }

    /**
     * @param CallbackResponse $response
     *
     * @return Order
     */
    private function loadOrderFromCallback(CallbackResponse $response)
    {
        return $this->orderFactory->create()->loadByIncrementId($response->shopOrderId);
    }

    /**
     * @param string $orderId
     *
     * @return Order
     */
    private function loadOrderFromOrderId($orderId)
    {
        return $this->order->loadByIncrementId($orderId);
    }

    /**
     * @param Order $order
     * @param       $state
     * @param       $statusKey
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function setCustomOrderStatus(Order $order, $state, $statusKey)
    {
        $order->setState($state);
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeCode  = $order->getStore()->getCode();
        if ($status = $this->systemConfig->getStatusConfig($statusKey, $storeScope, $storeCode)) {
            $order->setStatus($status);
        }
        $order->getResource()->save($order);
    }

    /**
     * @return Config
     */
    private function setConfig()
    {
        $config = new Config();
        $config->setCallbackOk($this->urlInterface->getDirectUrl(ConstantConfig::VALITOR_OK));
        $config->setCallbackFail($this->urlInterface->getDirectUrl(ConstantConfig::VALITOR_FAIL));
        $config->setCallbackRedirect($this->urlInterface->getDirectUrl(ConstantConfig::VALITOR_REDIRECT));
        $config->setCallbackOpen($this->urlInterface->getDirectUrl(ConstantConfig::VALITOR_OPEN));
        $config->setCallbackNotification($this->urlInterface->getDirectUrl(ConstantConfig::VALITOR_NOTIFICATION));
        //$config->setCallbackVerifyOrder($this->urlInterface->getDirectUrl(ConstantConfig::VERIFY_ORDER));
        $config->setCallbackForm($this->urlInterface->getDirectUrl(ConstantConfig::VALITOR_CALLBACK));

        return $config;
    }

    /**
     * @param Order $order
     *
     * @return Customer
     */
    private function setCustomer(Order $order)
    {
        $billingAddress = new Address();
        if ($order->getBillingAddress()) {
            $address                    = $order->getBillingAddress()->convertToArray();
            $billingAddress->Email      = $order->getBillingAddress()->getEmail();
            $billingAddress->Firstname  = $address['firstname'];
            $billingAddress->Lastname   = $address['lastname'];
            $billingAddress->Address    = $address['street'];
            $billingAddress->City       = $address['city'];
            $billingAddress->PostalCode = $address['postcode'];
            $billingAddress->Region     = $address['region'] ?: '0';
            $billingAddress->Country    = $address['country_id'];
        }
        $customer = new Customer($billingAddress);

        if ($order->getShippingAddress()) {
            $address                     = $order->getShippingAddress()->convertToArray();
            $shippingAddress             = new Address();
            $shippingAddress->Email      = $order->getShippingAddress()->getEmail();
            $shippingAddress->Firstname  = $address['firstname'];
            $shippingAddress->Lastname   = $address['lastname'];
            $shippingAddress->Address    = $address['street'];
            $shippingAddress->City       = $address['city'];
            $shippingAddress->PostalCode = $address['postcode'];
            $shippingAddress->Region     = $address['region'] ?: '0';
            $shippingAddress->Country    = $address['country_id'];
            $customer->setShipping($shippingAddress);
        } else {
            $customer->setShipping($billingAddress);
        }

        if ($order->getBillingAddress()) {
            $customer->setEmail($order->getBillingAddress()->getEmail());
            $customerPhone = $order->getBillingAddress()->getTelephone();
        } elseif ($order->getShippingAddress()) {
            $customer->setEmail($order->getShippingAddress()->getEmail());
            $customerPhone = $order->getShippingAddress()->getTelephone();
        } else {
            $customer->setEmail($order->getBillingAddress()->getEmail());
            $customerPhone = $order->getBillingAddress()->getTelephone();
        }

        $customer->setPhone(str_replace(' ', '', $customerPhone));

        if (!$order->getCustomerIsGuest()) {
            $customer->setUsername($order->getCustomerId());
            $cst       = $this->customerRepositoryInterface->getById($order->getCustomerId());
            $createdAt = $cst->getCreatedAt();
            $customer->setCreatedDate(new \DateTime($createdAt));
        }

        return $customer;
    }

    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @param $orderItems
     *
     * @return bool
     */
    private function allItemsHaveDiscount($orderItems)
    {
        $discountOnAllItems = true;
        foreach ($orderItems as $item) {
            $appliedRule = $item->getAppliedRuleIds();
            $productType = $item->getProductType();
            if (!empty($appliedRule)) {
                $appliedRuleArr = explode(",", $appliedRule);
                foreach ($appliedRuleArr as $ruleId) {
                    $couponCodeData  = $this->rule->create()->load($ruleId);
                    $applyToShipping = $couponCodeData->getData('apply_to_shipping');
                    if (!$applyToShipping && $productType != 'virtual' && $productType != 'downloadable') {
                        $discountOnAllItems = false;
                    }
                }
            } else {
                $discountOnAllItems = false;
            }
        }

        return $discountOnAllItems;
    }

    /**
     * @param $orderID
     *
     * @return int
     */
    private function getOrderShippingTax($orderID)
    {
        $shippingTaxPercent = 0;
        $tax_items          = $this->taxItem->getTaxItemsByOrderId($orderID);
        if (!empty($tax_items) && is_array($tax_items)) {
            foreach ($tax_items as $item) {
                if ($item['taxable_item_type'] === 'shipping') {
                    $shippingTaxPercent += $item['tax_percent'];
                }
            }
        }

        return $shippingTaxPercent;
    }


    /**
     * @param $store
     *
     * @return bool
     */
    private function checkSettingsTaxAfterDiscount($store = null)
    {
        return $this->taxConfig->applyTaxAfterDiscount($store);
    }

    /**
     * @param $order
     *
     * @return bool
     */
    private function storePriceIncTax($order = null)
    {
        if ($order !== null) {
            if ($order->getValitorPriceIncludesTax() !== null) {
                return $order->getValitorPriceIncludesTax();
            }
        }
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ((int)$this->scopeConfig->getValue('tax/calculation/price_includes_tax', $storeScope) === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $item
     * @param $unitPrice
     * @param $unitPriceWithoutTax
     * @param $taxAmount
     * @param $discountedAmount
     * @param $couponCodeAmount
     * @param $storePriceIncTax
     * @param $catalogDiscountCheck
     *
     * @return float|int
     */
    private function compensationAmountCal(
        $item,
        $unitPrice,
        $unitPriceWithoutTax,
        $taxAmount,
        $discountedAmount,
        $couponCodeAmount,
        $storePriceIncTax,
        $catalogDiscountCheck
    ) {
        $taxPercent   = $item->getTaxPercent();
        $quantity     = $item->getQtyOrdered();
        $compensation = 0;
        //Discount compensation calculation - Gateway calculation pattern
        $gatewaySubTotal = ($unitPrice * $quantity) + $taxAmount;
        $gatewaySubTotal = $gatewaySubTotal - ($gatewaySubTotal * ($discountedAmount / 100));
        // Magento calculation pattern
        if (abs($couponCodeAmount) > 0 && $storePriceIncTax) {
            $cmsPriceCal  = $unitPriceWithoutTax * $quantity;
            $cmsTaxCal    = $cmsPriceCal * ($taxPercent / 100);
            $cmsSubTotal  = $cmsPriceCal + $cmsTaxCal;
            $cmsSubTotal  = $cmsSubTotal - ($cmsSubTotal * ($discountedAmount / 100));
            $compensation = $cmsSubTotal - $gatewaySubTotal;
        } elseif ($catalogDiscountCheck || empty($couponCodeAmount) || $couponCodeAmount == 0) {
            $cmsSubTotal  = $item->getBaseRowTotal() + $item->getBaseTaxAmount();
            $compensation = $cmsSubTotal - $gatewaySubTotal;
        }

        return $compensation;
    }

    /**
     * Get image url by imagename.
     *
     * @param        $order
     * @param string $image
     *
     * @return string
     */
    protected function getProductImageUrl($order, $image)
    {
        $url = $image;
        if ($image) {
            if (is_string($image)) {
                $url = $order->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/product/' . $image;
            }
        }

        return $url;
    }

    /**
     * @param RequestInterface $request
     * @param                  $avsCode
     * @param                  $historyComment
     *
     * @return bool
     */
    public function avsCheck(RequestInterface $request, $avsCode, $historyComment)
    {
        $checkRejectionCase = false;
        $transInfo          = null;
        $callback           = new Callback($request->getPostValue());
        $response           = $callback->call();
        if ($response) {
            $order                 = $this->loadOrderFromCallback($response);
            $storeScope            = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $storeCode             = $order->getStore()->getCode();
            $transInfo             = sprintf("Transaction ID: %s - Payment ID: %s - Credit card token: %s",
                $response->transactionId,
                $response->paymentId,
                $response->creditCardToken
            );
            $isAvsEnabled          = $this->checkAvsConfig($response, $storeCode, $storeScope, 'avscontrol');
            $isAvsEnforced         = $this->checkAvsConfig($response, $storeCode, $storeScope, 'enforceavs');
            $getAcceptedAvsResults = $this->getAcceptedAvsResults($response, $storeCode, $storeScope);

            if ($isAvsEnabled) {
                if ($isAvsEnforced && empty($avsCode)) {
                    $checkRejectionCase = true;
                } elseif (stripos($getAcceptedAvsResults, $avsCode) === false) {
                    $checkRejectionCase = true;
                }
            }
            if ($checkRejectionCase) {
                //check if order status set in configuration
                $statusKey         = Order::STATE_CANCELED;
                $orderStatusCancel = $this->systemConfig->getStatusConfig('cancel', $storeScope, $storeCode);
                //Save payment info in order to retrieve it for release operation
                if ($order->getId()) {
                    $this->savePaymentData($response, $order);
                }
                if ($orderStatusCancel) {
                    $statusKey = $orderStatusCancel;
                }
                $this->handleOrderStateAction($request, Order::STATE_CANCELED, $statusKey, $historyComment, $transInfo);
            }
        }

        return $checkRejectionCase;
    }

    /**
     * @param $response
     * @param $storeCode
     * @param $storeScope
     * @param $configField
     *
     * @return bool
     */
    public function checkAvsConfig($response, $storeCode, $storeScope, $configField)
    {
        $isEnabled = false;
        foreach (SystemConfig::getTerminalCodes() as $terminalName) {
            $terminalConfig = $this->systemConfig->getTerminalConfigFromTerminalName(
                $terminalName,
                'terminalname',
                $storeScope,
                $storeCode
            );
            if ($terminalConfig === $response->Transactions[0]->Terminal) {
                $isEnabled = $this->systemConfig->getTerminalConfigFromTerminalName(
                    $terminalName,
                    $configField,
                    $storeScope,
                    $storeCode
                );
                break;
            }
        }

        return $isEnabled;
    }

    /**
     * @param $response
     * @param $storeCode
     * @param $storeScope
     *
     * @return |null
     */
    public function getAcceptedAvsResults($response, $storeCode, $storeScope)
    {
        $acceptedAvsResults = null;
        foreach (SystemConfig::getTerminalCodes() as $terminalName) {
            $terminalConfig = $this->systemConfig->getTerminalConfigFromTerminalName(
                $terminalName,
                'terminalname',
                $storeScope,
                $storeCode
            );
            if ($terminalConfig === $response->Transactions[0]->Terminal) {
                $acceptedAvsResults = $this->systemConfig->getTerminalConfigFromTerminalName(
                    $terminalName,
                    'avs_acceptance',
                    $storeScope,
                    $storeCode
                );
                break;
            }
        }

        return $acceptedAvsResults;
    }

    /**
     * @param $response
     * @param $order
     */
    public function savePaymentData($response, $order)
    {
        $payment = $order->getPayment();
        $payment->setPaymentId($response->paymentId);
        $payment->setLastTransId($response->transactionId);
        $payment->save();
    }
}
