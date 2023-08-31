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

    /**
     * Sets additional configuration options by parsing command line arguments
     *
     * @param array $args
     * @return void
     */
    public function setOptions(array $args): bool;

    public function open(string $mode = 'r');

    /**
     * Seek file position to th specified date
     *
     * @param Datetime $date
     * @return void
     */
    public function seek(Datetime $date);

    /**
     * Seek to the last record
     *
     * @return void
     */
    public function seekLast();

    /**
     * Seek to the first record
     *
     */
    public function seekFirst();

    /**
     * Give the datetime of the current record
     *
     * @return Datetime
     */
    public function tell(): Datetime;

    /**
     * Get supported formats of this producer
     *
     * @return integer
     */
    public function produceFormat(): int;

    /**
     * get supported format of this consumer
     *
     * @return integer
     */
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
     * @param Tick $tick a tick to apend to the bar
     * @return void
     */
    public function addTick(Bar $bar, Tick $tick);

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
