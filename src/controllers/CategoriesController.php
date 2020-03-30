<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\controllers;

use Craft;
use craft\commerce\controllers\BaseCpController;
use craft\commerce\models\Customer;
use craft\commerce\models\TaxCategory;
use craft\commerce\Plugin;
use craft\commerce\taxjar\TaxJar;
use yii\web\HttpException;
use yii\web\Response;

/**
 * TaxJar Categories Controller
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class CategoriesController extends BaseCpController
{
    /**
     * @param int|null $id
     * @param Customer|null $customer
     * @return Response
     * @throws HttpException
     */
    public function actionSync(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('commerce-manageTaxes');

        try {
            $allCategories = TaxJar::getInstance()->getApi()->getCategories();
        } catch (\Exception $exception) {
            return $this->asJson(['success' => false]);
        }


        foreach ($allCategories as $taxJarCategory) {
            $handle = $taxJarCategory->product_tax_code;
            $existing = Plugin::getInstance()->getTaxCategories()->getTaxCategoryByHandle($handle);

            if (!$existing) {
                $newCategory = new TaxCategory();
                $newCategory->name = $taxJarCategory->name;
                $newCategory->description = $taxJarCategory->description;
                $newCategory->handle = $handle;
                $newCategory->default = false;
                if (!Plugin::getInstance()->getTaxCategories()->saveTaxCategory($newCategory)) {
                    Craft::error('Could not save tax category from taxjar.');
                    return $this->asJson(['success' => false]);
                }
            }
        }

        return $this->asJson(['success' => true]);
    }
}
