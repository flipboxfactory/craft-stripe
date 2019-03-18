<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\services;

use flipbox\craft\integration\services\IntegrationCache;
use flipbox\craft\stripe\Stripe;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Cache extends IntegrationCache
{
    /**
     * The override file
     */
    public $overrideFile = 'stripe-cache';

    /**
     * @inheritdoc
     */
    protected function getDefaultCache(): string
    {
        return Stripe::getInstance()->getSettings()->getDefaultCache();
    }

    /**
     * @inheritdoc
     */
    protected function handleCacheNotFound(string $handle)
    {
        Stripe::warning(sprintf(
            "Unable to find cache '%s'.",
            $handle
        ));

        return parent::handleCacheNotFound($handle);
    }
}
