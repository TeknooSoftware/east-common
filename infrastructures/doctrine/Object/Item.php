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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Object;

use Teknoo\East\Website\Object\Item as OriginalItem;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Doctrine\StandardTrait;

/**
 * Item specialization in doctrine as document.
 * The translation is now directly provided by an full internal extension embedded in this library.
 * Implement States's Doctrine Document feature via the trait `Teknoo\States\Doctrine\Document\StandardTrait`
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Item extends OriginalItem
{
    use AutomatedTrait;
    use StandardTrait {
        AutomatedTrait::updateStates insteadof StandardTrait;
    }
}
