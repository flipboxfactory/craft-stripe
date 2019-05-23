<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\queries\IntegrationAssociationQuery;
use flipbox\craft\stripe\connections\ConnectionInterface;
use flipbox\craft\stripe\fields\actions\SyncItemFrom;
use flipbox\craft\stripe\fields\actions\SyncItemTo;
use flipbox\craft\stripe\fields\actions\SyncTo;
use flipbox\craft\stripe\helpers\TransformerHelper;
use flipbox\craft\stripe\records\ObjectAssociation;
use flipbox\craft\stripe\Stripe;
use flipbox\craft\stripe\transformers\PopulateElementErrorsFromUpsertResponse;
use Psr\SimpleCache\CacheInterface;
use Stripe\ApiResource;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class Objects extends Integrations implements ObjectsFieldInterface
{
    /**
     * @inheritdoc
     */
    const TRANSLATION_CATEGORY = 'stripe';

    /**
     * @inheritdoc
     */
    const INPUT_TEMPLATE_PATH = 'stripe/_components/fieldtypes/Objects/input';

    /**
     * @inheritdoc
     */
    const INPUT_ITEM_TEMPLATE_PATH = 'stripe/_components/fieldtypes/Objects/_inputItem';

    /**
     * @inheritdoc
     */
    const SETTINGS_TEMPLATE_PATH = 'stripe/_components/fieldtypes/Objects/settings';

    /**
     * @inheritdoc
     */
    const ACTION_PREFORM_ACTION_PATH = 'stripe/cp/fields/perform-action';

    /**
     * @inheritdoc
     */
    const ACTION_CREATE_ITEM_PATH = 'stripe/cp/fields/create-item';

    /**
     * @inheritdoc
     */
    const ACTION_ASSOCIATION_ITEM_PATH = 'stripe/cp/objects/associate';

    /**
     * @inheritdoc
     */
    const ACTION_DISSOCIATION_ITEM_PATH = 'stripe/cp/objects/dissociate';

    /**
     * @inheritdoc
     */
    const ACTION_PREFORM_ITEM_ACTION_PATH = 'stripe/cp/fields/perform-item-action';

    /**
     * Indicates whether the full sync operation should be preformed if a matching Stripe Object was found but not
     * currently associated to the element.  For example, when attempting to Sync a Craft User to a Stripe Contact, if
     * the Stripe Contact already exists; true would override data in Stripe while false would just perform
     * an association (note, a subsequent sync operation could be preformed)
     * @var bool
     *
     * @deprecated
     */
    public $syncToStripeOnMatch = false;

    /**
     * @inheritdoc
     */
    protected $defaultAvailableActions = [
        SyncTo::class
    ];

    /**
     * @inheritdoc
     */
    protected $defaultAvailableItemActions = [
        SyncItemFrom::class,
        SyncItemTo::class,
    ];

    /**
     * @param array $payload
     * @param string|null $id
     * @return ApiResource
     */
    abstract protected function upsertToStripe(
        array $payload,
        string $id = null
    ): ApiResource;

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return ObjectAssociation::class;
    }

    /*******************************************
     * CONNECTION
     *******************************************/

    /**
     * @return ConnectionInterface
     * @throws \flipbox\craft\integration\exceptions\ConnectionNotFound
     */
    public function getConnection(): ConnectionInterface
    {
        return Stripe::getInstance()->getConnections()->get();
    }

    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return Stripe::getInstance()->getCache()->get();
    }


    /*******************************************
     * SYNC TO
     *******************************************/

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function syncToStripe(
        ElementInterface $element,
        string $objectId = null,
        $transformer = null
    ): bool {
        /** @var Element $element */

        $id = $objectId ?: $this->resolveObjectIdFromElement($element);

        // Get callable used to create payload
        if (null === ($transformer = TransformerHelper::resolveTransformer($transformer))) {
            $transformer = Stripe::getInstance()->getSettings()->getSyncUpsertPayloadTransformer();
        }

        // Create payload
        $payload = call_user_func_array(
            $transformer,
            [
                $element,
                $this,
                $id
            ]
        );

        try {
            $object = $this->upsertToStripe($payload, $id);

            if (empty($objectId)) {
                if (null === ($objectId = $object->id)) {
                    Stripe::error("Unable to determine object id from response");
                    return false;
                }

                return $this->addAssociation($element, $objectId);
            }

            return true;
        } catch (\Exception $e) {
            call_user_func_array(
                new PopulateElementErrorsFromUpsertResponse(),
                [
                    $element,
                    $this,
                    $objectId
                ]
            );
        }

        return false;
    }

    /*******************************************
     * SYNC FROM
     *******************************************/

    /**
     * @@inheritdoc
     * @throws \Throwable
     */
    public function syncFromStripe(
        ElementInterface $element,
        string $objectId = null,
        $transformer = null
    ): bool {

        $id = $objectId ?: $this->resolveObjectIdFromElement($element);

        if (null === $id) {
            return false;
        }

        try {
            $object = $this->readFromStripe($id);

            // Get callable used to populate element
            if (null === ($transformer = TransformerHelper::resolveTransformer($transformer))) {
                $transformer = Stripe::getInstance()->getSettings()->getSyncPopulateElementTransformer();
            }

            // Populate element
            call_user_func_array(
                $transformer,
                [
                    $object,
                    $element,
                    $this,
                    $id
                ]
            );

            if ($objectId !== null) {
                $this->addAssociation(
                    $element,
                    $id
                );
            }

            return Craft::$app->getElements()->saveElement($element);
        } catch (\Exception $e) {
            call_user_func_array(
                new PopulateElementErrorsFromUpsertResponse(),
                [
                    $element,
                    $this,
                    $objectId
                ]
            );
        }

        return false;
    }

    /**
     * @param ElementInterface|Element $element
     * @param string $id
     * @return bool
     * @throws \Throwable
     */
    public function addAssociation(
        ElementInterface $element,
        string $id
    ) {
        /** @var IntegrationAssociationQuery $query */
        if (null === ($query = $element->getFieldValue($this->handle))) {
            Stripe::warning("Field is not available on element.");
            return false;
        };

        /** @var ObjectAssociation[] $association */
        $associations = ArrayHelper::index($query->all(), 'objectId');

        if (!array_key_exists($id, $associations)) {
            $associations[$id] = $association = new ObjectAssociation([
                'element' => $element,
                'field' => $this,
                'siteId' => SiteHelper::ensureSiteId($element->siteId),
                'objectId' => $id
            ]);

            $query->setCachedResult(array_values($associations));

            return $association->save();
        }

        return true;
    }

    /**
     * @param ElementInterface|Element $element
     * @param string $id
     * @return bool
     * @throws \Throwable
     */
    public function removeAssociation(
        ElementInterface $element,
        string $id
    ) {
        /** @var IntegrationAssociationQuery $query */
        if (null === ($query = $element->getFieldValue($this->handle))) {
            Stripe::warning("Field is not available on element.");
            return false;
        };

        /** @var ObjectAssociation[] $association */
        $associations = ArrayHelper::index($query->all(), 'objectId');

        if ($association = ArrayHelper::remove($associations, $id)) {
            $query->setCachedResult(array_values($associations));
            return $association->delete();
        }

        return true;
    }

    /**
     * @param ElementInterface|Element $element
     * @return null|string
     */
    public function resolveObjectIdFromElement(
        ElementInterface $element
    ) {

        if (!$objectId = ObjectAssociation::find()
            ->select(['objectId'])
            ->elementId($element->getId())
            ->fieldId($this->id)
            ->siteId(SiteHelper::ensureSiteId($element->siteId))
            ->scalar()
        ) {
            Stripe::warning(sprintf(
                "Stripe Object Id association was not found for element '%s'",
                $element->getId()
            ));

            return null;
        }

        Stripe::info(sprintf(
            "Stripe Object Id '%s' was found for element '%s'",
            $objectId,
            $element->getId()
        ));

        return $objectId;
    }

    /**
     * @param int $elementId
     * @param int|null $siteId
     * @return bool|false|string|null
     */
    public function resolveObjectIdFromElementId(
        int $elementId,
        int $siteId = null
    ) {
        if (!$objectId = ObjectAssociation::find()
            ->select(['objectId'])
            ->elementId($elementId)
            ->fieldId($this->id)
            ->siteId(SiteHelper::ensureSiteId($siteId))
            ->scalar()
        ) {
            Stripe::warning(sprintf(
                "Stripe Object Id association was not found for element '%s'",
                $elementId
            ));

            return null;
        }

        Stripe::info(sprintf(
            "Stripe Object Id '%s' was found for element '%s'",
            $objectId,
            $elementId
        ));

        return $objectId;
    }

    /**
     * @param string $objectId
     * @param int|null $siteId
     * @return bool|false|string|null
     */
    public function resolveElementIdFromObjectId(
        string $objectId,
        int $siteId = null
    ) {
        if (!$elementId = ObjectAssociation::find()
            ->select(['elementId'])
            ->objectId($objectId)
            ->fieldId($this->id)
            ->siteId(SiteHelper::ensureSiteId($siteId))
            ->scalar()
        ) {
            Stripe::warning(sprintf(
                "Stripe Element Id association was not found for object '%s'",
                $objectId
            ));

            return null;
        }

        Stripe::info(sprintf(
            "Stripe Element Id '%s' was found for object '%s'",
            $elementId,
            $objectId
        ));

        return $elementId;
    }
}
