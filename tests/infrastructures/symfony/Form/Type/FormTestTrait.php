<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\East\CommonBundle\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait FormTestTrait
{
    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::any())
            ->method('add')
            ->willReturnCallback(
                function ($child, $type, array $options = array()) use ($builder) {
                    if (DocumentType::class == $type && isset($options['query_builder'])) {
                        $qBuilder = $this->createMock(Builder::class);
                        $qBuilder->expects(self::once())
                            ->method('field')
                            ->with('deletedAt')
                            ->willReturnSelf();

                        $qBuilder->expects(self::once())
                            ->method('equals')
                            ->with(null)
                            ->willReturnSelf();

                        $repository = $this->createMock(DocumentRepository::class);
                        $repository->expects(self::once())
                            ->method('createQueryBuilder')
                            ->willReturn($qBuilder);

                        $options['query_builder']($repository);
                    }

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, ['doctrine_type' => DocumentType::class])
        );
    }
}
