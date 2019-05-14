<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\records;

use flipbox\craft\integration\records\EnvironmentalTableTrait;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\stripe\fields\Objects;
use flipbox\craft\stripe\migrations\ObjectAssociations;
use flipbox\craft\stripe\Stripe;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $fieldId
 * @property string $objectId
 */
class ObjectAssociation extends IntegrationAssociation
{
    use EnvironmentalTableTrait;

    /**
     * @inheritdoc
     */
    const TABLE_ALIAS = 'stripe_objects';

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public static function tableAlias()
    {
        return static::environmentTableAlias();
    }

    /**
     * @inheritdoc
     */
    protected static function environmentTableAlias()
    {
        return static::TABLE_ALIAS . Stripe::getInstance()->getSettings()->environmentTableSuffix;
    }

    /**
     * @inheritdoc
     */
    protected static function createEnvironmentTableMigration()
    {
        return new ObjectAssociations();
    }

    /**
     * @return \Stripe\ApiResource|null
     */
    public function getObject()
    {
        /** @var Objects $field */
        if (null === ($field = $this->getField())) {
            return null;
        }

        return $field->readFromStripe($this->objectId ?: self::DEFAULT_ID);
    }
}
