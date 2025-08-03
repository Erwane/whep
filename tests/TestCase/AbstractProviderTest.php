<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use WHEP\AbstractProvider;
use WHEP\Client;
use WHEP\Provider\Generic;
use WHEP\ProviderInterface;

#[CoversClass(AbstractProvider::class)]
#[Group('time-sensitive')]
class AbstractProviderTest extends TestCase
{
    public function testGetName(): void
    {
        $p = Client::getProvider('Generic');
        $this->assertEquals('generic', $p->getName());
    }

    public function testGetNotProcessed(): void
    {
        $p = Client::getProvider('Generic');

        $this->assertNull($p->getTime());
        $this->assertNull($p->getRecipient());
        $this->assertNull($p->getDetails());
        $this->assertNull($p->getSmtpResponse());
        $this->assertNull($p->getUrl());
        $this->assertNull($p->getRaw());
        $this->assertEquals('[]', $p->getRaw(true));
    }

    public function testProcess()
    {
        ClockMock::register(AbstractProvider::class);

        $expected = \DateTimeImmutable::createFromFormat('U.u e', microtime(true) . ' UTC', new \DateTimeZone('UTC'));

        /** @var \WHEP\Provider\Generic $p */
        $p = Client::getProvider('Generic');
        $p->process(['smtp' => '552: Over quota', 'email' => ' Recipient.Name@Example.COM ']);

        $this->assertEquals($expected, $p->getTime());
        $this->assertEquals(ProviderInterface::EVENT_BOUNCE_QUOTA, $p->getType());
        $this->assertSame('recipient.name@example.com', $p->getRecipient());
    }

    public function testCallbacks(): void
    {
        $mock = $this->createPartialMock(Generic::class, ['customCallback']);
        $config = [
            'callbacks' => [
                ProviderInterface::EVENT_BOUNCE_QUOTA => [$mock, 'customCallback'],
            ],
        ];
        $p = Client::getProvider('Generic', $config);

        $mock->expects($this->once())
            ->method('customCallback')
            ->with($p);

        $p->process(['smtp' => '552: Over quota'])
            ->callback();
    }

    public function testCallbackNotCalled(): void
    {
        $mock = $this->createPartialMock(Generic::class, ['customCallback']);
        $config = [
            'callbacks' => [
                ProviderInterface::EVENT_BOUNCE_HARD => [$mock, 'customCallback'],
            ],
        ];
        $p = Client::getProvider('Generic', $config);

        $mock->expects($this->never())
            ->method('customCallback');

        $p->process(['smtp' => '552: Over quota'])
            ->callback();
    }

    public function testDebugInfo(): void
    {
        ClockMock::register(AbstractProvider::class);
        $time = \DateTimeImmutable::createFromFormat('U.u e', microtime(true) . ' UTC', new \DateTimeZone('UTC'));

        $p = Client::getProvider('Generic');
        $data = ['smtp' => '552: Over quota'];
        $p->process($data);

        $result = $p->__debugInfo();

        $expected = [
            'type' => 'quota',
            'time' => $time->format(DATE_ATOM),
            'email' => null,
            'details' => null,
            'smtp' => '552: Over quota',
            'url' => null,
            'raw' => $data,
        ];
        $this->assertSame($expected, $result);
    }
}
