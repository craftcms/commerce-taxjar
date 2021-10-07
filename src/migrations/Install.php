<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\migrations;

use Craft;
use craft\commerce\db\Table;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * Installation Migration
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKeys();
        $this->dropTables();
        $this->dropProjectConfig();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables for TaxJar
     */
    public function createTables()
    {
        $this->createTable('{{%taxjar_refunds}}', [
            'id' => $this->primaryKey(),
            'transactionId' => $this->string()->notNull(),
            'orderId' => $this->integer()->notNull(),
            'amount' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'shipping' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'salesTax' => $this->decimal(14, 4)->notNull()->defaultValue(0),
            'deduction' => $this->decimal(14, 4)->notNull()->defaultValue(0),
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
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * Drop the tables
     */
    public function dropTables()
    {
        $this->dropTableIfExists('{{%taxjar_refunds}}');
        $this->dropTableIfExists('{{%taxjar_refund_lineitems}}');
    }

    /**
     * Deletes the project config entry.
     */
    public function dropProjectConfig()
    {
        Craft::$app->projectConfig->remove('taxjar');
    }

    /**
     * Creates the indexes.
     */
    public function createIndexes()
    {
        $this->createIndex(null, '{{%taxjar_refunds}}', 'orderId', false);
        $this->createIndex(null, '{{%taxjar_refund_lineitems}}', ['lineItemId', 'refundId'], true);
    }

    /**
     * Adds the foreign keys.
     */
    public function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%taxjar_refunds}}', ['orderId'], Table::ORDERS, ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%taxjar_refund_lineitems}}', ['lineItemId'], Table::LINEITEMS, ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%taxjar_refund_lineitems}}', ['refundId'], '{{%taxjar_refunds}}', ['id'], 'CASCADE');
    }

    /**
     * Removes the foreign keys.
     */
    public function dropForeignKeys()
    {
        $tables = ['{{%taxjar_refunds}}', '{{%taxjar_refund_lineitems}}'];

        foreach ($tables as $table) {
            if ($this->_tableExists($table)) {
                MigrationHelper::dropAllForeignKeysToTable($table, $this);
                MigrationHelper::dropAllForeignKeysOnTable($table, $this);
            }
        }
    }

    /**
     * Insert the default data.
     */
    public function insertDefaultData()
    {
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns if the table exists.
     *
     * @param string $tableName
     * @param Migration|null $migration
     * @return bool If the table exists.
     * @throws \yii\base\NotSupportedException
     */
    private function _tableExists(string $tableName): bool
    {
        $schema = $this->db->getSchema();
        $schema->refresh();

        $rawTableName = $schema->getRawTableName($tableName);
        $table = $schema->getTableSchema($rawTableName);

        return (bool)$table;
    }
}
