<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use TaxJar\Client;
use yii\base\Component;

/**
 * TaxJar tax category service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 *
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
        $this->_client = Client::withApiKey('8bfb0693f9dabfb8df82021456a08f15');
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
}
