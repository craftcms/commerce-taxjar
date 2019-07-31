<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar;

use craft\base\Plugin as BasePlugin;
use craft\commerce\taxjar\services\Api;
use craft\commerce\taxjar\services\Categories;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;


/**
 * Class TaxJar
 *
 * @author    Pixel & Tonic
 * @package   TaxJar
 * @since     1.0.0
 *
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

        parent::init();
    }

    public function _registerRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['taxjar/categories'] = 'commerce-taxjar/categories/index';
        });
    }

    /**
     * Returns the categories service
     *
     * @return Categories The Categories service
     */
    public function getCategories(): Categories
    {
        return $this->get('categories');
    }

    /**
     * Returns the categories service
     *
     * @return Api The cart service
     */
    public function getApi(): Api
    {
        return $this->get('api');
    }

    // Private Methods
    // =========================================================================

    /**
     * Sets the components of the commerce plugin
     */
    private function _setPluginComponents()
    {
        $this->setComponents([
            'categories' => Categories::class,
            'api' => Api::class,
        ]);
    }
}
