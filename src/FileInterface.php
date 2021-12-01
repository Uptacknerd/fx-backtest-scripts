<?php

namespace Uptacknerd\FxBtScripts;

use Datetime;
use Generator;

interface FileInterface
{
    public function __construct(string $filename, bool $detectTimeframe = false);

    /**
     * Is the output injectable to a trading software MT4, MT5, ...
     *
     * @return boolean
     */
    // public function isInjectable(): ?string;

    public function open(string $mode = 'r');

    public function seek(Datetime $date);

    public function seekLast();

    public function seekFirst();

    public function tell(): Datetime;

    public function produceFormat(): int;

    public function consumeFormat(): int;

    public function negociateOutputType(FileInterface $producer, $preferTick = true): ?int;

    public function negociateInputType(FileInterface $consumer, $preferTickTick = false): ?int;

    public function readTicks(): Generator;

    public function readBars(): Generator;

    public function addBar(Bar $bar);

    /**
     * Undocumented function
     *
     * @param Bar $bar A bar with a tick timestamp
     * @return void
     */
    public function addTick(Bar $bar);

    public function setTimeframe(int $timeframe);

    public function setStartDate($startDate);

    public function setEndDate($endDate);

    public function getStartDate();

    public function getEndDate();

    public function incrementBarsCount();

    public function setServerName(string $serverName);

    /**
     * Terminate writing  the file (to update headers)
     *
     * @return void
     */
    public function finish();
}
