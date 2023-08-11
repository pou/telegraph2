<?php

namespace DefStudio\Telegraph\Concerns;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

trait SerializesClosure
{
    public function serializeClosure(Closure $closure): string
    {
        return serialize(new SerializableClosure($closure));
    }

    protected function unserializeClosure(string $string): Closure
    {
        /** @var SerializableClosure $closure */
        $closure = unserialize($string);

        return $closure->getClosure();
    }
}
