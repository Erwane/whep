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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WHEP\MxDetector;

#[CoversClass(MxDetector::class)]
class MxDetectorTest extends TestCase
{
    public static function dataGetType(): array
    {
        $responses = require_once TESTS . 'resources' . DIRECTORY_SEPARATOR . 'mx_responses.php';

        $data = [
            // Null
            [
                'unknown',
                'This is an unknown SMTP response',
                null,
            ],
        ];

        foreach ($responses as $type => $items) {
            foreach ($items as $name => $response) {
                $data[] = [
                    $name,
                    $response,
                    $type,
                ];
            }
        }

        return $data;
    }

    #[DataProvider('dataGetType')]
    public function testGetType($name, $smtp, $expected): void
    {
        $this->assertEquals(
            $expected,
            MxDetector::getType($smtp),
            sprintf('Type detection failed. type=%s; name=%s', $expected, $name),
        );
    }
}
