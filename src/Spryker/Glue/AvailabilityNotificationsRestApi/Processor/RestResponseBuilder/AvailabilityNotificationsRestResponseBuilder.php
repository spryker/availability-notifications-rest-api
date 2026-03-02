<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionCollectionTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionResponseTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Generated\Shared\Transfer\RestAvailabilityNotificationsAttributesTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiConfig;
use Spryker\Glue\AvailabilityNotificationsRestApi\Processor\Mapper\AvailabilityNotificationMapperInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

class AvailabilityNotificationsRestResponseBuilder implements AvailabilityNotificationsRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\AvailabilityNotificationsRestApi\Processor\Mapper\AvailabilityNotificationMapperInterface
     */
    protected $availabilityNotificationMapper;

    /**
     * @var \Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiConfig
     */
    protected $availabilityNotificationsRestApiConfig;

    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        AvailabilityNotificationMapperInterface $availabilityNotificationMapper,
        AvailabilityNotificationsRestApiConfig $availabilityNotificationsRestApiConfig
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->availabilityNotificationMapper = $availabilityNotificationMapper;
        $this->availabilityNotificationsRestApiConfig = $availabilityNotificationsRestApiConfig;
    }

    public function createAvailabilityNotificationResponse(
        AvailabilityNotificationSubscriptionTransfer $availabilityNotificationSubscriptionTransfer
    ): RestResponseInterface {
        $restResource = $this->createAvailabilityNotificationResource($availabilityNotificationSubscriptionTransfer);

        $restResponse = $this->restResourceBuilder->createRestResponse();
        $restResponse->addResource($restResource);

        return $restResponse;
    }

    public function createEmptyResponse(): RestResponseInterface
    {
        return $this->restResourceBuilder->createRestResponse();
    }

    public function createAvailabilityNotificationCollectionResponse(
        AvailabilityNotificationSubscriptionCollectionTransfer $availabilityNotificationSubscriptionCollectionTransfer
    ): RestResponseInterface {
        $pagination = $availabilityNotificationSubscriptionCollectionTransfer->getPagination();
        if ($pagination) {
            $totalItems = $pagination->getNbResults() ?? 0;
            $limit = $pagination->getMaxPerPage() ?? 0;
        }

        $restResponse = $this->restResourceBuilder->createRestResponse($totalItems ?? 0, $limit ?? 0);

        foreach ($availabilityNotificationSubscriptionCollectionTransfer->getAvailabilityNotificationSubscriptions() as $availabilityNotificationSubscriptionTransfer) {
            $restResource = $this->createAvailabilityNotificationResource($availabilityNotificationSubscriptionTransfer);
            $restResponse->addResource($restResource);
        }

        return $restResponse;
    }

    public function createSubscribeErrorResponse(
        AvailabilityNotificationSubscriptionResponseTransfer $availabilityNotificationSubscriptionResponseTransfer
    ): RestResponseInterface {
        $restErrorPayload = $this->availabilityNotificationsRestApiConfig->getErrorIdentifierToRestErrorMapping()[$availabilityNotificationSubscriptionResponseTransfer->getErrorMessage()] ?? $this->availabilityNotificationsRestApiConfig->getDefaultSubscribeRestError();

        return $this->createErrorResponse(
            (new RestErrorMessageTransfer())
                ->fromArray($restErrorPayload),
        );
    }

    public function createUnsubscribeErrorResponse(
        AvailabilityNotificationSubscriptionResponseTransfer $availabilityNotificationSubscriptionResponseTransfer
    ): RestResponseInterface {
        $restErrorPayload = $this->availabilityNotificationsRestApiConfig->getErrorIdentifierToRestErrorMapping()[$availabilityNotificationSubscriptionResponseTransfer->getErrorMessage()] ?? $this->availabilityNotificationsRestApiConfig->getDefaultUnsubscribeRestError();

        return $this->createErrorResponse(
            (new RestErrorMessageTransfer())
                ->fromArray($restErrorPayload),
        );
    }

    public function createCustomerUnauthorizedErrorResponse(): RestResponseInterface
    {
        $restErrorPayload = $this->availabilityNotificationsRestApiConfig->getCustomerUnauthorizedRestError();

        return $this->createErrorResponse(
            (new RestErrorMessageTransfer())
                ->fromArray($restErrorPayload),
        );
    }

    public function createErrorResponse(RestErrorMessageTransfer $restErrorMessageTransfer): RestResponseInterface
    {
        return $this->restResourceBuilder->createRestResponse()->addError($restErrorMessageTransfer);
    }

    protected function createAvailabilityNotificationResource(
        AvailabilityNotificationSubscriptionTransfer $availabilityNotificationSubscriptionTransfer
    ): RestResourceInterface {
        $restAvailabilityNotificationsAttributesTransfer = $this
            ->availabilityNotificationMapper
            ->mapAvailabilityNotificationSubscriptionTransferToRestAvailabilityNotificationsAttributesTransfer(
                $availabilityNotificationSubscriptionTransfer,
                new RestAvailabilityNotificationsAttributesTransfer(),
            );

        return $this->restResourceBuilder->createRestResource(
            AvailabilityNotificationsRestApiConfig::RESOURCE_AVAILABILITY_NOTIFICATIONS,
            $availabilityNotificationSubscriptionTransfer->getSubscriptionKey(),
            $restAvailabilityNotificationsAttributesTransfer,
        );
    }
}
