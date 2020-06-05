<?php
/**
 * Valitor Module for Magento 2.x.
 *
 * Copyright © 2020 Valitor. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SDM\Valitor\Model\Config\Source\Order\Status;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class Fraud extends Status
{

    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        Order::STATE_PAYMENT_REVIEW,
        Order::STATE_HOLDED,
    ];
}
