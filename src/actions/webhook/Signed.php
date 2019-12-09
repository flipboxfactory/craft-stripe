<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\actions\webhook;

use Craft;
use flipbox\craft\stripe\connections\ConnectionInterface;
use Stripe\Event;
use Stripe\Webhook;
use yii\base\Exception;

class Signed extends AbstractWebhook
{
    /**
     * @param ConnectionInterface $connection
     * @return Event
     * @throws Exception
     */
    protected function constructEvent(ConnectionInterface $connection): Event
    {
        $secret = $connection->getWebhookSigningSecret();
        $stripeSignature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (!$secret || !$stripeSignature) {
            throw new Exception('Webhook not signed or signing secret not set.');
        }

        try {
            return Webhook::constructEvent(Craft::$app->getRequest()->getRawBody(), $stripeSignature, $secret);
        } catch (\Exception $exception) {
            throw new Exception(
                'Webhook signature check failed: ' . $exception->getMessage()
            );
        }
    }
}
