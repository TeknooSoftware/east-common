<?php

declare(strict_types=1);

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
date_default_timezone_set('UTC');

error_reporting(E_ALL);

include __DIR__ . '/fakeQuery.php';
include __DIR__ . '/fakeUOW.php';
include __DIR__ . '/fakeRuntimeException.php';
include __DIR__ . '/fakeObjectId.php';
include __DIR__ . '/fakeLegacyPasswordAuthenticatedUserInterface.php';
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
