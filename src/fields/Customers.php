<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\fields;

use flipbox\craft\stripe\Stripe;
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
     * @param array $payload
     * @param string|null $id
     * @return Customer|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \flipbox\craft\integration\exceptions\ConnectionNotFound
     */
    protected function upsertToStripe(
        array $payload,
        string $id = null
    ): ApiResource {
        return Stripe::criteria(Customer::class)
            ->setField($this)
            ->setConnection($this->getConnection())
            ->setCache($this->getCache())
            ->setPayload($payload)
            ->setId($id)
            ->upsert();
    }

    /**
     * @param string $id
     * @return Customer|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \flipbox\craft\integration\exceptions\ConnectionNotFound
     */
    public function readFromStripe(
        string $id
    ): ApiResource {
        return Stripe::criteria(Customer::class)
            ->setField($this)
            ->setConnection($this->getConnection())
            ->setCache($this->getCache())
            ->setId($id)
            ->retrieve();

    }
}
