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
 * @property float $amount
 * @property int $id
 * @property int $orderId
 * @property float $salesTax
 * @property float $shipping
 * @property string $snapshot
 * @property string $transactionId
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Refund extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%taxjar_refunds}}';
    }
}
