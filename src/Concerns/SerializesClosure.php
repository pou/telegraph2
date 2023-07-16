<?php

namespace DefStudio\Telegraph\Concerns;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

trait SerializesClosure
{
    // todo exception
    public function serializeClosure(Closure $closure): string
    {
        return serialize(new SerializableClosure($closure, true));
    }

    protected function unserializeClosure(string $closure): ?Closure
    {
        return unserialize($closure)->getClosure();
    }
}
