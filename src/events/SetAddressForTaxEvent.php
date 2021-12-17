<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\events;

use yii\base\Event;

class SetAddressForTaxEvent extends Event
{
    public $order;
    public $address;
}