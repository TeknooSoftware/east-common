<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

date_default_timezone_set('UTC');

error_reporting(E_ALL | E_STRICT);

ini_set('memory_limit', '32M');

include __DIR__.'/../vendor/autoload.php';

if (!\class_exists(\MongoGridFSFile::class)) {
    /*
     * To avoid error on test where mongodb lib is not installed
     */
    class MongoGridFSFile
    {
        public function getSize()
        {
        }
    }
}
