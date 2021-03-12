<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\migrations;

use Craft;
use craft\db\Migration;
use craft\commerce\db\Table;

/**
 * m210226_225554_refund_records migration.
 */
class m210226_225554_refund_records extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%taxjar_refunds}}', [
            'id' => $this->primaryKey(),
            'transactionId' => $this->string()->notNull(),
            'orderId' => $this->integer()->notNull(),
            'amount' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'shipping' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'salesTax' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'snapshot' => $this->longText()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%taxjar_refund_lineitems}}', [
            'id' => $this->primaryKey(),
            'lineItemId' => $this->integer()->notNull(),
            'refundId' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
            'deduction' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%taxjar_refunds}}', 'orderId', false);
        $this->createIndex(null, '{{%taxjar_refund_lineitems}}', ['lineItemId', 'refundId'], true);

        $this->addForeignKey(null, '{{%taxjar_refunds}}', ['orderId'], Table::ORDERS, ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%taxjar_refund_lineitems}}', ['lineItemId'], Table::LINEITEMS, ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%taxjar_refund_lineitems}}', ['refundId'], '{{%taxjar_refunds}}', ['id'], 'CASCADE');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210226_225554_refund_records cannot be reverted.\n";
        return false;
    }
}
