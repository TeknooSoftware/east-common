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

namespace Teknoo\East\WebsiteBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Twig\Template\Engine;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Service\DatesService;
use Teknoo\East\WebsiteBundle\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\WebsiteBundle\Provider\UserProvider;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormHandling;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormProcessing;
use Teknoo\East\WebsiteBundle\Recipe\Step\RedirectClient;
use Teknoo\East\WebsiteBundle\Recipe\Step\RenderForm;

use function DI\create;
use function DI\decorate;
use function DI\get;

return [
    //Middleware
    EngineInterface::class => get(Engine::class),
    Engine::class => create()
        ->constructor(get('twig'))
];
