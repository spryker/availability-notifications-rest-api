<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Processor;

use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionResponseTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\AvailabilityNotification\AvailabilityNotificationClientInterface;
use Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Exception\AvailabilityNotificationsExceptionFactory;
use Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiConfig;

class AvailabilityNotificationsStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string KEY_SUBSCRIPTION_KEY = 'subscriptionKey';

    public function __construct(
        protected AvailabilityNotificationClientInterface $availabilityNotificationClient,
        protected AvailabilityNotificationsExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * The endpoint needs no authentication, so a new and a repeated subscription get the same empty answer
     * and the subscription key goes out only in the mail. Anything else would reveal which email
     * addresses are subscribed, or hand out the key that unsubscribes them.
     *
     * @param \Generated\Api\Storefront\AvailabilityNotificationsStorefrontResource $data
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): mixed
    {
        $subscriptionTransfer = (new AvailabilityNotificationSubscriptionTransfer())
            ->setEmail($data->email)
            ->setSku($data->sku)
            ->setLocale($this->findLocale())
            ->setStore($this->findStore())
            ->setCustomerReference($this->hasCustomer() ? $this->getCustomerReference() : null);

        $responseTransfer = $this->availabilityNotificationClient->subscribe($subscriptionTransfer);

        if (!$responseTransfer->getIsSuccess() && !$this->isRepeatedSubscription($responseTransfer)) {
            throw $this->exceptionFactory->createSubscribeFailureException($responseTransfer);
        }

        return null;
    }

    protected function isRepeatedSubscription(AvailabilityNotificationSubscriptionResponseTransfer $responseTransfer): bool
    {
        return $responseTransfer->getErrorMessage() === AvailabilityNotificationsRestApiConfig::RESPONSE_DETAIL_SUBSCRIPTION_ALREADY_EXISTS;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processDelete(): mixed
    {
        $subscriptionKey = (string)$this->findUriVariable(static::KEY_SUBSCRIPTION_KEY);

        if ($subscriptionKey === '') {
            throw $this->exceptionFactory->createSubscriptionDoesNotExistException();
        }

        $subscriptionTransfer = (new AvailabilityNotificationSubscriptionTransfer())
            ->setSubscriptionKey($subscriptionKey);

        $responseTransfer = $this->availabilityNotificationClient->unsubscribeBySubscriptionKey($subscriptionTransfer);

        if (!$responseTransfer->getIsSuccess()) {
            throw $this->exceptionFactory->createUnsubscribeFailureException($responseTransfer);
        }

        return null;
    }
}
