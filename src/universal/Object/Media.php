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

namespace Teknoo\East\Website\Object;

class Media
{
    use PublishableTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var resource
     */
    private $raw;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string
     */
    private $alternative;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): Media
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return self
     */
    public function setSize(int $size): Media
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return self
     */
    public function setMimeType(string $mimeType): Media
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternative(): string
    {
        return $this->alternative;
    }

    /**
     * @param string $alternative
     * @return self
     */
    public function setAlternative(string $alternative): Media
    {
        $this->alternative = $alternative;

        return $this;
    }

    /**
     * @return resource
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param resource $raw
     * @return self
     */
    public function setRaw($raw): Media
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * @param \DateTime $publishedAt
     * @return Media
     */
    public function setPublishedAt(\DateTime $publishedAt): Media
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}