<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Object;

use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

class MediaMetadata implements ImmutableInterface
{
    use ImmutableTrait;

    private ?string $contentType = '';

    private ?string $fileName = '';

    private ?string $alternative = '';

    public function __construct(string $contentType, string $fileName = '', string $alternative = '')
    {
        $this->uniqueConstructorCheck();

        $this->contentType = $contentType;
        $this->fileName = $fileName;
        $this->alternative = $alternative;
    }

    public function getContentType(): string
    {
        return (string) $this->contentType;
    }

    public function getFileName(): string
    {
        return (string) $this->fileName;
    }

    public function getAlternative(): string
    {
        return (string) $this->alternative;
    }
}
