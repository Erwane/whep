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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WHEP\Exception\ProviderException;
use WHEP\Factory;
use WHEP\Provider\Generic;

#[CoversClass(Factory::class)]
class FactoryTest extends TestCase
{
    public function testUnknownProvider(): void
    {
        $this->expectException(ProviderException::class);

        Factory::provider('unknown');
    }

    public function testProvider(): void
    {
        $provider = Factory::provider('generic');

        $this->assertEquals(Generic::class, get_class($provider));
    }
}
