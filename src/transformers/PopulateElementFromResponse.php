<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipbox/stripe/blob/master/LICENSE.md
 * @link       https://github.com/flipbox/stripe
 */

namespace flipbox\craft\stripe\transformers;

use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\stripe\events\PopulateElementFromResponseEvent;
use flipbox\craft\stripe\fields\Customers;
use flipbox\craft\stripe\Force;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class PopulateElementFromResponse
{
    /**
     * An action used to assemble a unique event name.
     *
     * @var string
     */
    public $action;

    /**
     * @param ResponseInterface $response
     * @param ElementInterface $element
     * @param Customers $field
     * @param string $objectId
     * @return ElementInterface
     */
    public function __invoke(
        ResponseInterface $response,
        ElementInterface $element,
        Customers $field,
        string $objectId
    ): ElementInterface {
        $this->populateElementFromResponse($response, $element, $field, $objectId);
        return $element;
    }

    /**
     * @param ResponseInterface $response
     * @param ElementInterface|Element $element
     * @param Customers $field
     * @param string $objectId
     * @return ElementInterface
     */
    protected function populateElementFromResponse(
        ResponseInterface $response,
        ElementInterface $element,
        Customers $field,
        string $objectId
    ): ElementInterface {

        $event = new PopulateElementFromResponseEvent([
            'response' => $response,
            'field' => $field,
            'objectId' => $objectId
        ]);

        $name = $event::eventName(
            $field->handle,
            $this->action
        );

        Force::info(sprintf(
            "Populate Element: Event '%s', Element '%s'",
            $name,
            $element->id . ' - ' . $element->title
        ), __METHOD__);

        $element->trigger($name, $event);

        return $event->sender ?: $element;
    }
}
