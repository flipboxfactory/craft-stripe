<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\services;

use flipbox\craft\integration\services\IntegrationConnections;
use flipbox\craft\stripe\records\Connection;
use flipbox\craft\stripe\Stripe as StripePlugin;
use flipbox\craft\stripe\connections\ConnectionInterface;
use Stripe\Stripe;
use yii\helpers\Json;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method ConnectionInterface|null find(string $handle, bool $enabledOnly = true)
 * @method ConnectionInterface get()
 */
class Connections extends IntegrationConnections
{
    /**
     * The override file
     */
    public $overrideFile = 'stripe-connections';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        // Apply default connection to Stripe SDK
        $this->initDefaultConnection();
    }

    /**
     * @inheritdoc
     */
    protected static function tableName(): string
    {
        return Connection::tableName();
    }

    /**
     * @inheritdoc
     */
    protected static function connectionInstance(): string
    {
        return ConnectionInterface::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultConnection(): string
    {
        return StripePlugin::getInstance()->getSettings()->getDefaultConnection();
    }

    /**
     * Set's an API Key and API Version to Stripe SDK
     * @return void
     */
    private function initDefaultConnection()
    {
        try {
            if (null === ($connection = $this->find($this->getDefaultConnection()))) {
                return;
            }

            Stripe::setApiKey($connection->getApiKey());
            Stripe::setApiVersion($connection->getVersion());
        } catch (\Exception $e) {
            StripePlugin::warning(sprintf(
                "Exception caught while trying to set default connection. Exception: [%s].",
                (string)Json::encode([
                    'Trace' => $e->getTraceAsString(),
                    'File' => $e->getFile(),
                    'Line' => $e->getLine(),
                    'Code' => $e->getCode(),
                    'Message' => $e->getMessage()
                ])
            ));
        }
    }
}
