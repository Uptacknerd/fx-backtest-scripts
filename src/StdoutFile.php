<?php

namespace Uptacknerd\FxBtScripts;

use DateInterval;
use Datetime;
use DateTimeZone;
use RuntimeException;
use Generator;

class StdoutFile extends AbstractFile
{

    private string $outputFormat = '';
    private ?Tick $previousTick = null;

    protected string $filename = 'stdout';
    protected $handle = STDOUT;

    public function setOptions(array $args): bool {
        switch (strtolower($args['--output-format'] ?? '')) {
            case 'mt5-tick':
                $this->outputFormat = 'mt5-tick';
                break;
            case 'mt5-bar':
                $this->outputFormat = 'mt5-bar';
                break;
            case 'ohlcv':
                $this->outputFormat = 'ohlcv';
                break;
        }
        return true;
    }

    public function isInjectable(): ?string {
        return null;
    }

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

    public function addTick(Bar $bar, Tick $tick)
    {
        if ($this->outputFormat == 'mt5-bar') {
            $this->addTickMt5Bar($bar, $tick);
        } else if ($this->outputFormat == 'mt5-tick') {
            $this->addTickMt5Tick($bar, $tick);
        } else {
            $this->addTickOhlcv($bar, $tick);
        }
    }

    protected function addTickMt5Bar(Bar $bar, Tick $tick) {
        Throw new RuntimeException("Not implemented");
    }

    protected function addTickMt5Tick(Bar $bar, Tick $tick) {
        $row = [];
        $row[] = $tick->getDate()->format('Y.m.d H:i:s.u');
        $flag = 0;
        if ($this->previousTick === null || $this->previousTick->getBid() != $tick->getBid()) {
            $row[] = number_format($tick->getBid(), 6, '.', '');
            $flag |= 2;
        } else {
            $row[] = '';
        }
        if ($this->previousTick === null || $this->previousTick->getAsk() != $tick->getAsk()) {
            $row[] = number_format($tick->getAsk(), 6, '.', '');
            $flag |= 4;
        } else {
            $row[] = '';
        }
        $row[] = ''; // in MT5 this matches the "last" column. Don't know what is it, is is empty in tick exports from MT5
        $row[] = ''; // Volume; seems always empty in tick exports from MT5
        $row[] = $flag;
        fwrite($this->handle, implode(',', $row) . PHP_EOL);
    }

    public function addTickOhlcv(Bar $bar, Tick $tick)
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

    public function setServerName(string $serveRName) {}

    public function getMinLotSize() {}

    public function setMinLotSize($minLotSize) {}

    public function getMaxLotSize() {}

    public function setMaxLotSize($maxLotSize) {}

    public function getLotStep() {}

    public function setLotStep($lotStep) {}

    public function setStopLevel($stopLevel) {}
}
