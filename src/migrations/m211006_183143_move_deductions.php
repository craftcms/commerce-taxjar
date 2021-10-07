<?php

namespace craft\commerce\taxjar\migrations;

use Craft;
use craft\commerce\taxjar\records\Refund as RefundRecord;
use craft\db\Migration;
use craft\db\Query;

/**
 * m211006_183143_move_deductions migration.
 */
class m211006_183143_move_deductions extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%taxjar_refunds}}', 'deduction')) {
            $this->addColumn('{{%taxjar_refunds}}', 'deduction', $this->decimal(14, 4)->notNull()->defaultValue(0) . " AFTER salesTax");

            $rows = (new Query())
                ->select(['refundId', 'deduction'])
                ->from('{{%taxjar_refund_lineitems}}')
                ->all();
            
            $refunds = RefundRecord::findAll(array_unique(array_column($rows, 'refundId')));

            foreach ($refunds as $refund) {
                foreach ($rows as $row) {
                    if ($row['refundId'] === $refund->id) {
                        $refund->deduction += $row['deduction'];
                    }
                }
                
                $refund->deduction = abs($refund->deduction);
                
                $refund->save(false);
            }
        }

        if ($this->db->columnExists('{{%taxjar_refund_lineitems}}', 'deduction')) {
            $this->dropColumn('{{%taxjar_refund_lineitems}}', 'deduction');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m211006_183143_move_deductions cannot be reverted.\n";
        return false;
    }
}
