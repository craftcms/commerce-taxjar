<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\web\assets\actions;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use yii\web\JqueryAsset;

/**
 * Actions Asset
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Actions extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
            JqueryAsset::class,
        ];

        $this->css[] = 'css/actions.css';
        $this->js[] = 'js/actions.js';

        parent::init();
    }
}
