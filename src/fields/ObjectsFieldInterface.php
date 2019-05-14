<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\fields;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use Stripe\ApiResource;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
interface ObjectsFieldInterface extends FieldInterface
{
    /**
     * @param string $id
     * @return ApiResource
     */
    public function readFromStripe(
        string $id
    ): ApiResource;

    /**
     * @param ElementInterface $element
     * @param string|null $objectId
     * @param callable|array|string $transformer
     * @return bool
     */
    public function syncFromStripe(
        ElementInterface $element,
        string $objectId = null,
        $transformer = null
    ): bool;

    /**
     * @param ElementInterface $element
     * @param string|null $objectId
     * @param callable|array|string $transformer
     * @return bool
     */
    public function syncToStripe(
        ElementInterface $element,
        string $objectId = null,
        $transformer = null
    ): bool;
}
