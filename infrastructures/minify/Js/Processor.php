<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\MMMinify\Js;

use Teknoo\East\Common\Contracts\Minify\FilesSetInterface;
use Teknoo\East\Common\Contracts\Minify\ProcessorInterface;
use Teknoo\East\Common\Minify\File;
use Teknoo\East\Common\Minify\FileType;
use Teknoo\East\Common\MMMinify\AbstractMinifier;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Processor extends AbstractMinifier
{
    public function minify(FilesSetInterface $set, string $fileName, callable $holder): ProcessorInterface
    {
        $this->process(
            set: $set,
            holder: $holder,
            type: FileType::JS,
            path: $fileName,
        );

        return $this;
    }
}
