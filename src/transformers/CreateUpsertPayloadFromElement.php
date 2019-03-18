<?php

/**
 * @noinspection PhpUnusedParameterInspection
 *
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipbox/stripe/blob/master/LICENSE.md
 * @link       https://github.com/flipbox/stripe
 */

namespace flipbox\craft\stripe\transformers;

use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\stripe\events\CreatePayloadFromElementEvent;
use flipbox\craft\stripe\fields\Customers;
use flipbox\craft\stripe\Stripe;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CreateUpsertPayloadFromElement extends BaseObject
{
    /**
     * An action used to assemble a unique event name.
     *
     * @var string
     */
    public $action;

    /**
     * @param ElementInterface|Element $element
     * @param Customers $field
     * @param string|null $id
     * @return array
     */
    public function __invoke(
        ElementInterface $element,
        Customers $field,
        string $id = null
    ): array {

        $event = new CreatePayloadFromElementEvent([
            'payload' => $this->createPayload($element, $field, $id)
        ]);

        $name = $event::eventName(
            $field->handle,
            $this->action
        );

        Stripe::info(sprintf(
            "Create payload: Event '%s', Element '%s'",
            $name,
            $element->id . ' - ' . $element->title
        ), __METHOD__);

        $element->trigger($name, $event);

        return $event->getPayload();
    }

    /**
     * @param ElementInterface $element
     * @param Customers $field
     * @param string|null $id
     * @return array
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function createPayload(
        ElementInterface $element,
        Customers $field,
        string $id = null
    ): array {
        /** @var Element $element */

        return [];
    }
}
