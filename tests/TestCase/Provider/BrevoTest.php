<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace WHEP\Test\TestCase\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ResourceHelper\File;
use WHEP\Factory;
use WHEP\Provider\Brevo;
use WHEP\ProviderInterface;

#[CoversClass(Brevo::class)]
class BrevoTest extends TestCase
{
    public function testCheckIp(): void
    {
        $p = Factory::provider('brevo', ['client_ip' => '1.179.121.81']);
        $p->process([]);
        $this->assertTrue($p->securityChecked());
    }

    public static function dataTypesMap(): array
    {
        return [
            [
                'request',
                ProviderInterface::EVENT_REQUEST,
            ],
            [
                'delivered',
                ProviderInterface::EVENT_SENT,
            ],
            [
                'unique_opened',
                ProviderInterface::EVENT_OPENED,
            ],
            [
                'opened',
                ProviderInterface::EVENT_OPENED,
            ],
            [
                'proxy_open',
                ProviderInterface::EVENT_OPENED,
            ],
            [
                'unique_proxy_open',
                ProviderInterface::EVENT_OPENED,
            ],
            [
                'click',
                ProviderInterface::EVENT_CLICK,
            ],
            [
                'soft_bounce',
                ProviderInterface::EVENT_BOUNCE_SOFT,
            ],
            [
                'hard_bounce',
                ProviderInterface::EVENT_BOUNCE_HARD,
            ],
            [
                'invalid_email',
                ProviderInterface::EVENT_ERROR,
            ],
            [
                'error',
                ProviderInterface::EVENT_ERROR,
            ],
            [
                'deferred',
                ProviderInterface::EVENT_DEFERRED,
            ],
            [
                'spam',
                ProviderInterface::EVENT_ABUSE,
            ],
            [
                'unsubscribed',
                ProviderInterface::EVENT_UNSUB,
            ],
            [
                'blocked',
                ProviderInterface::EVENT_BLOCKED,
            ],
        ];
    }

    /** @dataProvider dataTypesMap */
    public function testTypesMap($event, $expected): void
    {
        $p = Factory::provider('brevo', ['check_ip' => false]);
        $p->process(['event' => $event]);
        $this->assertEquals($expected, $p->getType());
    }

    public static function dataLoad(): array
    {
        return [
            [
                'blocked.json',
                ProviderInterface::EVENT_BLOCKED,
                'recipient@example.com',
                'blocked : due to blacklist user',
                null,
                null,
            ],
            [
                'bounce_hard.json',
                ProviderInterface::EVENT_BOUNCE_HARD,
                'recipient@example.com',
                null,
                '550 5.1.1 Invalid recipient',
                null,
            ],
            [
                'bounce_soft.json',
                ProviderInterface::EVENT_BOUNCE_SOFT,
                'recipient@example.com',
                null,
                '552 5.1.1 OFR_417',
                null,
            ],
            [
                'click.json',
                ProviderInterface::EVENT_CLICK,
                'recipient@example.com',
                null,
                null,
                'https://company.com/landing_page',
            ],
            [
                'delivered.json',
                ProviderInterface::EVENT_SENT,
                'recipient@example.com',
                'sent',
                null,
                null,
            ],
            [
                'opened.json',
                ProviderInterface::EVENT_OPENED,
                'recipient@example.com',
                null,
                null,
                null,
            ],
            [
                'request.json',
                ProviderInterface::EVENT_REQUEST,
                'recipient@example.com',
                'sent',
                null,
                null,
            ],
            [
                'unique_opened.json',
                ProviderInterface::EVENT_OPENED,
                'recipient@example.com',
                null,
                null,
                null,
            ],
            [
                'unique_proxy_open.json',
                ProviderInterface::EVENT_OPENED,
                'recipient@example.com',
                null,
                null,
                null,
            ],
        ];
    }

    /** @dataProvider dataLoad */
    public function testLoad($resource, $type, $recipient, $details, $smtp, $url): void
    {
        $json = File::getContent('brevo/' . $resource);
        $data = json_decode($json, true);

        $p = Factory::provider('brevo', ['check_ip' => false]);
        $p->process($data);

        $this->assertEquals($type, $p->getType());
        $this->assertEquals($recipient, $p->getRecipient());
        $this->assertEquals($details, $p->getDetails());
        $this->assertEquals($smtp, $p->getSmtpResponse());
        $this->assertEquals($url, $p->getUrl());
    }
}
