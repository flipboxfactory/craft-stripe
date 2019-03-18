<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\fields;

use craft\helpers\Json;
use flipbox\craft\stripe\criteria\Criteria;
use flipbox\craft\stripe\Stripe;
use Psr\Http\Message\ResponseInterface;
use Stripe\ApiResource;
use Stripe\Customer;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Customers extends Objects
{
    /**
     * @inheritdoc
     */
    public function getObjectLabel(): string
    {
        return 'Customer';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Stripe::t('Stripe: Customers');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Stripe::t('Add a Stripe Customer');
    }

    /**
     * @inheritdoc
     * @return Customer
     * @throws \flipbox\craft\integration\exceptions\ConnectionNotFound
     */
    protected function upsertToStripe(
        array $payload,
        string $id = null
    ): ApiResource {

        return (new Criteria([
            'connection' => $this->getConnection(),
            'cache' => $this->getCache(),
            'payload' => $payload,
            'id' => $id
        ]))->upsert();
    }

    /**
     * @inheritdoc
     * @return Customer
     * @throws \flipbox\craft\integration\exceptions\ConnectionNotFound
     * @throws \Exception
     */
    public function readFromStripe(
        string $id
    ): ApiResource {
        return (new Criteria([
            'connection' => $this->getConnection(),
            'cache' => $this->getCache(),
            'id' => $id
        ]))->read();
    }
}
