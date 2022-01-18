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
use craft\commerce\taxjar\queue\jobs\CreateOrder;
use craft\commerce\taxjar\services\Api;
use craft\commerce\taxjar\engines\TaxJar as TaxJarEngine;
use craft\commerce\taxjar\web\assets\actions\Actions;
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
    public $schemaVersion = '1.1.1';

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

        Craft::$app->view->hook('cp.commerce.order.edit.order-secondary-actions', function(array &$context) {
            if ($context['order']->isCompleted) {
                $view = Craft::$app->getView();
                $view->registerAssetBundle(Actions::class);
                $html = $view->renderTemplate('commerce-taxjar/_actions', [
                    'order' => $context['order'],
                    'orderId' => $context['orderId']
                ]);

                return $html;
            }
        });

        parent::init();
    }

    public function _registerHandlers()
    {
        // We want to be the tax engine for commerce
        Event::on(Taxes::class, Taxes::EVENT_REGISTER_TAX_ENGINE, static function(TaxEngineEvent $e) {
            $e->engine = new TaxJarEngine;
        });

        // Automatically create transaction in TaxJar after order is complete and paid
        Event::on(Order::class, Order::EVENT_AFTER_ORDER_PAID, function(Event $event) {
            $order = $event->sender;
            // But only if it's a brand new order
            if ($order->storedTotalPaid == 0) {
                Craft::$app->getQueue()->push(new CreateOrder([
                    'orderId' => $order->id
                ]));
            }
        });
    }

    /**
     * Registered the routes
     */
    private function _registerRoutes()
    {

    }

    /**
     * Returns the API service
     *
     * @return Api The API service
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
