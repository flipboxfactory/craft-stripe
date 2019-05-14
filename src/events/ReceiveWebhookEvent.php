<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\events;

use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ReceiveWebhookEvent extends Event
{
    const EVENT_RECEIVE_WEBHOOK = 'receiveWebhook';

    /**
     * @var array The raw webhook data
     */
    public $data;

    /**
     * @var \Stripe\Event
     */
    public $event;
}
