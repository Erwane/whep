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
     * @var array<array>
     */
    protected static $_searches = [
        // Quota
        [
            'needle' => ' out of storage space.',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_QUOTA,
        ],
        [
            'needle' => 'Recipient overquota',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_QUOTA,
        ],
        [
            'needle' => 'is over quota',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_QUOTA,
        ],
        [
            'needle' => ': Over quota',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_QUOTA,
        ],
        [
            'needle' => 'exceeded storage allocation',
            'method' => '_endsWith',
            'type' => ProviderInterface::EVENT_BOUNCE_QUOTA,
        ],
        [
            'needle' => 'user quota exceeded',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_QUOTA,
        ],

        // Hard
        [
            'needle' => ' out of storage space and inactive.',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_HARD,
        ],
        [
            'needle' => 'Invalid recipient.',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_BOUNCE_HARD,
        ],

        // Error
        [
            'needle' => 'None/bad reputation',
            'method' => '_contains',
            'type' => ProviderInterface::EVENT_ERROR,
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
             * @uses \WHEP\MxDetector::_startsWith()
             * @uses \WHEP\MxDetector::_endsWith()
             * @uses \WHEP\MxDetector::_contains()
             * @uses \WHEP\MxDetector::_regex()
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
    protected static function _startsWith(string $haystack, string $needle): bool
    {
        $haystack = mb_strtolower($haystack);
        $needle = mb_strtolower($needle);

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
    protected static function _endsWith(string $haystack, string $needle): bool
    {
        if ($haystack === '') {
            return false;
        }

        $haystack = mb_strtolower($haystack);
        $needle = mb_strtolower($needle);

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
    protected static function _contains(string $haystack, string $needle): bool
    {
        $haystack = mb_strtolower($haystack);
        $needle = mb_strtolower($needle);

        return $needle === '' || strpos($haystack, $needle) !== false;
    }

    /**
     * Haystack match needle regex pattern.
     *
     * @param string $haystack Haystack
     * @param string $pattern Regex pattern
     * @return bool
     */
    protected static function _regex(string $haystack, string $pattern): bool
    {
        return preg_match($pattern, $haystack);
    }
}
