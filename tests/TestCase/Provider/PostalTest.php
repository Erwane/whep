<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace TestCase\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ResourceHelper\File;
use WHEP\Exception\SecurityException;
use WHEP\Factory;
use WHEP\Provider\Postal;
use WHEP\ProviderInterface;

#[CoversClass(Postal::class)]
class PostalTest extends TestCase
{
    public function testCheckSecuritySuccess(): void
    {
        $p = Factory::provider('postal', [
            'remote_ip' => '192.168.0.1',
            'allowed_ip' => ['192.168.0.1'],
        ]);
        $p->checkSecurity([]);
        $this->assertTrue($p->securityChecked());
    }

    public static function dataTypesMap(): array
    {
        return [
            [
                ['event' => 'SoftFail'],
                ProviderInterface::EVENT_BOUNCE_SOFT,
            ],
            [
                ['event' => 'HardFail'],
                ProviderInterface::EVENT_BOUNCE_HARD,
            ],
            [
                ['event' => 'MessageBounced'],
                ProviderInterface::EVENT_BOUNCE_HARD,
            ],
            [
                ['event' => 'Held'],
                ProviderInterface::EVENT_BLOCKED,
            ],
            [
                ['event' => 'Sent'],
                ProviderInterface::EVENT_SENT,
            ],
            [
                ['event' => 'MessageLoaded'],
                ProviderInterface::EVENT_OPENED,
            ],
            [
                ['event' => 'MessageLinkClicked'],
                ProviderInterface::EVENT_CLICK,
            ],
            [
                ['event' => 'DomainDNSError'],
                ProviderInterface::EVENT_ERROR,
            ],
        ];
    }

    #[DataProvider('dataTypesMap')]
    public function testTypesMap($data, $expected): void
    {
        $p = Factory::provider('postal', [
            'remote_ip' => '192.168.0.1',
            'allowed_ip' => ['192.168.0.1'],
        ]);

        $p->process($data);
        $this->assertEquals($expected, $p->getType());
    }

    public function testLoadNoData(): void
    {
        $p = Factory::provider('postal', ['check_ip' => false]);
        $p->process([]);

        $this->assertEquals(ProviderInterface::EVENT_ERROR, $p->getType());
        $this->assertEquals([], $p->getRaw());
        $this->assertNull($p->getRecipient());
    }

    public static function dataLoad(): array
    {
        return [
            [
                'bounced_undeliverable.json',
                ProviderInterface::EVENT_BOUNCE_HARD,
                'Undeliverable: My Newsletter',
                null,
                'recipient@example.com',
                null,
            ],
            [
                'bounced_undeliverable_fr.json',
                ProviderInterface::EVENT_BOUNCE_HARD,
                'Non remis : My Newsletter',
                null,
                'recipient@example.com',
                null,
            ],
            [
                'bounced_undelivered.json',
                ProviderInterface::EVENT_BOUNCE_HARD,
                'Undelivered Mail Returned to Sender',
                null,
                'recipient@example.com',
                null,
            ],
            [
                'delayed_bad_reputation.json',
                ProviderInterface::EVENT_ERROR,
                'No SMTP servers were available.',
                '554 IP=1.2.3.4 - None/bad reputation.',
                'recipient.name@example.com',
                null,
            ],
            [
                'delayed_defer_busy.json',
                ProviderInterface::EVENT_BOUNCE_SOFT,
                'No SMTP servers were available.',
                '451 DEFER - D: We are busy;',
                'recipient@example.com',
                null,
            ],
            [
                'delayed_quota.json',
                ProviderInterface::EVENT_BOUNCE_QUOTA,
                'Temporary SMTP delivery error when sending to 1.2.3.4:25 (gmail-smtp-in.l.google.com)',
                "452-4.2.2 The recipient's inbox is out of storage space. Please direct the",
                'recipient@example.com',
                null,
            ],
            [
                'delivery_failed.json',
                ProviderInterface::EVENT_BOUNCE_HARD,
                'Permanent SMTP delivery error when sending to 1.2.3.4:25 (mx.com)',
                '550 5.4.1 Recipient address rejected: Access denied.',
                'recipient@example.com',
                null,
            ],
            [
                'dns_error.json',
                ProviderInterface::EVENT_ERROR,
                'The DKIM record at example.com does not match the record',
                null,
                null,
                null,
            ],
            [
                'held.json',
                ProviderInterface::EVENT_BLOCKED,
                'Recipient (recipient@example.com) is on the suppression list',
                '',
                'recipient@example.com',
                null,
            ],
            [
                'link_clicked.json',
                ProviderInterface::EVENT_CLICK,
                null,
                null,
                'recipient@example.com',
                'https://company.com/landing_page',
            ],
            [
                'message_loaded.json',
                ProviderInterface::EVENT_OPENED,
                null,
                null,
                'test@example.com',
                null,
            ],
            [
                'sent.json',
                ProviderInterface::EVENT_SENT,
                'Message sent by SMTP to aspmx.l.google.com',
                '250 2.0.0 OK 1477944899 ly2si31746747wjb.95 - gsmtp',
                'test@example.com',
                null,
            ],
        ];
    }

    #[DataProvider('dataLoad')]
    public function testLoad($resource, $type, $details, $smtp, $email, $url): void
    {
        $json = File::getContent('postal/' . $resource);
        $data = json_decode($json, true);

        $p = Factory::provider('postal', ['allowed_ip' => ['192.168.0.1'], 'remote_ip' => '192.168.0.1']);
        $p->process($data);

        $this->assertEquals($type, $p->getType());
        $this->assertEquals($details, $p->getDetails());
        $this->assertEquals($smtp, $p->getSmtpResponse());
        $this->assertEquals($email, $p->getRecipient());
        $this->assertEquals($url, $p->getUrl());
    }
}
