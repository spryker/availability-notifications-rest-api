<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionCollectionTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionResponseTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface AvailabilityNotificationsRestResponseBuilderInterface
{
    public function createAvailabilityNotificationResponse(
        AvailabilityNotificationSubscriptionTransfer $availabilityNotificationSubscriptionTransfer
    ): RestResponseInterface;

    public function createEmptyResponse(): RestResponseInterface;

    public function createAvailabilityNotificationCollectionResponse(
        AvailabilityNotificationSubscriptionCollectionTransfer $availabilityNotificationSubscriptionCollectionTransfer
    ): RestResponseInterface;

    public function createSubscribeErrorResponse(
        AvailabilityNotificationSubscriptionResponseTransfer $availabilityNotificationSubscriptionResponseTransfer
    ): RestResponseInterface;

    public function createUnsubscribeErrorResponse(
        AvailabilityNotificationSubscriptionResponseTransfer $availabilityNotificationSubscriptionResponseTransfer
    ): RestResponseInterface;

    public function createCustomerUnauthorizedErrorResponse(): RestResponseInterface;

    public function createErrorResponse(RestErrorMessageTransfer $restErrorMessageTransfer): RestResponseInterface;
}
