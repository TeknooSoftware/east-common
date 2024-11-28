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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Infrastructures;

use Laminas\Diactoros\ResponseFactory as DiactorosResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\Http\Message\CallbackStreamFactoryInterface;

use function DI\get;

return [
    ResponseFactoryInterface::class => get(DiactorosResponseFactory::class),
    StreamFactoryInterface::class => get(StreamFactory::class),
    CallbackStreamFactoryInterface::class => get(CallbackStreamFactory::class),
];
