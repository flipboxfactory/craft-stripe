<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\criteria;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use flipbox\craft\stripe\fields\Customers;
use flipbox\craft\stripe\helpers\TransformerHelper;
use flipbox\craft\stripe\transformers\CreateUpsertPayloadFromElement;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
trait PayloadAttributeFromElementTrait
{
    use PayloadAttributeTrait;

    /**
     * @var callable
     */
    public $transformer = [
        'class' => CreateUpsertPayloadFromElement::class,
        'action' => 'save'
    ];

    /**
     * @return FieldInterface|Field|null
     */
    abstract public function getField();

    /**
     * @return ElementInterface|Element|null
     */
    abstract public function getElement();

    /**
     * @return array
     */
    public function getPayload(): array
    {
        if (null === $this->payload) {
            $this->payload = $this->resolvePayload();
        }

        return $this->payload;
    }

    /**
     * @return array
     */
    protected function resolvePayload(): array
    {
        $field = $this->getField();
        $element = $this->getElement();

        if (!$field instanceof Customers || !$element instanceof ElementInterface) {
            return [];
        }

        if (null === ($transformer = TransformerHelper::resolveTransformer($this->transformer))) {
            return [];
        }

        $payload = call_user_func_array(
            $transformer,
            [
                $element,
                $field
            ]
        );

        return (array)$payload;
    }
}
