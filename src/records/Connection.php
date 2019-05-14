<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\records;

use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use flipbox\craft\integration\records\IntegrationConnection;
use flipbox\craft\stripe\connections\ConnectionInterface;
use flipbox\craft\stripe\connections\SavableConnectionInterface;
use flipbox\craft\stripe\Stripe;
use flipbox\craft\stripe\validators\ConnectionValidator;
use Flipbox\Skeleton\Helpers\ObjectHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Connection extends IntegrationConnection
{
    /**
     * The table name
     */
    const TABLE_ALIAS = 'stripe_connections';

    /**
     * @var ConnectionInterface|null
     */
    private $connection;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'class'
                    ],
                    ConnectionValidator::class
                ]
            ]
        );
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        if (null === $this->connection) {
            $config = ComponentHelper::mergeSettings([
                'settings' => $this->settings,
                'class' => $this->class
            ]);

            // Apply overrides
            if (!empty($this->handle)) {
                if (null !== ($override = Stripe::getInstance()->getConnections()->getOverrides($this->handle))) {
                    $config = array_merge($config, $override);
                }
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->connection = ObjectHelper::create(
                $config,
                ConnectionInterface::class
            );
        }

        return $this->connection;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $connection = $this->getConnection();

        if ($connection instanceof SavableConnectionInterface) {
            if (!$connection->beforeSave($insert)) {
                return false;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $connection = $this->getConnection();

        if ($connection instanceof SavableConnectionInterface) {
            $connection->afterSave(
                $insert,
                (array)ArrayHelper::getValue($changedAttributes, 'settings', []) ?: []
            );
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $connection = $this->getConnection();

        if ($connection instanceof SavableConnectionInterface) {
            if (!$connection->beforeDelete()) {
                return false;
            }
        }

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $connection = $this->getConnection();

        if ($connection instanceof SavableConnectionInterface) {
            $connection->afterDelete();
        }

        parent::afterDelete();
    }
}
