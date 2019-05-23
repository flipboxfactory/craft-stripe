<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\actions\objects;

use flipbox\craft\stripe\fields\ObjectsFieldInterface;
use flipbox\craft\integration\actions\objects\AssociateObject as AssociateIntegration;
use flipbox\craft\integration\records\IntegrationAssociation;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AssociateObject extends AssociateIntegration
{
    /**
     * @inheritdoc
     * @param IntegrationAssociation $record
     */
    protected function validate(
        IntegrationAssociation $record
    ): bool {

        $field = $record->getField();

        if (!$field instanceof ObjectsFieldInterface) {
            return false;
        }

        try {

            /** @var ResponseInterface $response */
            $object = $field->readFromStripe(
                $record->objectId
            );

            return true;
        } catch (\Exception $e) {
        }

        return false;
    }
}
