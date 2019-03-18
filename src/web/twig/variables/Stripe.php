<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\web\twig\variables;

use flipbox\craft\stripe\models\Settings;
use flipbox\craft\stripe\services\Cache;
use flipbox\craft\stripe\services\Connections;
use flipbox\craft\stripe\Stripe as StripePlugin;
use yii\di\ServiceLocator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Stripe extends ServiceLocator
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'connections' => StripePlugin::getInstance()->getConnections(),
            'cache' => StripePlugin::getInstance()->getCache(),
            'criteria' => Criteria::class
        ]);
    }

    /**
     * Sub-Variables that are accessed 'craft.salesforce.settings'
     *
     * @return Settings
     */
    public function getSettings()
    {
        return StripePlugin::getInstance()->getSettings();
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return Connections
     */
    public function getConnections(): Connections
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('connections');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return Cache
     */
    public function getCache(): Cache
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('cache');
    }
}
