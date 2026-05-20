<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\AvailabilityNotificationsStorefrontResource;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Reader\AvailabilityNotificationReaderInterface;

class AvailabilityNotificationsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string KEY_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected AvailabilityNotificationReaderInterface $availabilityNotificationReader,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\AvailabilityNotificationsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $customerReference = $this->resolveCustomerReference();

        if ($customerReference === '') {
            return [];
        }

        $limit = $this->getPaginationLimit();
        $offset = $this->getPaginationOffset();

        $collectionTransfer = $this->availabilityNotificationReader->getSubscriptionsByCustomerReference(
            $customerReference,
            $this->findStoreName(),
            $limit,
            $offset,
        );

        $resources = [];
        foreach ($collectionTransfer->getAvailabilityNotificationSubscriptions() as $subscriptionTransfer) {
            $resources[] = $this->buildResource($subscriptionTransfer);
        }

        if ($resources !== []) {
            $totalCount = $collectionTransfer->getPagination()?->getNbResults() ?? count($resources);
            // Consumed by Spryker\ApiPlatform\EventSubscriber\PaginationLinksResponseSubscriber
            // to emit JSON:API top-level pagination links.
            $resources[0]->pagination = $this->calculatePagination($offset, $limit, $totalCount);
        }

        return $resources;
    }

    /**
     * `/customers/{customerReference}/availability-notifications` puts the reference on the URI;
     * `/my-availability-notifications` carries no URI variable and falls back to the authenticated
     * customer (validated by `is_granted('ROLE_CUSTOMER')` on the operation).
     */
    protected function resolveCustomerReference(): string
    {
        if ($this->hasUriVariable(static::KEY_CUSTOMER_REFERENCE)) {
            return (string)$this->getUriVariable(static::KEY_CUSTOMER_REFERENCE);
        }

        if (!$this->hasCustomer()) {
            return '';
        }

        return $this->getCustomerReference();
    }

    protected function buildResource(
        AvailabilityNotificationSubscriptionTransfer $subscriptionTransfer,
    ): AvailabilityNotificationsStorefrontResource {
        $resource = new AvailabilityNotificationsStorefrontResource();
        $resource->subscriptionKey = $subscriptionTransfer->getSubscriptionKey();
        $resource->email = $subscriptionTransfer->getEmail();
        $resource->sku = $subscriptionTransfer->getSku();
        $resource->localeName = $subscriptionTransfer->getLocale()?->getLocaleName();

        return $resource;
    }
}
