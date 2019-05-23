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
trait IdAttributeTrait
{
    /**
     * @var string|null
     */
    protected $id;

    /**
     * @return string
     * @throws \Exception
     */
    public function getId(): string
    {
        if (null === ($id = $this->findId())) {
            throw new \Exception("Invalid Object Id");
        }
        return $id;
    }

    /**
     * @return string|null
     */
    public function findId()
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return $this
     */
    public function setId(string $id = null)
    {
        $this->id = $id;
        return $this;
    }
}
