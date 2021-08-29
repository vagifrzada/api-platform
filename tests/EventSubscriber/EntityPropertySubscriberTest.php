<?php

namespace App\Tests\EventSubscriber;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use App\EventSubscriber\EntityPropertySubscriber;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntityPropertySubscriberTest extends TestCase
{
    public function testConfiguration(): void
    {
        $events = EntityPropertySubscriber::getSubscribedEvents();
        $this->assertIsArray($events);
        $this->assertArrayHasKey(KernelEvents::VIEW, $events);
        $this->assertEquals(
            ['applyPropertyValue', EventPriorities::PRE_WRITE],
            $events[KernelEvents::VIEW]
        );
    }

    /**
     * @dataProvider providerSettingEntityProperty
     */
    public function testSettingEntityProperty(
        string $method,
        string $entity,
        int $getUserCall,
        int $setAuthorCall,
        int $setCreatedCall,
    ): void
    {
        $subscriber = new EntityPropertySubscriber($this->getTokenStorageMock($getUserCall));
        $subscriber->applyPropertyValue(
            $this->getEvent($this->getRequestMock($method),
                $this->getEntityMock($entity, $setAuthorCall, $setCreatedCall)
            )
        );
    }

    public function testNoTokenPresentInTokenStorage(): void
    {
        $subscriber = new EntityPropertySubscriber($this->getTokenStorageMock(1, false));
        $subscriber->applyPropertyValue(
            $this->getEvent($this->getRequestMock(Request::METHOD_POST),
                $this->getEntityMock(Post::class, 0, 1)
            )
        );
    }

    public function providerSettingEntityProperty(): array
    {
        return [
            [Request::METHOD_POST, Post::class, 1, 1, 1],
            [Request::METHOD_GET, Post::class, 0, 0, 0],
            [Request::METHOD_POST, User::class, 0, 0, 1],
            [Request::METHOD_POST, Comment::class, 1, 1, 1],
            [Request::METHOD_GET, Comment::class, 0, 0, 0],
        ];
    }

    private function getEvent(Request $requestMock, object $entity): ViewEvent
    {
        return new ViewEvent(
            $this->getMockBuilder(HttpKernelInterface::class)->getMockForAbstractClass(),
            $requestMock,
            HttpKernelInterface::MAIN_REQUEST,
            $entity,
        );
    }

    private function getEntityMock(string $entity, int $setAuthorCall, $setCreatedCall): object
    {
        $entityMock = $this->getMockBuilder($entity)->getMock();

        if ($entity !== User::class) {
            $entityMock->expects($this->exactly($setAuthorCall))
                ->method('setAuthor')
                ->withAnyParameters();
        }

        $entityMock->expects($this->exactly($setCreatedCall))
            ->method('setCreatedAt')
            ->withAnyParameters();

        return $entityMock;
    }

    private function getTokenStorageMock(int $expected = 1, bool $hasToken = true): TokenStorageInterface
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();

        $tokenMock->expects($hasToken ? $this->exactly($expected) : $this->never())
            ->method('getUser')
            ->willReturn(new User());

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();

        $tokenStorageMock->expects($this->exactly($expected))
            ->method('getToken')
            ->willReturn($hasToken ? $tokenMock : null);

        return $tokenStorageMock;
    }

    private function getRequestMock(string $method): Request
    {
        $requestMock = $this->getMockBuilder(Request::class)->getMock();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        return $requestMock;
    }
}