<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\engines;

use Craft;
use craft\commerce\base\TaxEngineInterface;
use craft\commerce\taxjar\adjusters\TaxJar as TaxJarAdjuster;
use craft\commerce\taxjar\web\assets\taxjar\TaxJar as TaxJarAsset;

/**
 * Class TaxJar
 *
 * @package craft\commerce\taxjar\engines
 * @since 1.0
 */
class TaxJar implements TaxEngineInterface
{
    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return 'TaxJar Engine';
    }

    /**
     * @inheritDoc
     */
    public function taxAdjusterClass(): string
    {
        return TaxJarAdjuster::class;
    }

    /**
     * @inheritDoc
     */
    public function viewTaxCategories(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteTaxCategories(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function createTaxCategories(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function editTaxCategories(): bool
    {
        return true;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function taxCategoryActionHtml(): string
    {
        Craft::$app->getView()->registerTranslations('commerce', [
            'Categories Updated. Reloading page.',
            'Categories update failed. Make sure you are not in sandbox mode.'
        ]);

        Craft::$app->getView()->registerAssetBundle(TaxJarAsset::class);

        return '<a href="#" class="taxjar-sync-categories-btn btn reload icon">Sync TaxJar Categories</a>';
    }

    /**
     * @inheritDoc
     */
    public function viewTaxZones(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function editTaxZones(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function createTaxZones(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function deleteTaxZones(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function taxZoneActionHtml(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function viewTaxRates(): bool
    {
        return false;
    }

    public function editTaxRates(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function createTaxRates(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function deleteTaxRates(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function taxRateActionHtml(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function cpTaxNavSubItems(): array
    {
        return [];
    }
}