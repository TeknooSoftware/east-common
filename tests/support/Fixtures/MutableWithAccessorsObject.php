<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

class MutableWithAccessorsObject
{
    private string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
