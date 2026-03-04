<?php
/**
 * Created by PhpStorm.
 * Author : Richard Déloge, richarddeloge@gmail.com, https://teknoo.software
 * Date: 04/03/2026
 * Time: 10:27
 */

namespace Teknoo\East\Common\Contracts\Rendering;

use Psr\Http\Message\ServerRequestInterface;

interface LiveComponentBuilderInterface
{
    public function buildComponent(
        ServerRequestInterface $request,
        string $componentName,
        array $parameters,
        array $body,
    ): bool;
}