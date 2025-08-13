<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Contracts\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;

/**
 * Interface to define step to use into a HTTP EndPoint Recipe to render thanks to a template engine the form instance
 * from the workplan
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface RenderFormInterface
{
    /**
     * @param array<string, mixed> $viewParameters
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        mixed $form,
        string $template,
        ObjectInterface $object,
        bool $isTranslatable = false,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
        bool|string|null $objectSaved = null,
        ?string $api = null,
        #[Transform(transformer: 'boolval')]
        ?bool $cleanHtml = null,
    ): RenderFormInterface;
}
