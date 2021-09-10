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

namespace Teknoo\East\Website\Contracts\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;

/**
 * Interface to define step to use into a HTTP EndPoint Recipe to render thanks to a template engine the form instance
 * from the workplan
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RenderFormInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        mixed $form,
        string $template,
        ObjectInterface $object,
        bool $isTranslatable = false
    ): RenderFormInterface;
}
