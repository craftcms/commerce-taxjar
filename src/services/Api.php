<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use craft\commerce\taxjar\models\Settings;
use craft\commerce\taxjar\TaxJar;
use TaxJar\Client;
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
    /**
     * @var Client
     */
    private $_client;

    public const HANDLE_PREFIX = "tj_";

    /**
     *
     */
    public function init()
    {
        /** @var Settings $taxJarSettings */
        $taxJarSettings = TaxJar::getInstance()->getSettings();
        $apiKey = $taxJarSettings->apiKey;
        $this->_client = Client::withApiKey($apiKey);
        if ($taxJarSettings->useSandbox) {
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
