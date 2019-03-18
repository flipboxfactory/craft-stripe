<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\criteria;

use flipbox\craft\stripe\connections\ConnectionInterface;
use flipbox\craft\stripe\Stripe;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ConnectionTrait
{
    /**
     * @var ConnectionInterface|null
     */
    protected $connection;

    /**
     * @param $value
     * @return $this
     */
    public function connection($value)
    {
        return $this->setConnection($value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setConnection($value)
    {
        $this->connection = $value;
        return $this;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection = $this->resolveConnection($this->connection);
    }

    /**
     * @param $connection
     * @return ConnectionInterface
     */
    protected function resolveConnection($connection): ConnectionInterface
    {
        if ($connection instanceof ConnectionInterface) {
            return $connection;
        }

        if ($connection === null) {
            $connection = Stripe::getInstance()->getSettings()->getDefaultConnection();
        }

        return Stripe::getInstance()->getConnections()->find($connection);
    }
}
