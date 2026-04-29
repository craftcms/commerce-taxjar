<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\console\controllers;

use craft\commerce\taxjar\Plugin;
use craft\console\Controller;
use craft\helpers\Console;
use yii\console\ExitCode;

/**
 * Manages TaxJar tax categories.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.1.0
 */
class CategoriesController extends Controller
{
    /**
     * Lists all tax categories available from the TaxJar API.
     */
    public function actionIndex(): int
    {
        try {
            $categories = Plugin::getInstance()->getApi()->getCategories();
        } catch (\Exception $e) {
            $this->stderr('Failed to fetch categories from TaxJar: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (empty($categories)) {
            $this->stdout('No categories found.' . PHP_EOL, Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout(PHP_EOL);
        Console::outputWarning(count($categories) . ' categories found:');
        $this->stdout(PHP_EOL);

        foreach ($categories as $category) {
            $this->stdout($category->product_tax_code, Console::FG_CYAN);
            $this->stdout('  ' . $category->name, Console::BOLD);
            $this->stdout(PHP_EOL);
            if (!empty($category->description)) {
                $this->stdout('    ' . $category->description . PHP_EOL, Console::FG_GREY);
            }
        }

        $this->stdout(PHP_EOL);

        return ExitCode::OK;
    }

    /**
     * Syncs tax categories from the TaxJar API into Craft Commerce.
     */
    public function actionSync(): int
    {
        $this->stdout('Syncing categories from TaxJar ... ');

        try {
            $result = Plugin::getInstance()->getCategories()->sync();
        } catch (\Exception $e) {
            $this->stdout('failed' . PHP_EOL, Console::FG_RED);
            $this->stderr($e->getMessage() . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout('done' . PHP_EOL, Console::FG_GREEN);
        $this->stdout("Created: {$result['created']}, updated: {$result['updated']}." . PHP_EOL);

        return ExitCode::OK;
    }
}
