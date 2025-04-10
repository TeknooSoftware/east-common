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

namespace Teknoo\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Doctrine\Contracts\DBSource\ConfigurationHelperInterface;
use TypeError;

/**
 * Default Configuration Helper about Doctrine ODM configuration
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ConfigurationHelper implements ConfigurationHelperInterface
{
    private ?DocumentManager $dm = null;

    public function setManager(ManagerInterface $manager, ObjectManager $om): ConfigurationHelperInterface
    {
        if (!$om instanceof DocumentManager) {
            throw new TypeError('The object manager must be an instance of ' . DocumentManager::class);
        }

        $this->dm = $om;

        return $this;
    }

    private function getDocumentManager(): DocumentManager
    {
        if (!$this->dm) {
            throw new RuntimeException('The Doctrine Document Manager is not set for this helper');
        }

        return $this->dm;
    }

    public function registerFilter(
        string $className,
        array $parameters = [],
        bool $enabling = true
    ): ConfigurationHelperInterface {
        $this->getDocumentManager()->getConfiguration()->addFilter(
            $className,
            $className,
            $parameters,
        );

        if ($enabling) {
            $this->enableFilter($className);
        }

        return $this;
    }

    public function enableFilter(string $className): ConfigurationHelperInterface
    {
        $this->getDocumentManager()->getFilterCollection()->enable($className);

        return $this;
    }

    public function disableFilter(string $className): ConfigurationHelperInterface
    {
        $this->getDocumentManager()->getFilterCollection()->disable($className);

        return $this;
    }
}
