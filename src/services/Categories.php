<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use Craft;
use craft\commerce\models\TaxCategory;
use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\taxjar\Plugin;
use yii\base\Component;

/**
 * TaxJar Categories service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.1.0
 */
class Categories extends Component
{
    /**
     * Syncs tax categories from the TaxJar API into Craft Commerce.
     *
     * Returns an array with 'created' and 'skipped' counts.
     *
     * @return array{created: int, updated: int}
     * @throws \RuntimeException if a category cannot be saved
     */
    public function sync(): array
    {
        Craft::info('TaxJar category sync started.', 'commerce-taxjar');

        $allCategories = Plugin::getInstance()->getApi()->getCategories();

        Craft::info(count($allCategories) . ' categories fetched from TaxJar.', 'commerce-taxjar');

        $taxCategories = CommercePlugin::getInstance()->getTaxCategories();
        $created = 0;
        $skipped = 0;

        $created = 0;
        $updated = 0;

        foreach ($allCategories as $taxJarCategory) {
            $handle = $taxJarCategory->product_tax_code;
            $description = $taxJarCategory->description;

            if (strlen($description) >= 255) {
                $description = rtrim(substr($description, 0, 252)) . '...';
            }

            $category = $taxCategories->getTaxCategoryByHandle($handle) ?? new TaxCategory();
            $isNew = $category->id === null;

            $category->handle = $handle;
            $category->name = $taxJarCategory->name;
            $category->description = $description;

            if ($isNew) {
                $category->default = false;
            }

            if (!$taxCategories->saveTaxCategory($category)) {
                Craft::error("TaxJar sync: could not save tax category '{$handle}'.", 'commerce-taxjar');
                throw new \RuntimeException("Could not save tax category '{$handle}'.");
            }

            if ($isNew) {
                Craft::info("TaxJar sync: created category '{$handle}' ({$taxJarCategory->name}).", 'commerce-taxjar');
                $created++;
            } else {
                $updated++;
            }
        }

        Craft::info("TaxJar sync complete. Created: {$created}, updated: {$updated}.", 'commerce-taxjar');

        return ['created' => $created, 'updated' => $updated];
    }
}
