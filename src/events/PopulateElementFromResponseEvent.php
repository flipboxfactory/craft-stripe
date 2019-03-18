<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\events;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\StringHelper;
use flipbox\craft\stripe\fields\Customers;
use Psr\Http\Message\ResponseInterface;
use yii\base\Event;

/**
 * @property ElementInterface|Element $sender
 */
class PopulateElementFromResponseEvent extends Event
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var Customers
     */
    private $field;

    /**
     * @var string|null
     */
    public $objectId;

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param Customers $field
     * @return $this
     */
    public function setField(Customers $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return Customers
     */
    public function getField(): Customers
    {
        return $this->field;
    }

    /**
     * @param string $object
     * @param string|null $action
     * @return string
     */
    public static function eventName(
        string $object,
        string $action = null
    ): string {
        $name = array_filter([
            'populate',
            $object,
            $action
        ]);

        return StringHelper::toLowerCase(
            StringHelper::toString($name, ':')
        );
    }
}
