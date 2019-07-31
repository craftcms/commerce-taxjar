<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\controllers;

use Craft;
use craft\commerce\taxjar\models\Category;
use craft\commerce\taxjar\TaxJar as Plugin;
use craft\commerce\Plugin as CommercePlugin;
use craft\web\Controller;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Class Categories Controller
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class CategoriesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     * @throws HttpException
     */
    public function actionIndex(): Response
    {
        Plugin::getInstance()->getCategories()->refreshTaxJarCategories();

        $variables['title'] = Craft::t('taxjar', 'Edit Tax Category Mapping');

        $variables['categories'] = Plugin::getInstance()->getCategories()->getAllCategories();
        $variables['taxCategories'] = CommercePlugin::getInstance()->getTaxCategories()->getAllTaxCategoriesAsList();

        // Get the HTML and JS for the new tax zone/category modals
        $view = $this->getView();
        $view->setNamespace('new');

        $view->startJsBuffer();
        $variables['newTaxCategoryFields'] = $view->namespaceInputs(
            $view->renderTemplate('commerce/tax/taxcategories/_fields', [
                'productTypes' => CommercePlugin::getInstance()->getProductTypes()->getAllProductTypes()
            ])
        );
        $variables['newTaxCategoryJs'] = $view->clearJsBuffer(false);
        $view->setNamespace();

        return $this->renderTemplate('taxjar-commerce/categories/_edit', $variables);
    }

    /**
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $categories = Craft::$app->getRequest()->getBodyParam('categories');
        $updatedCategories = [];

        foreach ($categories as $category) {
            $new = new Category();
            $new->id = $category['id'];
            $new->taxJarCategoryDescription = $category['taxJarCategoryDescription'];
            $new->taxJarCategoryId = $category['taxJarCategoryId'];
            $new->taxJarCategoryName = $category['taxJarCategoryName'];
            $new->taxCategoryId = $category['taxCategoryId'];

            $updatedCategories[] = $new;
        }

        // Save it
        if (!Plugin::getInstance()->getCategories()->saveCategories($updatedCategories)) {

            Craft::$app->getSession()->setError(Craft::t('taxjar', 'Couldn’t save categories.'));
            Craft::$app->getUrlManager()->setRouteParams(['categories' => $new]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('taxjar', 'Category mapping saved.'));

        $this->redirectToPostedUrl();
    }
}
