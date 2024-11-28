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

namespace Teknoo\East\Common\Recipe\Cookbook;

use Teknoo\East\Common\Contracts\Recipe\Cookbook\MinifierEndPointInterface;
use Teknoo\East\Common\Recipe\Plan\MinifierEndPoint as OriginalPlan;

/**
 * HTTP EndPoint Recipe able to minify a list of assets files into an unique file, the file
 *  can be directly served by the HTTP server.
 *  The recipe can directly return the file if it's already generated (behavior defined by the parameter "noOverwrite")
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @deprecated Use `Teknoo\East\Common\Recipe\Plan\MinifierEndPoint` instead
 */
class MinifierEndPoint extends OriginalPlan implements MinifierEndPointInterface
{
}
