<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\models;

use craft\commerce\base\Model;
use craft\commerce\models\TaxCategory;
use craft\commerce\Plugin;
use craft\helpers\UrlHelper;

/**
 * Country Model
 *
 * @property string $cpEditUrl
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Category extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var int TaxJar tax category ID (Also known as the `product_tax_code`
     */
    public $taxJarCategoryId;

    /**
     * @var string TaxJar tax category name
     */
    public $taxJarCategoryName;

    /**
     * @var string TaxJar tax category description
     */
    public $taxJarCategoryDescription;

    /**
     * @var int Commerce tax category ID
     */
    public $taxCategoryId;

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->taxJarCategoryName;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['taxCategoryId', 'taxJarCategoryId', 'taxJarCategoryName', 'taxJarCategoryDescription'], 'required'],
        ];
    }

    /**
     * @return string
     */
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('taxjar/categories/');
    }

    /**
     * @return TaxCategory|null
     */
    public function getTaxCategory()
    {
        if ($this->taxCategoryId) {
            if ($category = Plugin::getInstance()->getTaxCategories()->getTaxCategoryById($this->taxCategoryId)) {
                return $category;
            }
        }

        return Plugin::getInstance()->getTaxCategories()->getDefaultTaxCategory();
    }
}
