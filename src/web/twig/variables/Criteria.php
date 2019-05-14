<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\web\twig\variables;

use yii\di\ServiceLocator;
use flipbox\craft\stripe\Stripe as StripePlugin;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Criteria extends ServiceLocator
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $components = [];
        foreach (StripePlugin::getInstance()->getSettings()->getCriteriaResources() as $variable => $resource) {
            $components[$variable] = StripePlugin::criteria($resource);
        }

        $this->setComponents($components);
    }
}
