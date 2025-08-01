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
 * WHEP base client.
 */
class Client
{
    /**
     * Get emailing provider.
     *
     * @param string $providerName Provider name
     * @param array $config Provider config
     * @return \WHEP\ProviderInterface
     * @throws \WHEP\WebhookProviderException
     */
    public static function getProvider(string $providerName, array $config = []): ProviderInterface
    {
        $class = '\WHEP\Provider\\' . ucfirst(strtolower($providerName));

        $config += [
            'callbacks' => [],
        ];

        if (class_exists($class)) {
            /** @var \WHEP\AbstractProvider $provider */
            $provider = new $class($config);
        } else {
            throw new WebhookProviderException();
        }

        return $provider;
    }
}
