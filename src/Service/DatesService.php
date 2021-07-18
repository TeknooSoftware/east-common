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

namespace Teknoo\East\Website\Service;

use DateTime;
use DateTimeInterface;

/**
 * Simple service to manage date and hour in a recipe to return always the same date during the request and avoid
 * differences between two datetime instance.
 *
 * You can override the date to pass by calling "setCurrentDate"
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DatesService
{
    private ?DateTimeInterface $currentDate = null;

    public function setCurrentDate(DateTimeInterface $currentDate): DatesService
    {
        $this->currentDate = $currentDate;

        return $this;
    }

    private function getCurrentDate(): DateTimeInterface
    {
        if ($this->currentDate instanceof DateTimeInterface) {
            return $this->currentDate;
        }

        return $this->currentDate = new DateTime();
    }

    public function passMeTheDate(callable $setter, bool $preferRealDate = false): self
    {
        if (false === $preferRealDate) {
            $setter(clone $this->getCurrentDate());
        } else {
            $setter(new DateTime());
        }

        return $this;
    }
}
