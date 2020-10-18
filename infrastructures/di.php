<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Infrastructures;

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
