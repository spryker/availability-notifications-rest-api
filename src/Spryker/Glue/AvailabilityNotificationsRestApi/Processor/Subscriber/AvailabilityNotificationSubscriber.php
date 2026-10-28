<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Processor\Subscriber;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionResponseTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiConfig;
use Spryker\Glue\AvailabilityNotificationsRestApi\Dependency\Client\AvailabilityNotificationsRestApiToAvailabilityNotificationClientInterface;
use Spryker\Glue\AvailabilityNotificationsRestApi\Dependency\Client\AvailabilityNotificationsRestApiToStoreClientInterface;
use Spryker\Glue\AvailabilityNotificationsRestApi\Processor\RestResponseBuilder\AvailabilityNotificationsRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AvailabilityNotificationSubscriber implements AvailabilityNotificationSubscriberInterface
{
    /**
     * @var \Spryker\Glue\AvailabilityNotificationsRestApi\Dependency\Client\AvailabilityNotificationsRestApiToAvailabilityNotificationClientInterface
     */
    protected $availabilityNotificationClient;

    /**
     * @var \Spryker\Glue\AvailabilityNotificationsRestApi\Dependency\Client\AvailabilityNotificationsRestApiToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Glue\AvailabilityNotificationsRestApi\Processor\RestResponseBuilder\AvailabilityNotificationsRestResponseBuilderInterface
     */
    protected $availabilityNotificationsRestResponseBuilder;

    public function __construct(
        AvailabilityNotificationsRestApiToAvailabilityNotificationClientInterface $availabilityNotificationClient,
        AvailabilityNotificationsRestApiToStoreClientInterface $storeClient,
        AvailabilityNotificationsRestResponseBuilderInterface $availabilityNotificationsRestResponseBuilder,
        protected AvailabilityNotificationsRestApiConfig $availabilityNotificationsRestApiConfig
    ) {
        $this->availabilityNotificationClient = $availabilityNotificationClient;
        $this->storeClient = $storeClient;
        $this->availabilityNotificationsRestResponseBuilder = $availabilityNotificationsRestResponseBuilder;
    }

    /**
     * @uses \Spryker\Glue\AvailabilityNotificationsRestApi\Plugin\GlueApplication\AvailabilityNotificationsResourceRoutePlugin::getResourceAttributesClassName()
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function subscribe(RestRequestInterface $restRequest): RestResponseInterface
    {
        $locale = $restRequest->getMetadata()->getLocale();
        $localeTransfer = (new LocaleTransfer())->setLocaleName($locale);
        $storeTransfer = $this->storeClient->getCurrentStore();
        $restUser = $restRequest->getRestUser();
        $customerReference = $restUser
            ? $restUser->getNaturalIdentifier()
            : null;

        /**
         * @var \Generated\Shared\Transfer\RestAvailabilityNotificationRequestAttributesTransfer $restAvailabilityNotificationRequestAttributesTransfer
         */
        $restAvailabilityNotificationRequestAttributesTransfer = $restRequest->getResource()->getAttributes();

        $availabilityNotificationSubscriptionTransfer = (new AvailabilityNotificationSubscriptionTransfer())
            ->fromArray($restAvailabilityNotificationRequestAttributesTransfer->toArray(), true)
            ->setLocale($localeTransfer)
            ->setStore($storeTransfer)
            ->setCustomerReference($customerReference);

        $availabilityNotificationSubscriptionResponseTransfer = $this->availabilityNotificationClient->subscribe($availabilityNotificationSubscriptionTransfer);

        if ($this->availabilityNotificationsRestApiConfig->isSubscriptionResponseWithoutKeyEnabled()) {
            return $this->createSubscriptionResponseWithoutKey($availabilityNotificationSubscriptionResponseTransfer);
        }

        if (!$availabilityNotificationSubscriptionResponseTransfer->getIsSuccess()) {
            return $this->availabilityNotificationsRestResponseBuilder->createSubscribeErrorResponse($availabilityNotificationSubscriptionResponseTransfer);
        }

        return $this->availabilityNotificationsRestResponseBuilder->createAvailabilityNotificationResponse($availabilityNotificationSubscriptionResponseTransfer->getAvailabilityNotificationSubscriptionOrFail());
    }

    protected function createSubscriptionResponseWithoutKey(
        AvailabilityNotificationSubscriptionResponseTransfer $availabilityNotificationSubscriptionResponseTransfer
    ): RestResponseInterface {
        // A repeated subscription answers like a new one, so the unauthenticated endpoint does not reveal who is subscribed.
        if (!$availabilityNotificationSubscriptionResponseTransfer->getIsSuccess() && !$this->isRepeatedSubscription($availabilityNotificationSubscriptionResponseTransfer)) {
            return $this->availabilityNotificationsRestResponseBuilder->createSubscribeErrorResponse($availabilityNotificationSubscriptionResponseTransfer);
        }

        return $this->availabilityNotificationsRestResponseBuilder->createEmptyResponse()
            ->setStatus(Response::HTTP_ACCEPTED);
    }

    protected function isRepeatedSubscription(
        AvailabilityNotificationSubscriptionResponseTransfer $availabilityNotificationSubscriptionResponseTransfer
    ): bool {
        return $availabilityNotificationSubscriptionResponseTransfer->getErrorMessage() === AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_SUBSCRIPTION_ALREADY_EXISTS;
    }

    public function unsubscribeBySubscriptionKey(RestRequestInterface $restRequest): RestResponseInterface
    {
        $availabilityNotificationSubscriptionResponseTransfer = $this->availabilityNotificationClient->unsubscribeBySubscriptionKey((new AvailabilityNotificationSubscriptionTransfer())->setSubscriptionKey($restRequest->getResource()->getId()));

        if (!$availabilityNotificationSubscriptionResponseTransfer->getIsSuccess()) {
            return $this->availabilityNotificationsRestResponseBuilder->createUnsubscribeErrorResponse($availabilityNotificationSubscriptionResponseTransfer);
        }

        return $this->availabilityNotificationsRestResponseBuilder->createEmptyResponse();
    }
}
