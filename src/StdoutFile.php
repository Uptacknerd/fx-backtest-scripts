<?php

namespace Uptacknerd\FxBtScripts;

use DateInterval;
use Datetime;
use DateTimeZone;
use RuntimeException;
use Generator;

class StdoutFile extends AbstractFile
{

    protected string $filename = 'stdout';
    protected $handle = STDOUT;

    public function open(string $mode = 'r')
    {
        // Do nothing as STDOUT is a special file handler, already opened by PHP
    }

    public function seek(Datetime $date): ?array
    {
        throw new RuntimeException("Cannot seek in write only file" . $this->filename);
    }

    public function seekLast()
    {
        throw new RuntimeException("Cannot seek in write only file" . $this->filename);
    }

    public function seekFirst()
    {
        throw new RuntimeException("Cannot seek in write only file" . $this->filename);
    }

    public function tell(): Datetime
    {
        return Datetime::createFromFormat('U', '0', new DateTimeZone('UTC'));
    }

    public function produceFormat(): int
    {
        return self::PRODUCE_NONE;
    }

    public function consumeFormat(): int
    {
        return self::CONSUME_BOTH;
    }

    public function addBar(Bar $bar)
    {
        $row = [];
        $row[]  = $bar->getOpenDate()->format('Y.m.d H:i:s.u');
        $row[] .= number_format($bar->getOpen(), 6, '.', '');
        $row[] .= number_format($bar->getHigh(), 6, '.', '');
        $row[] .= number_format($bar->getLow(), 6, '.', '');
        $row[] .= number_format($bar->getClose(), 6, '.', '');
        $row[] .= number_format($bar->getVolume(), 6, '.', '');

        fwrite($this->handle, implode(',', $row) . PHP_EOL);
    }

    public function addTick(Bar $bar)
    {
        $row = [];
        $row[]  = $bar->getOpenDate()->format('Y.m.d H:i:s.u');
        $row[] .= number_format($bar->getOpen(), 6, '.', '');
        $row[] .= number_format($bar->getHigh(), 6, '.', '');
        $row[] .= number_format($bar->getLow(), 6, '.', '');
        $row[] .= number_format($bar->getClose(), 6, '.', '');
        $row[] .= number_format(max($bar->getVolume(), 1), 6, '.', '');
        $row[]  = $bar->getTickDate()->format('Y.m.d H:i:s.u');

        fwrite($this->handle, implode(',', $row) . PHP_EOL);
    }

    public function readTicks(): Generator
    {
        throw new RuntimeException("Cannot read in write only file" . $this->filename);
    }

    public function readBars(): Generator
    {
        throw new RuntimeException("Cannot read from write only file" . $this->filename);
    }
}
