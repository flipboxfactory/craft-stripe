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
use flipbox\craft\stripe\events\ReceiveWebhookEvent;
use flipbox\craft\stripe\Stripe;
use Stripe\Event;
use yii\base\Action;
use yii\web\Response;

abstract class AbstractWebhook extends Action
{
    /**
     * @param ConnectionInterface $connection
     * @return Event
     */
    abstract protected function constructEvent(ConnectionInterface $connection): Event;

    /**
     * @inheritdoc
     */
    protected function processWebHook(ConnectionInterface $connection): Response
    {
        $response = Craft::$app->getResponse();

        try {
            // Check the payload and signature
            $event = $this->constructEvent($connection);
        } catch (\Exception $exception) {
            $response->setStatusCode(400);
            $response->data = $exception->getMessage();

            Stripe::warning($exception->getMessage(), 'webhook');

            return $response;
        }

        Stripe::getInstance()->trigger(
            ReceiveWebhookEvent::EVENT_RECEIVE_WEBHOOK,
            new ReceiveWebhookEvent([
                'data' => Json::decodeIfJson(Craft::$app->getRequest()->getRawBody()),
                'event' => $event
            ])
        );

        $response->data = 'ok';

        return $response;
    }

    /**
     * @return Response|\yii\console\Response
     */
    public function run()
    {
        try {
            $response = $this->processWebHook(
                $this->resolveConnection()
            );
        } catch (\Throwable $e) {
            Stripe::error(
                sprintf(
                    "Exception caught while trying to process webhook. Exception: [%s].",
                    Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                __METHOD__
            );

            $response = Craft::$app->getResponse();
            $response->setStatusCodeByException($e);
        }

        return $response;
    }

    /**
     * @return ConnectionInterface
     * @throws \flipbox\craft\integration\exceptions\ConnectionNotFound
     */
    protected function resolveConnection(): ConnectionInterface
    {
        $connection = Craft::$app->getRequest()->getParam('connection');
        if (empty($connection)) {
            $connection = Stripe::getInstance()->getSettings()->getDefaultConnection();
        }

        return Stripe::getInstance()->getConnections()->get($connection);
    }
}
