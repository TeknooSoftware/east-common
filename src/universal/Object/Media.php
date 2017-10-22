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

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Media implements DeletableInterface
{
    use ObjectTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \MongoGridFSFile
     */
    private $file;

    /**
     * @var int
     */
    private $length;

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
        return (string) $this->name;
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
     * @return \MongoGridFSFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param \MongoGridFSFile|mixed $file
     * @return self
     */
    public function setFile($file): Media
    {
        $this->file = $file;

        if ($this->file instanceof \MongoGridFSFile) {
            $this->length = $this->file->getSize();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return (int) $this->length;
    }

    /**
     * @param int $length
     * @return self
     */
    public function setLength(int $length): Media
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return (string) $this->mimeType;
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
        return (string) $this->alternative;
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
    public function getResource()
    {
        if (\is_callable([$this->file, 'getResource'])) {
            $resource = $this->file->getResource();

            if (\is_resource($resource)) {
                return $resource;
            }
        }

        throw new \RuntimeException('Any resource are available for this media');
    }
}
