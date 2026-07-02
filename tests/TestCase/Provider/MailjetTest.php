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
use WHEP\Provider\Mailjet;
use WHEP\ProviderInterface;

#[CoversClass(Mailjet::class)]
class MailjetTest extends TestCase
{
    public function testCheckIp(): void
    {
        $p = Factory::provider('mailjet', ['remote_ip' => '185.211.120.0']);
        $p->process([]);
        $this->assertTrue($p->securityChecked());
    }

    public static function dataTypesMap(): array
    {
        return [
            [
                'sent',
                ProviderInterface::EVENT_SENT,
            ],
            [
                'blocked',
                ProviderInterface::EVENT_BLOCKED,
            ],
            [
                'bounce',
                ProviderInterface::EVENT_BOUNCE_SOFT,
            ],
            [
                'open',
                ProviderInterface::EVENT_OPENED,
            ],
            [
                'click',
                ProviderInterface::EVENT_CLICK,
            ],
            [
                'unsub',
                ProviderInterface::EVENT_UNSUB,
            ],
            [
                'spam',
                ProviderInterface::EVENT_ABUSE,
            ],
        ];
    }

    /** @dataProvider dataTypesMap */
    public function testTypesMap($event, $expected): void
    {
        $p = Factory::provider('mailjet', ['check_ip' => false]);
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
                'preblocked',
                null,
                null,
            ],
            [
                'bounce_hard.json',
                ProviderInterface::EVENT_BOUNCE_HARD,
                'recipient@example.com',
                '',
                null,
                null,
            ],
            [
                'bounce_soft.json',
                ProviderInterface::EVENT_BOUNCE_SOFT,
                'recipient@example.com',
                '',
                null,
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
                'open.json',
                ProviderInterface::EVENT_OPENED,
                'recipient@example.com',
                null,
                null,
                null,
            ],
            [
                'sent.json',
                ProviderInterface::EVENT_SENT,
                'recipient@example.com',
                null,
                '250 2.0.0 mail accepted for delivery',
                null,
            ],
        ];
    }

    /** @dataProvider dataLoad */
    public function testLoad($resource, $type, $recipient, $details, $smtp, $url): void
    {
        $json = File::getContent('mailjet/' . $resource);
        $data = json_decode($json, true);

        $p = Factory::provider('mailjet', ['check_ip' => false]);
        $p->process($data);

        $this->assertEquals($type, $p->getType());
        $this->assertEquals($recipient, $p->getRecipient());
        $this->assertEquals($details, $p->getDetails());
        $this->assertEquals($smtp, $p->getSmtpResponse());
        $this->assertEquals($url, $p->getUrl());
    }
}
