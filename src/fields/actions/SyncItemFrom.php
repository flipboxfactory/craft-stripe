<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\fields\actions;

use craft\base\ElementInterface;
use flipbox\craft\integration\fields\actions\AbstractIntegrationItemAction;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\stripe\fields\Customers;
use flipbox\craft\stripe\queue\SyncElementFromStripeObjectJob;
use flipbox\craft\stripe\Stripe;

class SyncItemFrom extends AbstractIntegrationItemAction
{
    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Stripe::t('Sync From Stripe');
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Stripe::t("Performing a sync will override any unsaved data.  Please confirm to continue.");
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function performAction(Integrations $field, ElementInterface $element, IntegrationAssociation $record): bool
    {
        if (!$field instanceof Customers) {
            $this->setMessage("Invalid field type.");
            return false;
        }

        if (!$field->syncFromStripe($element)) {
            $this->setMessage("Failed to sync from Stripe " . $field->getObjectLabel());
            return false;
        }

        $this->setMessage("Sync from Stripe executed successfully");
        return true;
    }
}
