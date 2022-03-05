<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Middleware;

use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\View\ParametersBag;

/**
 * Middleware injected into the main East Foundation's recipe, as middleware, to inject into the view parameter bag
 * the instance of the menu generator service, to be used into the template engine to show menus.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MenuMiddleware
{
    final public const MIDDLEWARE_PRIORITY = 7;

    public function __construct(
        private MenuGenerator $menuGenerator,
    ) {
    }

    public function execute(
        ParametersBag $bag,
    ): self {
        $bag->set('menuGenerator', $this->menuGenerator);

        return $this;
    }
}
