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

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use WHEP\AbstractProvider;
use WHEP\Exception\IpException;
use WHEP\Exception\SecurityException;
use WHEP\Factory;
use WHEP\Provider\Generic;
use WHEP\ProviderInterface;

#[CoversClass(AbstractProvider::class)]
#[Group('time-sensitive')]
class AbstractProviderTest extends TestCase
{
    public function testGetName(): void
    {
        $p = Factory::provider('Generic');
        $this->assertEquals('generic', $p->getName());
    }

    public function testTimeDifferentLocale(): void
    {
        setlocale(LC_NUMERIC, 'fr_FR.UTF-8');

        $p = Factory::provider('Generic', ['check_ip' => false])
            ->process([]);

        try {
            $this->assertInstanceOf(DateTimeImmutable::class, $p->getTime());
        } finally {
            setlocale(LC_NUMERIC, 'C');
        }
    }

    public static function dataAddInvalidIpOrNetwork(): array
    {
        return [
            ['10.1.1.'],
            ['fg80::1'],
            ['10.1.1.1/33'],
            ['fg80::1/ab'],
        ];
    }

    /** @dataProvider dataAddInvalidIpOrNetwork */
    public function testAddInvalidIpOrNetwork($input): void
    {
        $this->expectException(IpException::class);
        $this->expectExceptionMessage('Invalid ip or network.');

        $p = Factory::provider('Generic');

        $p->addAllowedIpOrNetwork($input);
    }

    public static function dataAddAllowedIpOrNetwork(): array
    {
        return [
            ['10.1.1.1', ['192.168.0.0/24', '10.1.1.1']],
            ['10.1.1.0/16', ['192.168.0.0/24', '10.1.0.0/16']],
            ['10.1.1.0', ['192.168.0.0/24', '10.1.1.0']],
            ['fe80::1', ['192.168.0.0/24', 'fe80::1']],
            ['fe80::abcd:10/126', ['192.168.0.0/24', 'fe80::abcd:10/126']],
            [['10.1.1.1', 'fe80::1', '192.168.0.1/24'], ['192.168.0.0/24', '10.1.1.1', 'fe80::1']],
        ];
    }

    /** @dataProvider dataAddAllowedIpOrNetwork */
    public function testAddAllowedIpOrNetwork($input, $expected): void
    {
        $p = Factory::provider('Generic');
        $p->addAllowedIpOrNetwork($input);

        $info = $p->__debugInfo();
        $this->assertEquals($expected, $info['allowed_ip']);
    }

    public function testSetAllowedIpOrNetwork(): void
    {
        $p = Factory::provider('Generic');
        $p->setAllowedIpOrNetwork(['10.0.0.0/24']);

        $info = $p->__debugInfo();
        $this->assertEquals(['10.0.0.0/24'], $info['allowed_ip']);
    }

    public function testCheckRemoteIpNotSet(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Remote IP not set. Pass `remote_ip` to `Factory::provider()`.');
        $p = Factory::provider('generic');
        $p->process([]);
    }

    public function testCheckRemoteNotInNetwork(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Remote IP "10.0.0.1" is not in allowed list.');
        $p = Factory::provider('generic', ['remote_ip' => '10.0.0.1']);
        $p->process([]);
    }

    public function testGetNotProcessed(): void
    {
        $p = Factory::provider('Generic');

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
        /** @var \WHEP\Provider\Generic $p */
        $p = Factory::provider('Generic', ['remote_ip' => '192.168.0.10']);
        $p->process(['smtp' => '552: Over quota', 'email' => ' Recipient.Name@Example.COM ']);

        $this->assertInstanceOf(DateTimeImmutable::class, $p->getTime());
        $this->assertEquals(ProviderInterface::EVENT_BOUNCE_QUOTA, $p->getType());
        $this->assertSame('recipient.name@example.com', $p->getRecipient());
        $this->assertTrue($p->__debugInfo()['security_checked']);
    }

    public function testLoadNoType()
    {
        $p = Factory::provider('Generic', ['check_ip' => false]);
        $p->process([]);
        $this->assertEquals(ProviderInterface::EVENT_ERROR, $p->getType());
    }

    public function testCallbacks(): void
    {
        $mock = $this->createPartialMock(Generic::class, ['customCallback']);
        $config = [
            'check_ip' => false,
            'callbacks' => [
                ProviderInterface::EVENT_BOUNCE_QUOTA => [$mock, 'customCallback'],
            ],
        ];
        $p = Factory::provider('Generic', $config);

        $mock
            ->expects($this->once())
            ->method('customCallback')
            ->with($p);

        $p
            ->process(['smtp' => '552: Over quota'])
            ->callback();
    }

    public function testCallbackNotCalled(): void
    {
        $mock = $this->createPartialMock(Generic::class, ['customCallback']);
        $config = [
            'check_ip' => false,
            'callbacks' => [
                ProviderInterface::EVENT_BOUNCE_HARD => [$mock, 'customCallback'],
            ],
        ];
        $p = Factory::provider('Generic', $config);

        $mock
            ->expects($this->never())
            ->method('customCallback');

        $p
            ->process(['smtp' => '552: Over quota'])
            ->callback();
    }

    public function testDebugInfo(): void
    {
        $time = DateTimeImmutable::createFromFormat('U.u e', microtime(true) . ' UTC', new DateTimeZone('UTC'));

        $p = Factory::provider('Generic', ['check_ip' => false]);
        $data = ['smtp' => '552: Over quota'];
        $p->process($data);

        $result = $p->__debugInfo();

        $expected = [
            'name' => 'generic',
            'remote_ip' => null,
            'security_checked' => false,
            'type' => 'quota',
            'time' => $time->format(DATE_ATOM),
            'recipient' => null,
            'details' => null,
            'smtp' => '552: Over quota',
            'url' => null,
            'raw' => $data,
            'allowed_ip' => ['192.168.0.0/24'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testClientIpEmmitDeprecation(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new Exception($errstr, $errno);
        }, E_USER_DEPRECATED);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Since WHEP 3.0: `client_ip` is deprecated, use `remote_ip` instead.');

        Factory::provider('Generic', ['client_ip' => '192.168.0.1']);
    }

    public function testClientIpIsConvertedToRemoteIp(): void
    {
        $p = Factory::provider('Generic', ['client_ip' => '192.168.0.1']);
        $p->process(['smtp' => '552: Over quota', 'email' => ' Recipient.Name@Example.COM ']);

        $this->assertEquals('recipient.name@example.com', $p->getRecipient());
    }
}
