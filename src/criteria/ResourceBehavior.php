<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\criteria;

use Stripe\ApiOperations\All;
use Stripe\ApiOperations\Create;
use Stripe\ApiOperations\Delete;
use Stripe\ApiOperations\Retrieve;
use Stripe\ApiOperations\Update;
use Stripe\ApiResource;
use yii\base\Behavior;

/**
 * @property Criteria $owner
 */
class ResourceBehavior extends Behavior
{
    /**
     * @var ApiResource|All|Create|Delete|Retrieve|Update
     */
    public $resource = ApiResource::class;

    /**
     * @param array $criteria
     * @return ApiResource|All|Create|Delete|Retrieve|Update|null
     */
    public function retrieve(array $criteria = [])
    {
        $this->owner->populate($criteria);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->owner->request(function () {
            return $this->resource::retrieve(
                $this->owner->getId(),
                $this->owner->getRequestOptions()
            );
        });
    }

    /**
     * @param array $params
     * @param array $criteria
     * @return ApiResource|All|Create|Delete|Retrieve|Update|null
     */
    public function all(array $params = [], array $criteria = [])
    {
        $this->owner->populate($criteria);
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->owner->request(function () use ($params) {
            return $this->resource::all(
                $params,
                $this->owner->getRequestOptions()
            );
        });
    }

    /**
     * @param array $criteria
     * @return ApiResource|All|Create|Delete|Retrieve|Update|null
     */
    public function upsert(array $criteria = [])
    {
        $this->owner->populate($criteria);

        $id = $this->owner->findId();

        if (empty($id)) {
            return $this->create();
        }

        return $this->update();
    }

    /**
     * @param array $criteria
     * @return ApiResource|All|Create|Delete|Retrieve|Update|null
     */
    public function update(array $criteria = [])
    {
        $this->owner->populate($criteria);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->owner->mutate(function () {
            return $this->resource::update(
                $this->owner->getId(),
                $this->owner->getPayload(),
                $this->owner->getRequestOptions()
            );
        });
    }

    /**
     * @param array $criteria
     * @return ApiResource|All|Create|Delete|Retrieve|Update|null
     */
    public function create(array $criteria = [])
    {
        $this->owner->populate($criteria);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->owner->mutate(function () {
            return $this->resource::create(
                $this->owner->getPayload(),
                $this->owner->getRequestOptions()
            );
        });
    }

    /**
     * @param array $criteria
     * @return ApiResource|All|Create|Delete|Retrieve|Update|null
     */
    public function delete(array $criteria = [])
    {
        if (null === ($object = $this->retrieve($criteria))) {
            return null;
        }

        return $object->delete();
    }
}