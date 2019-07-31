<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\records;

use craft\commerce\records\TaxCategory;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Order status email record.
 *
 * @property int $taxJarCategoryId
 * @property int $taxCategoryId
 * @property int $taxJarCategoryName
 * @property int $taxJarCategoryDescription
 * @property TaxCategory $taxCategory
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Category extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%commerce_taxjar_categories}}';
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getTaxCategory(): ActiveQueryInterface
    {
        return $this->hasOne(TaxCategory::class, ['id' => 'taxCategoryId']);
    }
}
