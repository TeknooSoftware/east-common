<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

use Teknoo\East\Common\Contracts\Object\VisitableInterface;

use function is_string;

class VisitableTestObject implements VisitableInterface
{
    private string $name;
    private string $title;

    public function __construct(string $name = '', string $title = '')
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function visit(string|array $visitors, ?callable $callable = null): VisitableInterface
    {
        if (is_string($visitors)) {
            $visitors = [$visitors => $callable];
        }

        foreach ($visitors as $property => $callback) {
            if (isset($this->{$property})) {
                $callback($this->{$property});
            }
        }

        return $this;
    }
}
