<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\queue\jobs;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\taxjar\TaxJar;
use craft\helpers\DateTimeHelper;
use craft\queue\BaseJob;

class CreateOrder extends BaseJob
{
    /**
     * @var int Order ID
     */
    public $orderId;

    public function execute($queue)
    {
        $api = TaxJar::getInstance()->getApi();
        $this->setProgress($queue, 0.1);

        $order = Order::find()->id($this->orderId)->one();
        $this->setProgress($queue, 0.2);

        $from = $api->getFromParams();
        $this->setProgress($queue, 0.3);

        $to = $api->getToParams($order->getShippingAddress());
        $this->setProgress($queue, 0.4);

        $amounts = $api->getAmountsParams($order);
        $this->setProgress($queue, 0.5);

        $createOrderParams = [
            'transaction_id' => $order->id,
            'transaction_date' => DateTimeHelper::toIso8601($order->dateOrdered),
            'sales_tax' => $order->totalTax
        ];
        $this->setProgress($queue, 0.6);

        $orderData = array_merge($from, $to, $amounts, $createOrderParams);
        $this->setProgress($queue, 0.75);

        $api->getClient()->createOrder($orderData);
        $this->setProgress($queue, 1);
    }

    protected function defaultDescription(): string
    {
        return 'Sending TaxJar transaction for order #' . $this->orderId;
    }
}
