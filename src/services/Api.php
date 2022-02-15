<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use TaxJar\Client;
use craft\commerce\Plugin;
use craft\commerce\elements\Order;
use craft\commerce\models\Address;
use craft\commerce\taxjar\TaxJar;
use yii\base\Component;

/**
 * TaxJar API service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 *
 *
 * @property \TaxJar\Client $client
 */
class Api extends Component
{
    // Constants
    // =========================================================================

    const TYPE_FROM = 'from';
    const TYPE_TO = 'to';

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
     * @return array
     */
    public function getFromParams(): array
    {
        $storeLocation = Plugin::getInstance()->getAddresses()->getStoreLocationAddress();
        return $this->_getAddressParams(self::TYPE_FROM, $storeLocation);
    }

    /**
     * @param Address $address
     * @return array
     */
    public function getToParams(Address $address): array
    {
        return $this->_getAddressParams(self::TYPE_TO, $address);
    }

    /**
     * @param Order $order
     * @param bool $includeAll
     * @return array
     */
    public function getAmountsParams(Order $order, bool $includeAll = true): array
    {
        return [
            'amount' => $order->getItemSubtotal() + $order->getTotalDiscount() + $order->getTotalShippingCost(),
            'shipping' => $order->getTotalShippingCost(),
            'line_items' => $this->getLineItemsParams($order, $includeAll)
        ];
    }

    /**
     * @param Order $order
     * @param bool $includeAll
     * @return array
     */
    public function getLineItemsParams(Order $order, bool $includeAll = true): array
    {
        $lineItemsParams = [];
        $taxCategories = Plugin::getInstance()->getTaxCategories();
        if ($includeAll) {
            $lineItemTaxes = $this->getLineItemTaxesByLineItemUid($order);
        }

        foreach ($order->getLineItems() as $lineItem) {
            $category = $taxCategories->getTaxCategoryById($lineItem->taxCategoryId);
            $lineItemParams = [
                'id' => $lineItem->uid, // Use UID as it's a consistent identifier even when line item is not yet saved
                'quantity' => $lineItem->qty,
                'unit_price' => $lineItem->salePrice,
                'discount' => $lineItem->getDiscount() < 0 ? $lineItem->getDiscount() * -1 : null,
                'product_tax_code' => $category && $category->handle !== 'general' ? $category->handle : null
            ];

            if ($includeAll) {
                $lineItemParams['product_identifier'] = $lineItem->sku;
                $lineItemParams['description'] = $lineItem->description;
                $lineItemParams['sales_tax'] = isset($lineItemTaxes[$lineItem->uid]) ? $lineItemTaxes[$lineItem->uid] : $lineItem->tax;
            }

            $lineItemsParams[] = $lineItemParams;
        }

        return $lineItemsParams;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getLineItemTaxesByLineItemUid(Order $order): array
    {
        $adjustments = $order->getAdjustmentsByType('tax');
        $taxes = [];

        foreach ($adjustments as $adjustment) {
            if (!$adjustment->lineItemId && !empty($adjustment->sourceSnapshot)) {
                if ($adjustment->amount == 0 && !isset($adjustment->sourceSnapshot['breakdown'])) {
                    foreach ($order->lineItems as $lineItem) {
                        $taxes[$lineItem->uid] = 0.0000;
                    }
                } else {
                    foreach ($adjustment->sourceSnapshot['breakdown']['line_items'] as $lineItem) {
                        $taxes[$lineItem['id']] = $lineItem['tax_collectable'];
                    }
                }
            }
        }

        return $taxes;
    }

    /**
     * @param string $type
     * @param Address $address
     * @return array
     */
    private function _getAddressParams(string $type, Address $address): array
    {
        return [
            $type . '_country' => $address->getCountry()->iso,
            $type . '_zip' => $address->zipCode,
            $type . '_state' => $address->getState()->abbreviation,
            $type . '_city' => $address->city,
            $type . '_street' => $address->address1
        ];
    }
}
