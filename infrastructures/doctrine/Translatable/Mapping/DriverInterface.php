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
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as ClassMetadataODM;

/**
 * Interface to define driver, able to read a configuration / metadata about class/object's translations and return it
 * to the ExtensionMetadataFactory to configure this Doctrine extension.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
interface DriverInterface
{
    /*
     * Read extended metadata configuration for a single mapped class
     */
    /**
     * @param ClassMetadata<ClassMetadataODM|object> $meta
     * @param array<string, mixed> $config
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config): DriverInterface;
}
