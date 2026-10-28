<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\AvailabilityNotificationsRestApi\Processor\Subscriber;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionResponseTransfer;
use Generated\Shared\Transfer\AvailabilityNotificationSubscriptionTransfer;
use Generated\Shared\Transfer\RestAvailabilityNotificationRequestAttributesTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiConfig;
use Spryker\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiDependencyProvider;
use Spryker\Glue\AvailabilityNotificationsRestApi\Dependency\Client\AvailabilityNotificationsRestApiToAvailabilityNotificationClientInterface;
use Spryker\Glue\AvailabilityNotificationsRestApi\Dependency\Client\AvailabilityNotificationsRestApiToStoreClientInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResource;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilder;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\Metadata;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequest;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use SprykerTest\Glue\AvailabilityNotificationsRestApi\AvailabilityNotificationsRestApiTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group AvailabilityNotificationsRestApi
 * @group Processor
 * @group Subscriber
 * @group AvailabilityNotificationSubscriberTest
 * Add your own group annotations below this line
 */
class AvailabilityNotificationSubscriberTest extends Unit
{
    /**
     * @uses \Spryker\Shared\AvailabilityNotification\AvailabilityNotificationConfig::MESSAGE_SUBSCRIPTION_ALREADY_EXISTS
     */
    protected const string ERROR_MESSAGE_SUBSCRIPTION_ALREADY_EXISTS = 'Subscription already exists.';

    /**
     * @uses \Spryker\Shared\AvailabilityNotification\AvailabilityNotificationConfig::MESSAGE_PRODUCT_NOT_FOUND
     */
    protected const string ERROR_MESSAGE_PRODUCT_NOT_FOUND = 'Product not found.';

    protected const string CONFIG_METHOD_SUBSCRIPTION_RESPONSE_WITHOUT_KEY = 'isSubscriptionResponseWithoutKeyEnabled';

    protected const string SUBSCRIPTION_KEY = 'subscription-key';

    protected const string SKU = 'sku-1';

    protected const string EMAIL = 'subscriber@spryker.local';

    protected AvailabilityNotificationsRestApiTester $tester;

    public function testGivenTheDefaultConfigWhenSubscribingThenTheResponseCarriesTheSubscriptionResource(): void
    {
        // Arrange
        $this->mockAvailabilityNotificationClient($this->createNewSubscriptionResponseTransfer());

        // Act
        $restResponse = $this->subscribe();

        // Assert
        $this->assertCount(0, $restResponse->getErrors());
        $this->assertCount(1, $restResponse->getResources());
        $this->assertSame(static::SUBSCRIPTION_KEY, $restResponse->getResources()[0]->getId());
    }

