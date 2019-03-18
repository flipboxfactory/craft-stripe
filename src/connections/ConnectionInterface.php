<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\connections;

interface ConnectionInterface
{
    /**
     * @return string
     */
    public function getApiKey(): string;

    /**
     * @return string
     */
    public function getPublishableKey(): string;

    /**
     * @return string|null
     */
    public function getVersion();
}
