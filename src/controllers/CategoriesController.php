<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\controllers;

use Craft;
use craft\commerce\controllers\BaseCpController;
use craft\commerce\models\TaxCategory;
use craft\commerce\Plugin;
use craft\commerce\taxjar\services\Api;
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
            $category = Plugin::getInstance()->getTaxCategories()->getTaxCategoryByHandle($handle);

            if (!$category) {
                $category = new TaxCategory();

                $category->default = false;
                $category->handle = Api::HANDLE_PREFIX . $handle;
                $category->name = $taxJarCategory->name;
                $category->description = $taxJarCategory->description;

                if (strlen($category->description) >= 255) {
                    $category->description = rtrim(substr($category->description, 0, 252)) . '...';
                }

                if (!Plugin::getInstance()->getTaxCategories()->saveTaxCategory($category)) {
                    Craft::error('Could not save tax category from taxjar.');
                    return $this->asJson(['success' => false]);
                }
            }
        }

        return $this->asJson(['success' => true]);
    }
}
