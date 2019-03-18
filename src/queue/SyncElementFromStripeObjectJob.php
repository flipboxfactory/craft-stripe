<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\queue;

use Craft;
use craft\base\ElementInterface;
use craft\queue\BaseJob;
use flipbox\craft\ember\objects\ElementAttributeTrait;
use flipbox\craft\ember\objects\FieldAttributeTrait;
use flipbox\craft\stripe\fields\Customers;

/**
 * Sync a Stripe Object to a Craft Element
 */
class SyncElementFromStripeObjectJob extends BaseJob implements \Serializable
{
    use FieldAttributeTrait,
        ElementAttributeTrait;

    /**
     * @var string|null
     */
    public $objectId;

    /**
     * @var callable
     */
    public $transformer;

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @inheritdoc
     * @return bool
     */
    public function execute($queue)
    {
        $field = $this->getField();
        $element = $this->getElement();

        if (!$field instanceof Customers || !$element instanceof ElementInterface) {
            return false;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $field->syncToStripe(
            $element,
            $this->objectId,
            $this->transformer
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize([
            'fieldId' => $this->getFieldId(),
            'elementId' => $this->getElementId(),
            'objectId' => $this->objectId,
            'transformer' => $this->transformer
        ]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        Craft::configure(
            $this,
            unserialize($serialized)
        );
    }
}
