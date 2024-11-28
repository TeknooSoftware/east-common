<?php

/*
 * East Common.
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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Minify\Js;

use MatthiasMullie\Minify\Minify;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\FrontAsset\FileInterface;
use Teknoo\East\Common\FrontAsset\FilesSet;
use Teknoo\East\Common\Minify\AbstractMinifier;
use Teknoo\East\Common\Minify\Js\Minifier;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AbstractMinifier::class)]
#[CoversClass(Minifier::class)]
class MinifierTest extends TestCase
{
    public function testProcess()
    {
        $libMinifier = $this->createMock(Minify::class);

        $minifier = new Minifier(
            $libMinifier,
        );

        self::assertInstanceOf(
            Minifier::class,
            $minifier->process(
                new FilesSet([
                    $this->createMock(FileInterface::class),
                    $this->createMock(FileInterface::class),
                ]),
                'foo.js',
                fn ($x) => $x,
                '/bar',
            ),
        );
    }
}
