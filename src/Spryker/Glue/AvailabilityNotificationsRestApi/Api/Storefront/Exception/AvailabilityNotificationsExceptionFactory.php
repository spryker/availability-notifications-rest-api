<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Exception;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionResponseTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class AvailabilityNotificationsExceptionFactory
{
    public function createProductNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_PRODUCT_NOT_FOUND,
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_PRODUCT_NOT_FOUND,
        );
    }

    public function createSubscriptionAlreadyExistsException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_SUBSCRIPTION_ALREADY_EXISTS,
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_SUBSCRIPTION_ALREADY_EXISTS,
        );
    }

    public function createSubscriptionDoesNotExistException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_SUBSCRIPTION_DOES_NOT_EXIST,
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_SUBSCRIPTION_DOES_NOT_EXIST,
        );
    }

    public function createFailedToSubscribeException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_FAILED_TO_SUBSCRIBE,
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_FAILED_TO_SUBSCRIBE,
        );
    }

    public function createFailedToUnsubscribeException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_FAILED_TO_UNSUBSCRIBE,
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_FAILED_TO_UNSUBSCRIBE,
        );
    }

    /**
     * Maps the error message returned by the Zed subscribe call to a GlueApiException carrying the
     * matching legacy Spryker error code/detail/status. Falls back to a generic "failed to subscribe"
     * 422 when the message is not recognised.
     */
    public function createSubscribeFailureException(
        AvailabilityNotificationSubscriptionResponseTransfer $responseTransfer,
    ): GlueApiException {
        return match ($responseTransfer->getErrorMessage()) {
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_PRODUCT_NOT_FOUND => $this->createProductNotFoundException(),
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_SUBSCRIPTION_ALREADY_EXISTS => $this->createSubscriptionAlreadyExistsException(),
            default => $this->createFailedToSubscribeException(),
        };
    }

    /**
     * Maps the error message returned by the Zed unsubscribe call to a GlueApiException carrying the
     * matching legacy Spryker error code/detail/status. Falls back to a generic "failed to unsubscribe"
     * 422 when the message is not recognised.
     */
    public function createUnsubscribeFailureException(
        AvailabilityNotificationSubscriptionResponseTransfer $responseTransfer,
    ): GlueApiException {
        return match ($responseTransfer->getErrorMessage()) {
            AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_SUBSCRIPTION_DOES_NOT_EXIST => $this->createSubscriptionDoesNotExistException(),
            default => $this->createFailedToUnsubscribeException(),
        };
    }
}
