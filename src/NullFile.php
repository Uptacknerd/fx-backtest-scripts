<?php

namespace Uptacknerd\FxBtScripts;

use DateInterval;
use DateTime;
use DateTimeZone;
use RuntimeException;
use Generator;

class NullFile extends AbstractFile
{

    public function open(string $mode = 'r')
    {
    }

    public function seek(Datetime $date)
    {
    }

    public function seekLast()
    {
    }

    public function seekFirst()
    {
    }

    public function tell(): Datetime
    {
        return new Datetime();
    }

    public function produceFormat(): int
    {
        return self::PRODUCE_NONE;
    }

    public function consumeFormat(): int
    {
        return self::CONSUME_BOTH;
    }

    public function readTicks(): Generator
    {
        throw new runtimeException("Cannot read from null");
    }

    public function readBar(): ?Bar
    {
        throw new runtimeException("Cannot read from null");
    }

    public function readBars(): Generator
    {
        throw new runtimeException("Cannot read from null");
    }


    public function addBar(Bar $bar)
    {
    }

    public function addTick(Bar $bar)
    {
    }
}
