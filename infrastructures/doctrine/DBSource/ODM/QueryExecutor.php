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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Teknoo\East\Common\Contracts\DBSource\QueryExecutorInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Pending;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Runnable;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\States\Automated\Assertion\AssertionInterface;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsEmpty;
use Teknoo\States\Automated\Assertion\Property\IsNotEmpty;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyTrait;

/**
 * Adapter for Doctrine ODM to able query business to translate operations to Doctrine ODM specification and run it.
 * This adapter is a stated class, the query is not runnable until its method "filterOn" is not called.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements QueryExecutorInterface<ObjectInterface>
 */
class QueryExecutor implements QueryExecutorInterface, AutomatedInterface
{
    use ProxyTrait;
    use AutomatedTrait;
    use ExprConversionTrait;

    /**
     * @var callable
     */
    private $queryTypeInitializer;

    private ?string $className = null;

    /**
     * @var array<string, mixed>
     */
    private array $criteria = [];

    /**
     * @var array<string, mixed>
     */
    private array $fields = [];

    public function __construct(
        private readonly DocumentManager $documentManager,
        callable $queryTypeInitializer,
    ) {
        //Init vars
        $this->queryTypeInitializer = $queryTypeInitializer;

        //Init States
        $this->initializeStateProxy();
        $this->updateStates();
    }

    /**
     * @return string[]
     */
    protected static function statesListDeclaration(): array
    {
        return [
            Pending::class,
            Runnable::class
        ];
    }

    /**
     * @return array<int, AssertionInterface>
     */
    protected function listAssertions(): array
    {
        return [
            (new Property(Pending::class))->with('className', new IsEmpty()),
            (new Property(Runnable::class))->with('className', new IsNotEmpty()),
        ];
    }

    public function filterOn(string $className, array $criteria): QueryExecutorInterface
    {
        $this->className = $className;
        $this->criteria = $criteria;

        $this->updateStates();

        return $this;
    }

    public function updateFields(array $fields): QueryExecutorInterface
    {
        $this->fields = $fields;

        $this->updateStates();

        return $this;
    }

    public function execute(PromiseInterface $promise): QueryExecutorInterface
    {
        $this->run($promise);

        return $this;
    }
}
