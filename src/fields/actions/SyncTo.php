<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\fields\actions;

use craft\base\ElementInterface;
use flipbox\craft\integration\fields\actions\AbstractIntegrationAction;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\queries\IntegrationAssociationQuery;
use flipbox\craft\stripe\fields\Customers;
use flipbox\craft\stripe\queue\SyncElementToStripeObjectJob;
use flipbox\craft\stripe\Stripe;
use yii\web\HttpException;

class SyncTo extends AbstractIntegrationAction
{
    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Stripe::t('Create Stripe Object from Element');
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Stripe::t(
            "This element will be used to create a new Stripe Object.  Please confirm to continue."
        );
    }

    /**
     * @inheritdoc
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function performAction(Integrations $field, ElementInterface $element): bool
    {
        if (!$field instanceof Customers) {
            $this->setMessage("Invalid field type.");
            return false;
        }

        /** @var IntegrationAssociationQuery $query */
        if (null === ($query = $element->getFieldValue($field->handle))) {
            throw new HttpException(400, 'Field is not associated to element');
        }

        if (!$field->syncToStripe($element)) {
            $this->setMessage("Failed to create Stripe " . $field->getObjectLabel());
            return false;
        }

        $this->id = $query->select(['objectId'])->scalar();

        $this->setMessage("Created Stripe Object successfully");
        return true;
    }
}