    public function testGivenTheDefaultConfigWhenSubscribingAgainThenItAnswersThatTheSubscriptionAlreadyExists(): void
    {
        // Arrange
        $this->mockAvailabilityNotificationClient($this->createErrorResponseTransfer(static::ERROR_MESSAGE_SUBSCRIPTION_ALREADY_EXISTS));

        // Act
        $restResponse = $this->subscribe();

        // Assert
        $this->assertCount(1, $restResponse->getErrors());
        $this->assertSame(AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_SUBSCRIPTION_ALREADY_EXISTS, $restResponse->getErrors()[0]->getCode());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $restResponse->getErrors()[0]->getStatus());
    }

    public function testGivenTheResponseWithoutKeyEnabledWhenSubscribingThenItIsAcceptedWithoutTheSubscriptionResource(): void
    {
        // Arrange
        $this->tester->mockConfigMethod(static::CONFIG_METHOD_SUBSCRIPTION_RESPONSE_WITHOUT_KEY, true);
        $this->mockAvailabilityNotificationClient($this->createNewSubscriptionResponseTransfer());

        // Act
        $restResponse = $this->subscribe();

        // Assert
        $this->assertSame(Response::HTTP_ACCEPTED, $restResponse->getStatus());
        $this->assertCount(0, $restResponse->getErrors());
        $this->assertCount(0, $restResponse->getResources());
    }

    public function testGivenTheResponseWithoutKeyEnabledWhenSubscribingAgainThenItAnswersLikeANewSubscription(): void
    {
        // Arrange
        $this->tester->mockConfigMethod(static::CONFIG_METHOD_SUBSCRIPTION_RESPONSE_WITHOUT_KEY, true);
        $this->mockAvailabilityNotificationClient($this->createErrorResponseTransfer(static::ERROR_MESSAGE_SUBSCRIPTION_ALREADY_EXISTS));

        // Act
        $restResponse = $this->subscribe();

        // Assert
        $this->assertSame(Response::HTTP_ACCEPTED, $restResponse->getStatus());
        $this->assertCount(0, $restResponse->getErrors());
        $this->assertCount(0, $restResponse->getResources());
    }

    public function testGivenTheResponseWithoutKeyEnabledWhenSubscribingToAProductThatDoesNotExistThenItStillAnswersWithTheError(): void
    {
        // Arrange
        $this->tester->mockConfigMethod(static::CONFIG_METHOD_SUBSCRIPTION_RESPONSE_WITHOUT_KEY, true);
        $this->mockAvailabilityNotificationClient($this->createErrorResponseTransfer(static::ERROR_MESSAGE_PRODUCT_NOT_FOUND));

        // Act
        $restResponse = $this->subscribe();

        // Assert
        $this->assertCount(1, $restResponse->getErrors());
        $this->assertSame(AvailabilityNotificationsRestApiConfig::RESPONSE_CODE_PRODUCT_NOT_FOUND, $restResponse->getErrors()[0]->getCode());
    }

    protected function subscribe(): RestResponseInterface
    {
        $storeClientMock = $this->createMock(AvailabilityNotificationsRestApiToStoreClientInterface::class);
        $storeClientMock->method('getCurrentStore')->willReturn((new StoreTransfer())->setName('DE'));
        $this->tester->setDependency(AvailabilityNotificationsRestApiDependencyProvider::CLIENT_STORE, $storeClientMock);
        $this->tester->mockFactoryMethod('getResourceBuilder', new RestResourceBuilder());

        return $this->tester->getFactory()
            ->createAvailabilityNotificationSubscriber()
            ->subscribe($this->createRestRequest());
    }

    protected function mockAvailabilityNotificationClient(AvailabilityNotificationSubscriptionResponseTransfer $responseTransfer): void
    {
        $availabilityNotificationClientMock = $this->createMock(AvailabilityNotificationsRestApiToAvailabilityNotificationClientInterface::class);
        $availabilityNotificationClientMock->method('subscribe')->willReturn($responseTransfer);

        $this->tester->setDependency(AvailabilityNotificationsRestApiDependencyProvider::CLIENT_AVAILABILITY_NOTIFICATION, $availabilityNotificationClientMock);
    }

    protected function createNewSubscriptionResponseTransfer(): AvailabilityNotificationSubscriptionResponseTransfer
    {
        return (new AvailabilityNotificationSubscriptionResponseTransfer())
            ->setIsSuccess(true)
            ->setAvailabilityNotificationSubscription(
                (new AvailabilityNotificationSubscriptionTransfer())
                    ->setSubscriptionKey(static::SUBSCRIPTION_KEY)
                    ->setSku(static::SKU)
                    ->setEmail(static::EMAIL),
            );
    }

    protected function createErrorResponseTransfer(string $errorMessage): AvailabilityNotificationSubscriptionResponseTransfer
    {
        return (new AvailabilityNotificationSubscriptionResponseTransfer())
            ->setIsSuccess(false)
            ->setErrorMessage($errorMessage);
    }

    protected function createRestRequest(): RestRequestInterface
    {
        $restResource = new RestResource(
            AvailabilityNotificationsRestApiConfig::RESOURCE_AVAILABILITY_NOTIFICATIONS,
            null,
            (new RestAvailabilityNotificationRequestAttributesTransfer())->setSku(static::SKU)->setEmail(static::EMAIL),
        );

        return new RestRequest(
            $restResource,
            Request::create('/availability-notifications', Request::METHOD_POST),
            new Metadata('json', 'json', Request::METHOD_POST, 'de_DE', false),
            [],
            [],
            null,
            [],
            [],
            [],
            [],
            false,
        );
    }
}
