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

namespace Teknoo\East\CommonBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\FoundationBundle\Command\Client;

/**
 * Command to run MinifierCommand recipe to compile and minify some assets in a single file
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MinifyCommand extends Command
{
    private const string SETNAME_ARGUMENT_NAME = 'setName';

    private const string FILENAME_ARGUMENT_NAME = 'finalAssetsPath';

    public function __construct(
        string $name,
        string $description,
        private readonly Executor $executor,
        private readonly Client $client,
        private readonly MinifierCommandInterface $minifierCommand,
        private readonly MessageFactoryInterface $messageFactory,
        private readonly SourceLoaderInterface $sourceLoader,
        private readonly PersisterInterface $persister,
        private readonly MinifierInterface $minifier,
        private readonly string $fileType,
        private readonly string $finalAssetsLocation,
    ) {
        $this->setDescription($description);

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: self::SETNAME_ARGUMENT_NAME,
            mode: InputArgument::REQUIRED,
            description: 'Set to build',
        );

        $this->addArgument(
            name: self::FILENAME_ARGUMENT_NAME,
            mode: InputArgument::REQUIRED,
            description: 'File to build',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = [
            self::SETNAME_ARGUMENT_NAME,
            self::FILENAME_ARGUMENT_NAME,
        ];

        $workPlan = [
            OutputInterface::class => $output,
            'sourceLoader' => $this->sourceLoader,
            'persister' => $this->persister,
            'minifier' => $this->minifier,
            'type' => $this->fileType,
            'finalAssetsLocation' => $this->finalAssetsLocation,
        ];

        foreach ($args as $arg) {
            $workPlan[$arg] = $input->getArgument($arg);
        }

        $request = $this->messageFactory->createMessage('1.1');

        $client = clone $this->client;
        $client->setOutput($output);

        $this->executor->execute(
            $this->minifierCommand,
            $request,
            $client,
            $workPlan,
        );

        $client->sendResponse(null, true);

        return $client->returnCode;
    }
}
