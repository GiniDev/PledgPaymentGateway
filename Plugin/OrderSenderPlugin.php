<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author Gildas Rossignon <gildas@ginidev.com>
 * @package Pledg_PledgPaymentGateway
 */

namespace Pledg\PledgPaymentGateway\Plugin;

use Magento\Sales\Model\Order;

class OrderSenderPlugin
{
    public function aroundSend(\Magento\Sales\Model\Order\Email\Sender\OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if($payment === 'pledg_gateway' && $order->getState() !== 'processing'){
            return false;
        }

        return $proceed($order, $forceSyncMode);
    }
}
