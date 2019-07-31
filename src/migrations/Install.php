<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\migrations;

use Craft;
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
        $this->createTable('{{%commerce_taxjar_categories}}', [
            'id' => $this->primaryKey(),
            'taxJarCategoryId' => $this->integer(),
            'taxJarCategoryName' => $this->text(),
            'taxJarCategoryDescription' => $this->text(),
            'taxCategoryId' => $this->integer(),
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
        $this->dropTableIfExists('{{%commerce_taxjar_categories}}');

        return null;
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
    }

    /**
     * Adds the foreign keys.
     */
    public function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%commerce_taxjar_categories}}', ['taxCategoryId'], '{{%commerce_taxcategories}}', ['id'], 'SET NULL');
    }

    /**
     * Removes the foreign keys.
     */
    public function dropForeignKeys()
    {
        if ($this->_tableExists('{{%commerce_taxjar_categories}}')) {
            MigrationHelper::dropAllForeignKeysToTable('{{%commerce_taxjar_categories}}', $this);
            MigrationHelper::dropAllForeignKeysOnTable('{{%commerce_taxjar_categories}}', $this);
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
