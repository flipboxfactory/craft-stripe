<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\criteria;

use craft\helpers\Json;
use flipbox\craft\stripe\Stripe;
use Psr\SimpleCache\InvalidArgumentException;
use Stripe\Error\Base;
use Stripe\StripeObject;
use yii\base\Component;

/**
 * $criteria = (new \flipbox\craft\stripe\criteria\Criteria())
 *      ->setElement($entry)
 *      ->setField($field);
 *
 * // Retrieve a customer (based on an element/field)
 * $object = $criteria->request(function() use ($criteria) {
 *      return \Stripe\Customer::retrieve(
 *          $criteria->getId(),
 *          $criteria->getRequestOptions()
 *      );
 * });
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Criteria extends Component
{
    use ConnectionTrait,
        CacheTrait,
        IdAttributeTrait,
        PayloadAttributeTrait,
        AllCriteriaTrait;

    /**
     * @return string|null
     */
    protected function getCacheKey()
    {
        return $this->findId();
    }

    /**
     * @param array $options
     * @return array
     */
    public function getRequestOptions(array $options = []): array
    {
        $connection = $this->getConnection();

        return array_filter(
            array_merge([
                'api_key' => $connection->getApiKey(),
                'stripe_version' => $connection->getVersion(),
            ],
                $options
            )
        );
    }

    /**
     * Request an object.  If an Id is present, we'll attempt to cache the results.
     *
     * @param \Closure $callback
     * @return StripeObject|null
     */
    public function request(\Closure $callback)
    {
        try {
            if (null === ($key = $this->getCacheKey())) {
                return $this->resource($callback);
            }

            // If it's cached
            if (null !== ($value = $this->getCache()->get($key))) {
                Stripe::info(
                    sprintf(
                        "Item found in cache. [key: %s, type: %s]",
                        $key,
                        get_class($this->getCache())
                    )
                );

                return $value;
            }

            Stripe::info(
                sprintf(
                    "Item not found in cache. [key: %s, type: %s]",
                    $key,
                    get_class($this->getCache())
                )
            );

            $object = $this->resource($callback);

            $this->getCache()->set($key, $object ?: null);

            Stripe::info(
                sprintf(
                    "Save item to cache. [key: %s, type: %s]",
                    $key,
                    get_class($this->getCache())
                )
            );

            return $object;
        } catch (InvalidArgumentException $e) {
            Stripe::warning(
                sprintf(
                    "InvalidArgumentException caught while trying to run '%s'.  Exception: [%s].",
                    __METHOD__,
                    (string)\yii\helpers\Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                'cache'
            );
        }
    }

    /**
     * Mutate an object. If an Id is present, we'll attempt to break cache.
     *
     * @param \Closure $callback
     * @return StripeObject|null
     */
    public function mutate(\Closure $callback)
    {
        try {
            // Failed
            if (null === ($object = $this->resource($callback))) {
                return null;
            }

            if (null === ($key = $this->getCacheKey())) {
                return $object;
            }

            if ($this->getCache()->delete($key)) {
                Stripe::info(
                    sprintf(
                        "Item removed from cache successfully. [key: %s, type: %s]",
                        $key,
                        get_class($this->getCache())
                    )
                );
            } else {
                Stripe::info(
                    sprintf(
                        "Item not removed from cache. [key: %s, type: %s]",
                        $key,
                        get_class($this->getCache())
                    )
                );
            }

            return $object;
        } catch (InvalidArgumentException $e) {
            Stripe::warning(
                sprintf(
                    "InvalidArgumentException caught while trying to run '%s'.  Exception: [%s].",
                    __METHOD__,
                    (string)\yii\helpers\Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                'cache'
            );
        }

        return null;
    }

    /**
     * @param \Closure $callback
     * @return StripeObject|null
     */
    protected function resource(\Closure $callback)
    {
        try {
            return $callback();
        } catch (Base $e) {
            Stripe::warning(
                sprintf(
                    "Exception caught. Exception: [%s].",
                    (string)Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage(),
                        'StripeCode' => $e->getStripeCode(),
                        'StripeRequestId' => $e->getRequestId()
                    ])
                ), 'Criteria'
            );
        }

        return null;
    }

    /**
     * @param array $properties
     * @return static
     */
    public function populate(array $properties = [])
    {
        if (!empty($properties)) {
            foreach ($properties as $name => $value) {
                if ($this->canSetProperty($name)) {
                    $this->$name = $value;
                }
            }
        }

        return $this;
    }
}
