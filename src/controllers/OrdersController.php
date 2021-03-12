<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\controllers;

use Craft;
use craft\commerce\Plugin;
use craft\commerce\controllers\BaseCpController;
use craft\commerce\elements\Order;
use craft\commerce\errors\RefundException;
use craft\commerce\helpers\Currency;
use craft\commerce\records\Transaction as TransactionRecord;
use craft\commerce\taxjar\TaxJar;
use craft\commerce\taxjar\records\Refund;
use craft\commerce\taxjar\records\LineItem;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use yii\web\Response;

/**
 * TaxJar Orders Controller
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class OrdersController extends BaseCpController
{
    /**
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->requirePermission('commerce-manageOrders');
    }

    /**
     * @return Response
     */
    public function actionSend(): Response
    {
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getParam('id');
        $client = TaxJar::getInstance()->getApi()->getClient();

        if ($id === null) {
            return $this->asErrorJson(Craft::t('commerce', 'Bad Request'));
        }

        $order = Order::find()->id($id)->one();

        if ($order === null) {
            return $this->asErrorJson(Craft::t('commerce', 'Can not find order'));
        }

        $existingTransaction = true;
        try {
            $client->showOrder($order->id);
        } catch (\Exception $exception) {
            $existingTransaction = false;
        }

        try {
            if (!$existingTransaction) {
                $client->createOrder($this->_getOrderData($order));
            } else {
                $client->updateOrder($this->_getOrderData($order));
            }
        } catch (\Exception $exception) {
            Craft::error($exception->getMessage(), __METHOD__);
            return $this->asErrorJson(Craft::t('commerce-taxjar', 'Could not send order transaction'));
        }

        $refunds = Refund::find()
            ->where(['orderId' => $order->id])
            ->all();

        foreach ($refunds as $refund) {
            $existingRefund = true;
            try {
                $client->showRefund($refund->transactionId);
            } catch (\Exception $exception) {
                $existingRefund = false;
            }

            try {
                if (!$existingRefund) {
                    $client->createRefund(json_decode($refund->snapshot));
                } else {
                    $client->updateRefund(json_decode($refund->snapshot));
                }
            } catch (\Exception $exception) {
                Craft::error($exception->getMessage(), __METHOD__);
                return $this->asErrorJson(Craft::t('commerce-taxjar', 'Could not send refund transaction'));
            }
        }

        $action = $existingTransaction ? 'updated' : 'created';
        return $this->asJson(['success' => true, 'action' => $action]);
    }

    /**
     * @return Response
     */
    public function actionDelete(): Response
    {
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getParam('id');
        $client = TaxJar::getInstance()->getApi()->getClient();

        if ($id === null) {
            return $this->asErrorJson(Craft::t('commerce', 'Bad Request'));
        }

        $order = Order::find()->id($id)->one();

        if ($order === null) {
            return $this->asErrorJson(Craft::t('commerce', 'Can not find order'));
        }

        $refunds = Refund::find()
            ->where(['orderId' => $order->id])
            ->all();

        foreach ($refunds as $refund) {
            try {
                $client->deleteRefund($refund->transactionId);
            } catch (\Exception $exception){
                Craft::error($exception->getMessage(), __METHOD__);
                return $this->asErrorJson(Craft::t('commerce-taxjar', 'Could not delete refund transaction'));
            }
        }

        try {
            $client->deleteOrder($order->id);
        } catch (\Exception $exception) {
            Craft::error($exception->getMessage(), __METHOD__);
            return $this->asErrorJson(Craft::t('commerce-taxjar', 'Could not delete order transaction'));
        }

        return $this->asJson(['success' => true, 'action' => 'deleted']);
    }

    /**
     * Returns Payment Modal
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionGetRefundModal(): Response
    {
        $this->requireAcceptsJson();
        $view = $this->getView();

        $request = Craft::$app->getRequest();
        $orderId = $request->getParam('orderId');

        $plugin = Plugin::getInstance();
        $order = $plugin->getOrders()->getOrderById($orderId);

        $refundIds = (new Query())
            ->select(['id'])
            ->from('{{%taxjar_refunds}}')
            ->where(['orderId' => $order->id])
            ->all();
        $remaining = [];

        if ($refundIds) {
            $refundedLineItems = LineItem::find()
                ->where(['IN', 'refundId', array_column($refundIds, 'id')])
                ->all();

            if ($refundedLineItems) {
                foreach ($order->lineItems as $lineItem) {
                    $refundedQty = 0;
                    foreach ($refundedLineItems as $item) {
                        if ($item->lineItemId == $lineItem->id)
                            $refundedQty += $item->quantity;
                    }

                    $remaining[$lineItem->id] = $lineItem->qty - $refundedQty;
                }
            }
        }

        $modalHtml = $view->renderTemplate('commerce-taxjar/_refundmodal', [
            'order' => $order,
            'remaining' => $remaining
        ]);

        return $this->asJson([
            'success' => true,
            'modalHtml' => $modalHtml,
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml(),
        ]);
    }

    public function actionRefund()
    {
        $this->requirePermission('commerce-refundPayment');
        $this->requirePostRequest();

        $plugin = Plugin::getInstance();
        $orderId = $this->request->getBodyParam('orderId');
        $refundItems = array_filter($this->request->getBodyParam('refunds'), function($var) {
            return !empty($var['qty']);
        });
        $shipping = (float)$this->request->getBodyParam('shipping');
        $tax = (float)$this->request->getBodyParam('tax');
        $refundNote = $this->request->getBodyParam('refundNote');

        if (empty($refundItems) && empty($shipping) && empty($tax)) {
            $this->setFailFlash(Craft::t('commerce-taxjar', 'No amount to refund.'));

            return null;
        }

        if ($orderId === null || !$order = $plugin->getOrders()->getOrderById($orderId)) {
            $this->setFailFlash(Craft::t('commerce-taxjar', 'Can not find an order to refund.'));

            return null;
        }

        $totalItems = 0;
        $totalTax = $tax;
        $lineItemsParams = [];

        if (!empty($refundItems)) {
            $taxCategories = Plugin::getInstance()->getTaxCategories();
            $refundItemIds = array_keys($refundItems);
            $refundIds = (new Query())
                ->select(['id'])
                ->from('{{%taxjar_refunds}}')
                ->where(['orderId' => $order->id])
                ->all();
            $refundedLineItems = null;
            if ($refundIds) {
                $refundedLineItems = LineItem::find()
                    ->where(['IN', 'refundId', array_column($refundIds, 'id')])
                    ->andWhere(['IN', 'lineItemId', $refundItemIds])
                    ->all();
            }


            foreach ($order->lineItems as $lineItem) {
                if (in_array($lineItem->id, $refundItemIds)) {
                    if ($refundedLineItems) {
                        $refundedQty = 0;
                        foreach ($refundedLineItems as $item) {
                            $refundedQty += $item->quantity;
                        }

                        if ($refundedQty + $refundItems[$lineItem->id]['qty'] > $lineItem->qty) {
                            $this->setFailFlash(Craft::t('commerce-taxjar', 'Can not refund quantity greater than purchased.'));

                            return null;
                        }
                    }

                    $qtyRatio = $refundItems[$lineItem->id]['qty'] / $lineItem->qty;
                    $qtySubtotal = $lineItem->subtotal * $qtyRatio;
                    $qtyDiscount = Currency::round($lineItem->discount * $qtyRatio);

                    $refundSubtotal = $qtySubtotal + $qtyDiscount;
                    $refundTax = $lineItem->tax * $qtyRatio;

                    if ($deduction = (float)$refundItems[$lineItem->id]['deduction']) {
                        $original = $refundSubtotal;
                        $refundSubtotal += $deduction;
                        $refundTax = $refundTax * ($refundSubtotal / $original);
                    }

                    $totalItems += $refundSubtotal;
                    $totalTax += Currency::round($refundTax);

                    // Build line item params while we're here
                    $category = $taxCategories->getTaxCategoryById($lineItem->taxCategoryId);
                    $lineItemsParams[] = [
                        'id' => $lineItem->id,
                        'quantity' => $refundItems[$lineItem->id]['qty'],
                        'unit_price' => ($qtySubtotal + $deduction) * -1,
                        'discount' => $qtyDiscount,
                        'sales_tax' => $refundTax * -1,
                        'product_tax_code' => $category && $category->handle !== 'general' ? $category->handle : null,
                        'product_identifier' => $lineItem->sku,
                        'descripton' => $lineItem->description
                    ];
                }
            }
        }

        $totalRefund = $totalItems + $totalTax + $shipping;
        // Check that total refund doesn't exceed total paid
        if ($totalRefund > $order->totalPaid) {
            $this->setFailFlash(Craft::t('commerce-taxjar', 'Refund can not exceed total paid.'));

            return null;
        }

        $payments = array_filter($order->transactions, function($transaction) {
            return $transaction->canRefund();
        });

        if (empty($payments)) {
            $this->setFailFlash(Craft::t('commerce-taxjar', 'No transactions available to be refunded.'));

            return null;
        }

        $refundTransactions = [];
        $toRefund = 0;
        foreach ($payments as $payment) {
            $max = $payment->getRefundableAmount();
            $amount = min($max, $totalRefund);
            $refundTransactions[] = ['payment' => $payment, 'amount' => $amount];
            $toRefund += $amount;

            if ($toRefund === $totalRefund)
                break;
        }

        $success = [];
        $fail = [];
        foreach ($refundTransactions as $transaction) {
            try {
                $child = Plugin::getInstance()->getPayments()->refundTransaction(
                    $transaction['payment'],
                    $transaction['amount'],
                    $refundNote
                );

                $message = $child->message ? ' (' . $child->message . ')' : '';

                if ($child->status == TransactionRecord::STATUS_SUCCESS) {
                    $child->order->updateOrderPaidInformation();
                    $success[] = $child->id;
                } else {
                    Craft::error(Craft::t('commerce', 'Couldn’t refund transaction: {message}', [
                        'message' => $message
                    ]), __METHOD__);

                    $fail[] = [
                        'transactionId' => $child->id,
                        'amount' => $transaction['amount']
                    ];
                }
            } catch (RefundException $exception) {
                Craft::error($exception->getMessage(), __METHOD__);

                $fail[] = [
                    'transactionId' => $child->id,
                    'amount' => $transaction['amount']
                ];
            }
        }

        if (empty($success)) {
            $this->setFailFlash(Craft::t('commerce-taxjar', 'Unable to refund, please check error logs.'));

            return null;
        }

        $api = TaxJar::getInstance()->getApi();
        $from = $api->getFromParams();
        $to = $api->getToParams($order->getShippingAddress());
        $refundParams = [
            'transaction_id' => implode('_', $success),
            'transaction_date' => date('c'),
            'transaction_reference_id' => $order->id,
            'amount' => ($totalItems + $shipping) * -1,
            'shipping' => $shipping * -1,
            'sales_tax' => $totalTax * -1,
            'line_items' => $lineItemsParams
        ];
        $refundData = array_merge($from, $to, $refundParams);

        try {
            $api->getClient()->createRefund($refundData);
        } catch (\Exception $exception) {
            Craft::error($exception->getMessage(), __METHOD__);
            $this->setFailFlash(Craft::t('commerce-taxjar', 'Unable to send refund.'));

            return null;
        }

        if (!empty($fail)) {
            $this->setFailFlash(Craft::t('commerce-taxjar', 'Unable to fully refund, please check error logs.'));
        } else {
            $this->setSuccessFlash(Craft::t('commerce-taxjar', 'Refund processed and sent.'));
        }

        $refund = new Refund();
        $refund->transactionId = $refundParams['transaction_id'];
        $refund->orderId = $refundParams['transaction_reference_id'];
        $refund->amount = $refundParams['amount'];
        $refund->shipping = $refundParams['shipping'];
        $refund->salesTax = $refundParams['sales_tax'];
        $refund->snapshot = json_encode($refundData);
        $refund->save();
        $refundId = $refund->getPrimaryKey();

        $rows = [];
        foreach ($refundItems as $id => $item) {
            $rows[] = [$id, $refundId, $item['qty'], (!empty($item['deduction']) ? $item['deduction'] : 0.0000)];
        }
        Craft::$app->getDb()->createCommand()->batchInsert(
            '{{%taxjar_refund_lineitems}}',
            ['lineItemId', 'refundId', 'quantity', 'deduction'],
            $rows
        )->execute();

        $this->redirectToPostedUrl();
    }

    /**
     * @param Order $order
     * @return array
     */
    private function _getOrderData(Order $order): array
    {
        $api = TaxJar::getInstance()->getApi();
        $from = $api->getFromParams();
        $to = $api->getToParams($order->getShippingAddress());
        $amounts = $api->getAmountsParams($order);
        $orderParams = [
            'transaction_id' => $order->id,
            'transaction_date' => DateTimeHelper::toIso8601($order->dateOrdered),
            'sales_tax' => $order->totalTax
        ];
        $orderData = array_merge($from, $to, $amounts, $orderParams);

        return $orderData;
    }
}
