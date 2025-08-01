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

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use WHEP\AbstractProvider;
use WHEP\Client;
use WHEP\Provider\Generic;
use WHEP\ProviderInterface;

/**
 * @uses   \WHEP\AbstractProvider
 * @covers \WHEP\AbstractProvider
 * @group time-sensitive
 */
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

        $this->assertNull($p->getStatus());
        $this->assertNull($p->getTime());
        $this->assertNull($p->getEmail());
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

        $p = Client::getProvider('Generic');
        $p->process(['smtp' => '552: Over quota']);

        $this->assertEquals($expected, $p->getTime());
        $this->assertEquals(ProviderInterface::TYPE_QUOTA, $p->getType());
    }

    public function testCallbacks(): void
    {
        $mock = $this->createPartialMock(Generic::class, ['customCallback']);
        $config = [
            'callbacks' => [
                ProviderInterface::TYPE_QUOTA => [$mock, 'customCallback'],
            ],
        ];
        $p = Client::getProvider('Generic', $config);

        $mock->expects($this->once())
            ->method('customCallback')
            ->with($p);

        $p->process(['smtp' => '552: Over quota'])
            ->callbacks();
    }

    public function testCallbackNotCalled(): void
    {
        $mock = $this->createPartialMock(Generic::class, ['customCallback']);
        $config = [
            'callbacks' => [
                ProviderInterface::TYPE_HARD_FAIL => [$mock, 'customCallback'],
            ],
        ];
        $p = Client::getProvider('Generic', $config);

        $mock->expects($this->never())
            ->method('customCallback');

        $p->process(['smtp' => '552: Over quota'])
            ->callbacks();
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
            'status' => 500,
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
