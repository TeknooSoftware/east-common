<?php

declare(strict_types=1);

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

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait AdminEndPointTrait
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var string
     */
    private $viewPath;

    /**
     * @param string $viewPath
     * @return self
     */
    public function setViewPath(string $viewPath): self
    {
        $this->viewPath = $viewPath;

        return $this;
    }

    /**
     * @param LoaderInterface $loader
     * @return self
     */
    public function setLoader(LoaderInterface $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * @param WriterInterface $writer
     * @return self
     */
    public function setWriter(WriterInterface $writer): self
    {
        $this->writer = $writer;

        return $this;
    }
}
