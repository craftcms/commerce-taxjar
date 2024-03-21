<?php

namespace craft\commerce\taxjar\events;

use craft\commerce\elements\Order;
use craft\elements\Address;
use yii\base\Event;

class ModifyRequestEvent extends Event
{
    public array $requestParams;

    public ?Order $order;

    public ?Address $address;
}
