<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace WHEP\Test\TestCase;

use PHPUnit\Framework\TestCase;
use WHEP\Client;
use WHEP\Provider\Generic;
use WHEP\WebhookProviderException;

/**
 * @uses   \WHEP\Client
 * @covers \WHEP\Client
 */
class ClientTest extends TestCase
{
    public function testGetUnknownProvider(): void
    {
        $this->expectException(WebhookProviderException::class);

        Client::getProvider('unknown');
    }

    public function testGetProvider(): void
    {
        $provider = Client::getProvider('generic');

        $this->assertEquals(Generic::class, get_class($provider));
    }
}
