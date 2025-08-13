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

namespace Teknoo\East\Common\Minify;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Minify\Css\Minifier as CssMinifier;
use Teknoo\East\Common\Minify\Js\Minifier as JsMinifier;

use function DI\get;

return [
    MinifierInterface::class . ':css' => get(CssMinifier::class),
    CssMinifier::class => static function (): CssMinifier {
        return new CssMinifier(
            new CSS(),
        );
    },

    MinifierInterface::class . ':js' => get(JsMinifier::class),
    JsMinifier::class => static function (): JsMinifier {
        return new JsMinifier(
            new JS(),
        );
    },
];
