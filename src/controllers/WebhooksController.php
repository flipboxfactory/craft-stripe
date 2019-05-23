<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use craft\web\Response as WebResponse;
use flipbox\craft\stripe\connections\ConnectionInterface;
use flipbox\craft\stripe\events\ReceiveWebhookEvent;
use flipbox\craft\stripe\Stripe;
use Stripe\Webhook;
use yii\web\Response;

class WebhooksController extends Controller
{
    /**
     * @inheritdoc
     */
    protected $allowAnonymous = ['process-webhook'];

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @return Response
     */
    public function actionProcessWebhook(): Response
    {
        try {
            $response = $this->processWebHook(
                $this->resolveConnection()
            );
        } catch (\Throwable $exception) {
            $message = 'Exception while processing webhook: ' . $exception->getMessage() . "\n";
            $message .= 'Exception thrown in ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
            $message .= 'Stack trace:' . "\n" . $exception->getTraceAsString();

            Craft::error($message, 'commerce');

            $response = Craft::$app->getResponse();
            $response->setStatusCodeByException($exception);
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

    /**
     * @inheritdoc
     */
    protected function processWebHook(ConnectionInterface $connection): WebResponse
    {
        $rawData = Craft::$app->getRequest()->getRawBody();
        $response = Craft::$app->getResponse();

        $secret = $connection->getWebhookSigningSecret();
        $stripeSignature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (!$secret || !$stripeSignature) {
            $message = 'Webhook not signed or signing secret not set.';

            $response->setStatusCode(400);
            $response->data = $message;

            Stripe::warning($message, 'webhook');

            return $response;
        }

        try {
            // Check the payload and signature
            $event = Webhook::constructEvent($rawData, $stripeSignature, $secret);
        } catch (\Exception $exception) {
            $message = 'Webhook signature check failed: ' . $exception->getMessage();

            $response->setStatusCode(400);
            $response->data = $message;

            Stripe::warning($message, 'webhook');

            return $response;
        }

        Stripe::getInstance()->trigger(
            ReceiveWebhookEvent::EVENT_RECEIVE_WEBHOOK,
            new ReceiveWebhookEvent([
                'data' => Json::decodeIfJson($rawData),
                'event' => $event
            ])
        );

        $response->data = 'ok';

        return $response;
    }
}
