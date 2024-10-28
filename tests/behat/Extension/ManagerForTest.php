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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Behat\Extension;

use Teknoo\East\Foundation\Extension\LoaderInterface;
use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ManagerForTest extends Manager
{
    private bool $enabled = false;

    public function __construct(?LoaderInterface $loader = null)
    {
        parent::__construct($loader);

        parent::$instance = $this;
    }

    public function execute(ModuleInterface $module): ManagerInterface
    {
        if ($this->enabled) {
            return parent::execute($module);
        }

        return $this;
    }

    public function listLoadedExtensions(): iterable
    {
        if ($this->enabled) {
            return parent::listLoadedExtensions();
        }

        return [];
    }

    public function enabling(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disabling(): self
    {
        $this->enabled = false;

        return $this;
    }
}