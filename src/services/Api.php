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
        if (\Craft::$app->getConfig()->getGeneral()->devMode) {
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
}
