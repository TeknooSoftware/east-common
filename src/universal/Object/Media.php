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
     * @var \MongoGridFSFile
     */
    private $file;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     * */
    private $chunkSize;

    /** 
     * @var string
     */
    private $md5;

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

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
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
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     * @return self
     */
    public function setChunkSize(int $chunkSize): Media
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getMd5(): string
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     * @return self
     */
    public function setMd5(string $md5): Media
    {
        $this->md5 = $md5;

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