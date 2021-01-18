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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Query\Content\PublishedContentFromSlugQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LoadContent
{
    private ContentLoader $contentLoader;

    public function __construct(ContentLoader $contentLoader)
    {
        $this->contentLoader = $contentLoader;
    }

    public function __invoke(string $slug, ManagerInterface $manager): self
    {
        $error = static function (\Throwable $error) use ($manager) {
            if ($error instanceof \DomainException) {
                $error = new \DomainException($error->getMessage(), 404, $error);
            }

            $manager->error($error);
        };

        $this->contentLoader->query(
            new PublishedContentFromSlugQuery($slug),
            new Promise(
                static function (Content $content) use ($manager, $error) {
                    $type = $content->getType();
                    if (null === $type) {
                        $error(new \RuntimeException('Content type is not available'));

                        return;
                    }

                    $manager->updateWorkPlan([
                        Content::class => $content,
                        'objectInstance' => $content,
                        'objectViewKey' => 'content',
                        'template' => $type->getTemplate(),
                    ]);
                },
                $error
            )
        );

        return $this;
    }
}
