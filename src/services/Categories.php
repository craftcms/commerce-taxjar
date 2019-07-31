<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\services;

use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\taxjar\models\Category;
use craft\commerce\taxjar\TaxJar;
use craft\commerce\taxjar\records\Category as CategoryRecord;
use craft\db\Query;
use yii\base\Component;
use yii\base\Exception;

/**
 * TaxJar tax category service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 *
 * @property Category[] $allCategories
 */
class Categories extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns all Tax Categories
     *
     * @return Category[]
     */
    public function getAllCategories(): array
    {
        $categories = [];
        $categoryResults = $this->_createTaxCategoryQuery()->all();
        foreach ($categoryResults as $categoryResult) {
            $categories[] = new Category($categoryResult);
        }

        return $categories;
    }

    /**
     * Get a tax category by its commerce tax category ID.
     *
     * @param int $taxCategoryId
     * @return Category|null
     */
    public function getCategoryByTaxCategoryId($taxCategoryId)
    {
        $categories = $this->getAllCategories();

        foreach ($categories as $category) {
            if ($category->taxCategoryId == $taxCategoryId) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Get a tax category by its TaxJar tax category ID.
     *
     * @param int $taxJarCategoryId
     * @return Category|null
     */
    public function getCategoryByTaxJarCategoryId($taxJarCategoryId)
    {
        $categories = $this->getAllCategories();

        foreach ($categories as $category) {
            if ($category->taxJarCategoryId == $taxJarCategoryId) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Save a tax category.
     *
     * @param Category[] $categories
     * @param bool $runValidation should we validate this state before saving.
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function saveCategories(array $categories, bool $runValidation = true): bool
    {
        $invalid = [];

        foreach ($categories as $category) {

            if (!$category->validate()) {
                $invalid[] = $category;
            }

            if (count($invalid)) {
                return false;
            }

            // Get the record or create it
            $categoryRecord = CategoryRecord::find()->where(['id' => $category->id])->one();
            if (!$categoryRecord) {
                $categoryRecord = new CategoryRecord();
            }

            $categoryRecord->taxJarCategoryId = $category->taxJarCategoryId;
            $categoryRecord->taxJarCategoryName = $category->taxJarCategoryName;
            $categoryRecord->taxJarCategoryDescription = $category->taxJarCategoryDescription;
            $categoryRecord->taxCategoryId = $category->taxCategoryId;

            $categoryRecord->save(false);
            $category->id = $categoryRecord->id;
        }

        return true;
    }

    /**
     * Refreshes the categories from the latest categories in the API.
     * Updates existing ones on record and adds any new ones with the default tax category mapping.
     *
     * @throws Exception
     */
    public function refreshTaxJarCategories()
    {
        $liveCategoriesFromApi = TaxJar::getInstance()->getApi()->getCategories();
        $categories = [];
        foreach ($liveCategoriesFromApi as $taxJarCategory) {
            $category = $this->getCategoryByTaxJarCategoryId($taxJarCategory->product_tax_code);

            if ($category == null) {
                $category = new Category();
                $category->taxCategoryId = CommercePlugin::getInstance()->getTaxCategories()->getDefaultTaxCategory()->id;
            }

            // Update the info from TaxJar
            $category->taxJarCategoryId = $taxJarCategory->product_tax_code;
            $category->taxJarCategoryName = $taxJarCategory->name;
            $category->taxJarCategoryDescription = $taxJarCategory->description;

            $categories[] = $category;
        }

        $this->saveCategories($categories);
    }

    // Private methods
    // =========================================================================

    /**
     * Returns a Query object prepped for retrieving tax categories.
     *
     * @return Query
     */
    private function _createTaxCategoryQuery(): Query
    {
        return (new Query())
            ->select([
                'categories.id',
                'categories.taxJarCategoryId',
                'categories.taxJarCategoryName',
                'categories.taxJarCategoryDescription',
                'categories.taxCategoryId'
            ])
            ->from(['{{%commerce_taxjar_categories}} categories']);
    }
}
