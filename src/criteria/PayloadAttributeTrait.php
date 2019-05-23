<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\criteria;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 3.3.0
 */
trait PayloadAttributeTrait
{
    /**
     * @var array|null
     */
    protected $payload;

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return array_filter((array)$this->payload);
    }

    /**
     * @param $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }
}
