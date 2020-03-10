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

namespace Teknoo\East\Website\Doctrine\Object;

use Gedmo\Translatable\Translatable;
use Teknoo\East\Website\Object\Content as OriginalContent;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Doctrine\Document\StandardTrait;

class Content extends OriginalContent implements Translatable
{
    use AutomatedTrait;
    use StandardTrait {
        AutomatedTrait::updateStates insteadof StandardTrait;
    }

    /**
     * {@inheritdoc}
     * @return array<string>
     */
    public static function statesListDeclaration(): array
    {
        return [];
    }

    public function setTranslatableLocale(?string $locale): self
    {
        $this->setLocaleField((string) $locale);

        return $this;
    }

    public function getTranslatableLocale(): string
    {
        return $this->getLocaleField();
    }
}