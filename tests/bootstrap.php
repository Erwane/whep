<?php
/**
 * This file is part of WHEP library
 *
 * @copyright   Copyright (c) Erwane BRETON
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
declare(strict_types=1);

use ResourceHelper\ResourceHelper;

const TESTS = __DIR__ . DIRECTORY_SEPARATOR;

setlocale(LC_ALL, 'C');

ResourceHelper::setBaseDir(__DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR);

const TEST_SIGNING_KEY = 'c2da3a65a3a5f47aade89e83fe65b615';
