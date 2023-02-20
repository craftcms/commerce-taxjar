<?php

namespace craft\commerce\taxjar\migrations;

use Craft;
use craft\commerce\taxjar\services\Api;
use craft\db\Migration;
use craft\db\Query;

/**
 * m230217_100801_add_taxjar_categories_prefix migration.
 */
class m230217_100801_add_taxjar_categories_prefix extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $taxCategories = (new Query())
            ->select(['id', 'handle'])
            ->from(['taxcategories' => \craft\commerce\db\Table::TAXCATEGORIES])
            ->all();

        foreach ($taxCategories as $taxCategory) {
            $handle = $taxCategory['handle'];
            $id = $taxCategory['id'];
            $firstChar = substr($handle, 0, 1);

            if (is_numeric($firstChar)) {
                $newHandle = Api::HANDLE_PREFIX . $handle;
                $this->update(\craft\commerce\db\Table::TAXCATEGORIES, ['handle' => $newHandle], ['id' => $id], [], false);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m230217_100801_add_taxjar_categories_prefix cannot be reverted.\n";
        return false;
    }
}
