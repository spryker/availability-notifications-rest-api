<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\AvailabilityNotificationsStorefrontResource;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\AvailabilityNotification\AvailabilityNotificationClientInterface;
use Spryker\Glue\AvailabilityNotificationsRestApi\Api\Storefront\Exception\AvailabilityNotificationsExceptionFactory;

class AvailabilityNotificationsStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string KEY_SUBSCRIPTION_KEY = 'subscriptionKey';

    public function __construct(
        protected AvailabilityNotificationClientInterface $availabilityNotificationClient,
        protected AvailabilityNotificationsExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @param \Generated\Api\Storefront\AvailabilityNotificationsStorefrontResource $data
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): AvailabilityNotificationsStorefrontResource
    {
        $subscriptionTransfer = (new AvailabilityNotificationSubscriptionTransfer())
            ->setEmail($data->email)
            ->setSku($data->sku)
            ->setLocale($this->findLocale())
            ->setStore($this->findStore())
            ->setCustomerReference($this->hasCustomer() ? $this->getCustomerReference() : null);

        $responseTransfer = $this->availabilityNotificationClient->subscribe($subscriptionTransfer);

        if (!$responseTransfer->getIsSuccess()) {
            throw $this->exceptionFactory->createSubscribeFailureException($responseTransfer);
        }

        $createdSubscriptionTransfer = $responseTransfer->getAvailabilityNotificationSubscriptionOrFail();

        $data->subscriptionKey = $createdSubscriptionTransfer->getSubscriptionKey();
        $data->email = $createdSubscriptionTransfer->getEmail();
        $data->sku = $createdSubscriptionTransfer->getSku();
        $data->localeName = $createdSubscriptionTransfer->getLocale()?->getLocaleName();

        return $data;
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
