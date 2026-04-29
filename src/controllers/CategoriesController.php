<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\controllers;

use Craft;
use craft\commerce\controllers\BaseCpController;
use craft\commerce\taxjar\Plugin;
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
            Plugin::getInstance()->getCategories()->sync();
        } catch (\Exception $exception) {
            Craft::error('TaxJar sync failed: ' . $exception->getMessage(), 'commerce-taxjar');
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }
}
