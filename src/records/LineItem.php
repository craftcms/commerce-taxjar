<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\records;

use craft\commerce\db\Table;
use craft\db\ActiveRecord;

/**
 * Taxjar refund record.
 *
 * @property float $deduction
 * @property int $id
 * @property int $lineItemId
 * @property int $quantity
 * @property int $refundId
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class LineItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%taxjar_refund_lineitems}}';
    }
}
