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
use WHEP\MxDetector;
use WHEP\ProviderInterface;

/**
 * @uses   \WHEP\MxDetector
 * @covers \WHEP\MxDetector
 */
class MxDetectorTest extends TestCase
{
    public static function dataGetType(): array
    {
        $responses = require_once TESTS . 'resources' . DIRECTORY_SEPARATOR . 'mx_responses.php';

        return [
            // Null
            [
                'This is an unknown SMTP response',
                null,
            ],
            // Quota
            [
                $responses['gmail']['quota'],
                ProviderInterface::EVENT_BOUNCE_QUOTA,
            ],
            [
                $responses['laposte']['quota'],
                ProviderInterface::EVENT_BOUNCE_QUOTA,
            ],
            [
                $responses['orange']['quota'],
                ProviderInterface::EVENT_BOUNCE_QUOTA,
            ],

            // Inactive, disabled, unknown
            [
                $responses['gmail']['quota_inactive'],
                ProviderInterface::EVENT_BOUNCE_HARD,
            ],
            [
                $responses['orange']['invalid'],
                ProviderInterface::EVENT_BOUNCE_HARD,
            ],

            // Error
            [
                $responses['t-online']['reputation'],
                ProviderInterface::EVENT_ERROR,
            ],
        ];
    }

    /**
     * @dataProvider dataGetType
     */
    public function testGetType($smtp, $expected): void
    {
        $this->assertEquals($expected, MxDetector::getType($smtp));
    }
}
