<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

namespace WHEP;

/**
 * MxDetector propose utilities to understand SMTP responses from MX servers.
 */
class MxDetector
{
    /**
     * SMTP responses.
     * smtpResponse array should be of this model:
     *   `needle` => The sentence to search with `method`
     *   `method` => Used method to match (_startsWith, _endsWith, _contains, _regex)
     *   `type` => ProviderInterface::TYPE_???,
     *
     * @var array[]
     */
    protected static $_searches = [
        // Gmail
        [
            'needle' => "552-5.2.2 The recipient's inbox is out of storage space and inactive",
            'method' => '_startsWith',
            'type' => ProviderInterface::TYPE_HARD_FAIL,
        ],
        [
            'needle' => "452-4.2.2 The recipient's inbox is out of storage space",
            'method' => '_startsWith',
            'type' => ProviderInterface::TYPE_QUOTA,
        ],
        // Orange
        [
            'needle' => 'Invalid recipient. OFR_416',
            'method' => '_contains',
            'type' => ProviderInterface::TYPE_HARD_FAIL,
        ],
        [
            'needle' => 'Recipient overquota. OFR_417',
            'method' => '_contains',
            'type' => ProviderInterface::TYPE_QUOTA,
        ],
        // LaPoste
        [
            'needle' => ': Over quota',
            'method' => '_endsWith',
            'type' => ProviderInterface::TYPE_QUOTA,
        ],
    ];

    /**
     * Get event type from SMTP response.
     *
     * @param string $smtp Smtp response
     * @return string|null
     */
    public static function getType(string $smtp): ?string
    {
        foreach (self::$_searches as $search) {
            /**
             * @uses self::_startsWith()
             * @uses self::_endsWith()
             * @uses self::_contains()
             * @uses self::_regex()
             */
            if (self::{$search['method']}($smtp, $search['needle'])) {
                return $search['type'];
            }
        }

        return null;
    }

    /**
     * Haystack starts with needle.
     *
     * @param string $haystack Haystack
     * @param string $needle Needle
     * @return bool
     */
    protected function _startsWith(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * Haystack ends with needle.
     *
     * @param string $haystack Haystack
     * @param string $needle Needle
     * @return bool
     * @see https://github.com/symfony/polyfill-php80/blob/1.x/Php80.php
     */
    protected function _endsWith(string $haystack, string $needle): bool
    {
        if ($haystack === '') {
            return false;
        }

        if ($needle === '' || $needle === $haystack) {
            return true;
        }

        $needleLength = strlen($needle);

        return $needleLength <= strlen($haystack) && substr_compare($haystack, $needle, -$needleLength) === 0;
    }

    /**
     * Haystack contains needle.
     *
     * @param string $haystack Haystack
     * @param string $needle Needle
     * @return bool
     */
    protected function _contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }

    /**
     * Haystack match needle regex pattern.
     *
     * @param string $haystack Haystack
     * @param string $pattern Regex pattern
     * @return bool
     */
    protected function _regex(string $haystack, string $pattern): bool
    {
        return preg_match($pattern, $haystack);
    }
}
