<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Generated\Shared\Transfer\RestAvailabilityNotificationsAttributesTransfer;

class AvailabilityNotificationMapper implements AvailabilityNotificationMapperInterface
{
    public function mapAvailabilityNotificationSubscriptionTransferToRestAvailabilityNotificationsAttributesTransfer(
        AvailabilityNotificationSubscriptionTransfer $availabilityNotificationSubscriptionTransfer,
        RestAvailabilityNotificationsAttributesTransfer $restAvailabilityNotificationsAttributesTransfer
    ): RestAvailabilityNotificationsAttributesTransfer {
        $locale = $availabilityNotificationSubscriptionTransfer->getLocale();
        if ($locale !== null) {
            $localeName = $locale->getLocaleName();
        }

        return $restAvailabilityNotificationsAttributesTransfer
                ->fromArray($availabilityNotificationSubscriptionTransfer->toArray(), true)
                ->setLocaleName($localeName ?? null);
    }
}
