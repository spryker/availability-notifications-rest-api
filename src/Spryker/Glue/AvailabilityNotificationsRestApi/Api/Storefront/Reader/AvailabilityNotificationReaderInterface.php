<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Reader;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionCollectionTransfer;

interface AvailabilityNotificationReaderInterface
{
    /**
     * Specification:
     * - Fetches availability notification subscriptions for the given customer reference.
     * - Scopes the result to the given store name when provided.
     * - Applies `?page[limit]` / `?page[offset]` translated to the legacy `maxPerPage` / `page`
     *   shape consumed by the AvailabilityNotification client.
     * - Returns the subscription collection together with its pagination metadata.
     */
    public function getSubscriptionsByCustomerReference(
        string $customerReference,
        ?string $storeName,
        int $limit,
        int $offset,
    ): AvailabilityNotificationSubscriptionCollectionTransfer;
}
