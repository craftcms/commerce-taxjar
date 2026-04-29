<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar;

use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\commerce\events\TaxEngineEvent;
use craft\commerce\services\Taxes;
use craft\commerce\taxjar\engines\TaxJar as TaxJarEngine;
use craft\commerce\taxjar\models\Settings;
use craft\commerce\taxjar\services\Api;
use craft\commerce\taxjar\services\Categories;
use yii\base\Event;

/**
 * Class TaxJar
 * @method Settings getSettings()
 *
 * @author    Pixel & Tonic
 * @package   TaxJar
 * @since     1.0
 *
 * @property-read \craft\commerce\taxjar\models\Settings $settings
 * @property Api $api
 */
class Plugin extends BasePlugin
{
    /**
     * @var string
     */
    public string $schemaVersion = "1.0.0";

    /** @var bool Whether the plugin has a settings page in the control panel */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'api' => ['class' => Api::class],
                'categories' => ['class' => Categories::class],
            ],
        ];
    }

    /**
     * Initializes the plugin
     */
    public function init()
    {
        $this->_registerHandlers();

        parent::init();
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return \Craft::createObject(Settings::class);
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content block on the settings page.
     *
     * @return string|null The rendered settings HTML
     */
    protected function settingsHtml(): ?string
    {
        $settings = $this->getSettings();
        return \Craft::$app->view->renderTemplate('commerce-taxjar/_settings.twig', [
            'plugin' => $this,
            'settings' => $settings,
        ]);
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
     * @throws \yii\base\InvalidConfigException
     */
    public function getApi(): Api
    {
        return $this->get('api');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getCategories(): Categories
    {
        return $this->get('categories');
    }
}
