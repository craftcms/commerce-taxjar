<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\commerce\elements\Order;
use craft\commerce\events\TaxEngineEvent;
use craft\commerce\models\Address;
use craft\commerce\Plugin;
use craft\commerce\services\Taxes;
use craft\commerce\taxjar\adjusters\Tax;
use craft\commerce\taxjar\models\Settings;
use craft\commerce\taxjar\services\Api;
use craft\commerce\taxjar\services\Categories;
use craft\commerce\taxjar\engines\TaxJar as TaxJarEngine;
use yii\base\Event;


/**
 * Class TaxJar
 *
 * @author    Pixel & Tonic
 * @package   TaxJar
 * @since     1.0
 *
 * @property \craft\commerce\taxjar\services\Api $api
 */
class TaxJar extends BasePlugin
{

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     *
     */
    public function init()
    {
        $this->_setPluginComponents();
        $this->_registerRoutes();
        $this->_registerHandlers();

        parent::init();
    }

    public function _registerHandlers()
    {
        // We want to be the tax engine for commerce
        Event::on(Taxes::class, Taxes::EVENT_REGISTER_TAX_ENGINE, static function(TaxEngineEvent $e) {
            $e->engine = new TaxJarEngine;
        });

        Event::on(
            Order::class,
            Order::EVENT_AFTER_ORDER_PAID,
            function(Event $event) {
                // @var Order $order
                $order = $event->sender;
                $this->getApi()->createOrder($order);
            }
        );
        
    }

    /**
     * Registered the routes
     */
    private function _registerRoutes()
    {

    }

    /**
     * Returns the categories service
     *
     * @return Api The cart service
     * @throws \yii\base\InvalidConfigException
     */
    public function getApi()
    {
        return $this->get('api');
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Sets the components of the commerce plugin
     */
    private function _setPluginComponents()
    {
        $this->setComponents([
            'api' => Api::class,
        ]);
    }
}
