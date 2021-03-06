<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author Gildas Rossignon <gildas@ginidev.com>
 * @package Pledg_PledgPaymentGateway
 */

namespace Pledg\PledgPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderFactory;
use Pledg\PledgPaymentGateway\Helper\Crypto;
use Pledg\PledgPaymentGateway\Helper\Data;
use Pledg\PledgPaymentGateway\Helper\Checkout;
use Pledg\PledgPaymentGateway\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

/**
 * @package Pledg\PledgPaymentGateway\Controller\Checkout
 */
abstract class AbstractAction extends Action {

    const LOG_FILE = 'pledg.log';
    const PLEDG_DEFAULT_CURRENCY_CODE = 'EUR';
    const PLEDG_DEFAULT_COUNTRY_CODE = 'FR';

    private $_context;

    private $_checkoutSession;

    private $_orderFactory;

    private $_cryptoHelper;

    private $_dataHelper;

    private $_checkoutHelper;

    private $_gatewayConfig;

    private $_messageManager;

    private $_logger;

    private $_scopeConfig;

    protected $_code;

    public function __construct(
        Config $gatewayConfig,
        Session $checkoutSession,
        Context $context,
        OrderFactory $orderFactory,
        Crypto $cryptoHelper,
        Data $dataHelper,
        Checkout $checkoutHelper,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_cryptoHelper = $cryptoHelper;
        $this->_dataHelper = $dataHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_gatewayConfig = $gatewayConfig;
        $this->_messageManager = $context->getMessageManager();
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
    }

    protected function getContext() {
        return $this->_context;
    }

    protected function getCheckoutSession() {
        return $this->_checkoutSession;
    }

    protected function getOrderFactory() {
        return $this->_orderFactory;
    }

    protected function getCryptoHelper() {
        return $this->_cryptoHelper;
    }

    protected function getDataHelper() {
        return $this->_dataHelper;
    }

    protected function getCheckoutHelper() {
        return $this->_checkoutHelper;
    }

    protected function getGatewayConfig() {
        return $this->_gatewayConfig;
    }

    protected function getMessageManager() {
        return $this->_messageManager;
    }

    protected function getLogger() {
        return $this->_logger;
    }

    protected function getOrder()
    {
        $orderId = $this->_checkoutSession->getLastRealOrderId();

        if (!isset($orderId)) {
            return null;
        }

        return $this->getOrderById($orderId);
    }

    protected function getOrderById($orderId)
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($orderId);

        if (!$order->getId()) {
            return null;
        }

        return $order;
    }

    protected function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    protected function isPledgEnable()
    {
        return $this->_scopeConfig->getValue('payment/pledg_gateway/active');
    }

    protected function isStaging()
    {
        return $this->_scopeConfig->getValue('payment/pledg_gateway/staging');
    }

    protected function getUrlPayment() {
        if ($this->isStaging()) {
            $url = $this->_scopeConfig->getValue('payment/pledg_gateway/staging_url');
        } else {
            $url = $this->_scopeConfig->getValue('payment/pledg_gateway/gateway_url');
        }

        $url .= '?merchantUid=' . $this->getMerchantUid();

        return $url;
    }

    protected function setCode($code) {
        $this->_code = $code;
    }

    protected function getMerchantUid() {
        return $this->_scopeConfig->getValue('payment/'.$this->_code.'/api_key');
    }

    public function getStoreName()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
