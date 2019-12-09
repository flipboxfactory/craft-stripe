<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\actions\webhook;

use Craft;
use craft\helpers\Json;
use flipbox\craft\stripe\connections\ConnectionInterface;
use Stripe\Event;
use yii\base\Exception;

class Unsigned extends AbstractWebhook
{
    protected function constructEvent(ConnectionInterface $connection): Event
    {
        if (!Craft::$app->getConfig()->general->devMode) {
            throw new Exception('Unable to process unsigned webhook.');
        }

        return Event::constructFrom(Json::decodeIfJson(Craft::$app->getRequest()->getRawBody()));
    }
}
