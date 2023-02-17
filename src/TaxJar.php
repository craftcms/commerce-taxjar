<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar;

use craft\base\Plugin as BasePlugin;
use craft\commerce\events\TaxEngineEvent;
use craft\commerce\services\Taxes;
use craft\commerce\taxjar\engines\TaxJar as TaxJarEngine;
use craft\commerce\taxjar\models\Settings;
use craft\commerce\taxjar\services\Api;
use yii\base\Event;

/**
 * Class TaxJar
 *
 * @author    Pixel & Tonic
 * @package   TaxJar
 * @since     1.0
 *
 * @property Api $api
 */
class TaxJar extends BasePlugin
{
    /**
     * @var string
     */
    public string $schemaVersion = "1.2.0";

    /**
     * Initializes the plugin
     */
    public function init()
    {
        $this->_setPluginComponents();
        $this->_registerHandlers();

        parent::init();
    }

    /**
     * Registers the event handlers
     *
     * @return void
     */
    public function _registerHandlers(): void
    {
        // We want to be the tax engine for Commerce
        Event::on(
            Taxes::class,
            Taxes::EVENT_REGISTER_TAX_ENGINE,
            static function(TaxEngineEvent $e) {
                $e->engine = new TaxJarEngine();
            }
        );
    }

    /**
     * Returns the Api service
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
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * Sets the components of the commerce plugin
     */
    private function _setPluginComponents(): void
    {
        $this->setComponents([
            'api' => Api::class,
        ]);
    }
}
