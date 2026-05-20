<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Reader;

use Generated\Shared\Transfer\AvailabilityNotificationCriteriaTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionCollectionTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Client\AvailabilityNotification\AvailabilityNotificationClientInterface;

class AvailabilityNotificationReader implements AvailabilityNotificationReaderInterface
{
    public function __construct(
        protected AvailabilityNotificationClientInterface $availabilityNotificationClient,
    ) {
    }

    public function getSubscriptionsByCustomerReference(
        string $customerReference,
        ?string $storeName,
        int $limit,
        int $offset,
    ): AvailabilityNotificationSubscriptionCollectionTransfer {
        $criteriaTransfer = (new AvailabilityNotificationCriteriaTransfer())
            ->addCustomerReference($customerReference)
            ->setPagination($this->buildPaginationTransfer($limit, $offset));

        if ($storeName !== null && $storeName !== '') {
            $criteriaTransfer->addStoreName($storeName);
        }

        return $this->availabilityNotificationClient->getAvailabilityNotifications($criteriaTransfer);
    }

    protected function buildPaginationTransfer(int $limit, int $offset): PaginationTransfer
    {
        return (new PaginationTransfer())
            ->setMaxPerPage($limit)
            ->setPage(intdiv($offset, max($limit, 1)) + 1);
    }
}
