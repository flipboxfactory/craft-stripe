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
     * The default connection handle
     */
    const DEFAULT_CONNECTION = 'app';

    /**
     * The override file
     */
    public $overrideFile = 'stripe-connections';

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
     *
     * @param string $handle
     * @return bool
     */
    public function setToSDK(string $handle): bool
    {
        if (null === ($connection = $this->find($handle))) {
            return false;
        }

        Stripe::setApiKey($connection->getApiKey());
        Stripe::setApiVersion($connection->getVersion());

        return true;
    }
}
