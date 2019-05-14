<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\criteria;

use Stripe\Customer;

class CustomerCriteria extends ElementCriteria
{
    /**
     * @param array $criteria
     * @return Customer|null
     */
    public function read(array $criteria = [])
    {
        $this->populate($criteria);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->request(function () {
            return Customer::retrieve(
                $this->getId(),
                $this->getRequestOptions()
            );
        });
    }

    /**
     * @param array $criteria
     * @return Customer|null
     */
    public function upsert(array $criteria = [])
    {
        $this->populate($criteria);

        $id = $this->findId();

        if (empty($id)) {
            return $this->create();
        }

        return $this->update();
    }

    /**
     * @param array $criteria
     * @return Customer|null
     */
    public function update(array $criteria = [])
    {
        $this->populate($criteria);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->mutate(function () {
            return Customer::update(
                $this->getId(),
                $this->getPayload(),
                $this->getRequestOptions()
            );
        });
    }

    /**
     * @param array $criteria
     * @return Customer|null
     */
    public function create(array $criteria = [])
    {
        $this->populate($criteria);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->mutate(function () {
            return Customer::create(
                $this->getPayload(),
                $this->getRequestOptions()
            );
        });
    }

    /**
     * @param array $criteria
     * @return Customer|null
     */
    public function delete(array $criteria = [])
    {
        $this->populate($criteria);

        /** @var Customer $object */
        if (null === ($object = $this->read())) {
            return null;
        }

        return $object->delete();
    }

}