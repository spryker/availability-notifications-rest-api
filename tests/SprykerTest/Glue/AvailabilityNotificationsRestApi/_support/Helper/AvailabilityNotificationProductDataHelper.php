<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Glue\AvailabilityNotificationsRestApi\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use SprykerTest\ApiPlatform\Helper\ApiRequestHelperTrait;
use SprykerTest\Shared\Product\Helper\ProductDataHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * The product an availability subscription is taken out on. The request bodies stay on the test
 * case.
 */
class AvailabilityNotificationProductDataHelper extends Module
{
    use ApiRequestHelperTrait;

    protected const string RESOURCE_AVAILABILITY_NOTIFICATIONS = 'availability-notifications';

    protected const string URI_COLLECTION = '/availability-notifications';

    protected const string URI_MY_COLLECTION = '/my-availability-notifications';

    protected const string ATTRIBUTE_EMAIL = 'email';

    protected const string ATTRIBUTE_SKU = 'sku';

    public function arrangeProduct(): ProductConcreteTransfer
    {
        return $this->getProductDataHelper()->haveFullProduct();
    }

    protected function getProductDataHelper(): ProductDataHelper
    {
        /** @var \SprykerTest\Shared\Product\Helper\ProductDataHelper $productDataHelper */
        $productDataHelper = $this->getModule('\\' . ProductDataHelper::class);

        return $productDataHelper;
    }

    public function customerCollectionUri(CustomerTransfer $customerTransfer): string
    {
        return sprintf('/customers/%s/availability-notifications', $customerTransfer->getCustomerReference());
    }

    public function subscriptionUri(string $subscriptionKey): string
    {
        return static::URI_COLLECTION . '/' . $subscriptionKey;
    }

    /**
     * Subscribes the given e-mail to the given product's availability.
     */
    public function postAvailabilityNotification(string $email, string $sku): Response
    {
        return $this->handleApiRequest(
            'POST',
            static::URI_COLLECTION,
            $this->encodeJsonApiBody(static::RESOURCE_AVAILABILITY_NOTIFICATIONS, [
                static::ATTRIBUTE_EMAIL => $email,
                static::ATTRIBUTE_SKU => $sku,
            ]),
        );
    }

    /**
     * The subscriptions of the authenticated customer.
     */
    public function getMyAvailabilityNotifications(): Response
    {
        return $this->handleApiRequest('GET', static::URI_MY_COLLECTION);
    }
}
