<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author Gildas Rossignon <gildas@ginidev.com>
 * @package Pledg_PledgPaymentGateway
 */

namespace Pledg\PledgPaymentGateway\Controller\Checkout;

/**
 * @package Pledg\PledgPaymentGateway\Controller\Checkout
 */
class Cancel extends AbstractAction {

    public function execute() {
        $orderId = $this->getRequest()->get('orderId');
        $order =  $this->getOrderById($orderId);

        if ($order && $order->getId()) {
            $this->getLogger()->debug('Requested order cancellation by customer. OrderId: ' . $order->getIncrementId());
            $this->getCheckoutHelper()->cancelCurrentOrder("Pledg: ".($order->getId())." was cancelled by the customer.");
            $this->getCheckoutHelper()->restoreQuote(); //restore cart
            $this->getMessageManager()->addWarningMessage(__("Votre paiement Pledg a bien été annulé. Merci de 'Mettre à jour le panier'."));
        }
        $this->_redirect('checkout/cart');
    }

}
