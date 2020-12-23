<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use TaxJar\Client;
use craft\commerce\taxjar\TaxJar;
use yii\base\Component;

/**
 * TaxJar tax category service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 *
 *
 * @property \TaxJar\Client $client
 * @property mixed $categories
 */
class Api extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var Client
     */
    private $_client;

    /**
     *
     */
    public function init()
    {
        $apiKey = TaxJar::getInstance()->getSettings()->apiKey;
        $this->_client = Client::withApiKey($apiKey);
        if (TaxJar::getInstance()->getSettings()->useSandbox) {
            $this->_client->setApiConfig('api_url', Client::SANDBOX_API_URL);
        }
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->_client->categories();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param $order
     * @return mixed
     */
    public function createOrder($order)
    {
        $taxOrderData = [
          'transaction_id' => $order->id,
          'transaction_date' => $order->datePaid->format('Y/m/d'),
          'to_country' => $order->shippingAddress->country->iso,
          'to_zip' => $order->shippingAddress->zipCode,
          'to_state' => $order->shippingAddress->state ? $order->shippingAddress->state->abbreviation : $order->shippingAddress->stateName,
          'to_city' => $order->shippingAddress->city,
          'to_street' => $order->shippingAddress->address1,
          'amount' => $order->total,
          'shipping' => $order->totalShippingCost,
          'sales_tax' => $order->totalTax,
          'line_items' => []
        ];
        foreach ($order->lineItems as $lineItem)
        {
            $taxOrderData['line_items'][] = [
                'quantity' => $lineItem->qty,
                'product_identifier' => $lineItem->sku,
                'description' => $lineItem->description,
                'unit_price' => $lineItem->total,
                'sales_tax' => $lineItem->tax
            ];
        }
        return $this->_client->createOrder($taxOrderData);
    }
}
