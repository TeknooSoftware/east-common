<?php

/*
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Resources\config;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\CommonBundle\Recipe\Step\FormHandling;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;
use Teknoo\East\CommonBundle\Recipe\Step\RedirectClient;
use Teknoo\East\CommonBundle\Recipe\Step\RenderForm;
use Teknoo\East\CommonBundle\Recipe\Step\SearchFormLoader;

use function DI\create;
use function DI\get;

return [
    SearchFormLoaderInterface::class => get(SearchFormLoader::class),

    FormHandlingInterface::class => get(FormHandling::class),

    FormProcessingInterface::class => get(FormProcessing::class),
    FormProcessing::class => create(),

    RedirectClientInterface::class => get(RedirectClient::class),

    RenderFormInterface::class => get(RenderForm::class),
    RenderForm::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),
];
