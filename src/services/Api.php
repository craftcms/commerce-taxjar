<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use craft\commerce\taxjar\models\Settings;
use craft\commerce\taxjar\Plugin;
use TaxJar\Client;
use yii\base\Component;

/**
 * TaxJar API service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 *
 * @property Client $client
 */
class Api extends Component
{
    /**
     * @var Client
     */
    private Client $_client;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        /** @var Settings $settings */
        $settings = Plugin::getInstance()->getSettings();
        $this->_client = Client::withApiKey($settings->getApiKey());

        if ($settings->getUseSandbox()) {
            $this->_client->setApiConfig('api_url', Client::SANDBOX_API_URL);
        }
    }

    /**
     * Returns all tax categories from the TaxJar API.
     */
    public function getCategories(): mixed
    {
        return $this->_client->categories();
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->_client;
    }
}
