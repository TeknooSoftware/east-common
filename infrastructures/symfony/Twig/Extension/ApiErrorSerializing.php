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

namespace Teknoo\East\CommonBundle\Twig\Extension;

use Throwable;
use Twig\Attribute\AsTwigFilter;

/**
 * Twig filter `east_api_error_serialization` to serialize an error into a JSON API response, with the envelope
 * `{"meta": {"error": true}, "data": {"code": 404, "message": "..."}}`. The code follows the behavior of the step
 * `RenderError` (500 when the error has no code). When the error has previous errors, they are listed, from the
 * nearest to the first, in `data.previous`, with their code and their message. The Throwable instance itself is never
 * serialized (no class, no file, no line and no trace), and messages of server errors (code 500 and more), previous
 * errors included, are replaced by a generic message, unless `$exposeServerErrorMessage` is true.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiErrorSerializing
{
    public function __construct(
        private readonly ApiObjectSerializing $objectSerializing,
        private readonly bool $exposeServerErrorMessage = false,
        private readonly string $serverErrorMessage = 'Internal Server Error',
    ) {
    }

    /**
     * @return array{code: int, message: string}
     */
    private function export(Throwable $error): array
    {
        $code = (int) $error->getCode();
        if (empty($code)) {
            $code = 500;
        }

        $message = $error->getMessage();
        if ($code >= 500 && !$this->exposeServerErrorMessage) {
            $message = $this->serverErrorMessage;
        }

        return [
            'code' => $code,
            'message' => $message,
        ];
    }

    /**
     * @param array<string, mixed> $meta
     */
    #[AsTwigFilter(name: 'east_api_error_serialization', isSafe: ['html', 'json', 'js'])]
    public function serialize(
        Throwable $error,
        string $format = 'json',
        array $meta = [],
    ): string {
        $data = $this->export($error);

        $previous = [];
        while (($error = $error->getPrevious()) instanceof Throwable) {
            $previous[] = $this->export($error);
        }

        if ([] !== $previous) {
            $data['previous'] = $previous;
        }

        return $this->objectSerializing->serialize(
            object: $data,
            format: $format,
            meta: ['error' => true] + $meta,
        );
    }
}
