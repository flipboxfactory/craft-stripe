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
trait AllCriteriaTrait
{
    /**
     * @var array
     */
    protected $all = [
        'created' => [
            'gt' => null,
            'gte' => null,
            'lt' => null,
            'lte' => null,
        ],
        'limit' => 10,
        'ending_before' => null,
        'starting_after' => null
    ];

    /**
     * @return array
     */
    public function getAllCriteria(): array
    {
        return array_filter($this->all);
    }

    /**
     * @param array $all
     * @return $this
     */
    public function setAllCriteria(array $all = [])
    {
        $this->all = $all;
        return $this;
    }
}