<?php

namespace Uptacknerd\FxBtScripts;

use Uptacknerd\FxBtScripts\FileInterface;
use Datetime;
use RuntimeException;

abstract class AbstractFile implements FileInterface
{

    const PRODUCE_NONE = -1;
    const PRODUCE_TICK = 0;
    const PRODUCE_BAR = 1;
    const PRODUCE_BOTH = 2;

    const CONSUME_NONE = -1;
    const CONSUME_TICK = 0;
    const CONSUME_BAR = 1;
    const CONSUME_BOTH = 2;

    protected string $filename;
    protected bool $useTemp;

    protected $handle;
    protected int $timeframe; // in seconds
    protected int $barsCount = 0;
    protected string $symbol = '';
    protected ?Datetime $startDate = null;
    protected ?Datetime $endDate = null;
    protected ?Bar $bar = null;
    protected ?Bar $firstBar = null;
    protected ?Bar $lastBar = null;

    /**
     * all bars to aggregate in a single bar for the specified timeframe
     */
    protected array $bars = [];
    protected Datetime $currentBarDate;

    public function __construct(string $filename, bool $detectTimeframe = false) {
        $this->filename = $filename;
    }

    protected function getTempFilename():string {
        if ($this->filename == '' || !isset($this->filename)) {
            return '';
        }

        return $this->filename . '.temp';
    }

    public function getFilename() {
        if ($this->useTemp) {
            return $this->getTempFilename();
        }

        return $this->filename;
    }

    public function open(string $mode = 'r') {
        switch ($mode) {
            case 'r':
            case 'w':
            case 'w+':
                break;

            default:
                throw new RuntimeException("Unsupported file mode $mode");
        }

        $filename = $this->filename;
        if (in_array($mode, ['w', 'w+'])) {
            if (file_exists($this->filename) && !is_writable($this->filename)) {
                throw new runtimeException("Output file already exists and is read only");
            }
            $this->useTemp = true;
            $filename = $this->getTempFilename();
        }
        $this->handle = fopen($filename, $mode);
        if ($this->handle === false) {
            throw new RuntimeException("Failed to open " . $filename);
        }

    }

    public function close() {
        fclose($this->handle);
        if ($this->useTemp) {
            if (!rename($this->getFilename(), $this->filename)) {
                throw new RuntimeException("Failed to rename temporary output file to its final name");
            }
            $this->useTemp = false;
        }
    }

    public function setTimeframe(int $timeframe)
    {
        $this->timeframe = $timeframe;
    }

    /**
     * Get the value of timeframe
     */
    public function getTimeframe()
    {
        return $this->timeframe;
    }

    /**
     * Get the value of startDate
     *
     * @return  Datetime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate) {
        if ($startDate instanceof Datetime) {
            $this->startDate = $startDate;
        } else {
            $this->startDate = Datetime::createFromFormat('U', $startDate);
        }

        return $this;
    }

    /**
     * Get the value of endDate
     *
     * @return  Datetime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }


    public function setEndDate($endDate) {
        if ($endDate instanceof Datetime) {
            $this->endDate = $endDate;
        } else {
            $this->endDate = Datetime::createFromFormat('U', $endDate);
        }

        return $this;
    }

    /**
     * Get the type of data to feed depending on the producer
     *
     * @param FileInterface $producer
     * @return void
     */
    public final function negociateInputType(FileInterface $producer, $preferTick = true): ?int {
        if ($producer->produceFormat() == AbstractFile::PRODUCE_NONE) {
            return null;
        }

        switch ($this->consumeFormat()) {
            case self::CONSUME_NONE:
                return null;

            default:
                return self::CONSUME_BAR;

            case self::CONSUME_BOTH:
                if ($preferTick) {
                    if (in_array($producer->produceFormat(), [self::PRODUCE_BOTH, self::PRODUCE_TICK])) {
                        return self::CONSUME_TICK;
                    }
                }
                break;

            case self::CONSUME_TICK:
                return self::CONSUME_TICK;
                break;
        }

        return self::CONSUME_BAR;
    }

    /**
     * Undocumented function
     *
     * @param FileInterface $consumer
     * @return void
     */
    public function negociateOutputType(FileInterface $consumer, $preferTick = true): ?int {
        if ($consumer->consumeFormat() == AbstractFile::CONSUME_NONE) {
            return null;
        }

        switch ($this->produceFormat()) {
            case self::PRODUCE_NONE:
                return null;

            default:
                return self::PRODUCE_BAR;

            case self::PRODUCE_BOTH:
                if ($preferTick) {
                    if (in_array($consumer->consumeFormat(), [self::CONSUME_BOTH, self::CONSUME_TICK])) {
                        return self::PRODUCE_TICK;
                    }
                }
                break;

            case self::PRODUCE_TICK:
                return self::PRODUCE_TICK;
        }

        return self::PRODUCE_BAR;

    }

    public function aggregateBars() {
        /** @var Bar $bar */
        if (($bar = reset($this->bars)) === null) {
            throw new RuntimeException("No bars to aggregate");
        }

        $aggregatedBar = new Bar($this->timeframe);
        $aggregatedBar->setOpenDate($bar->getOpenDate())
            ->setOpen($bar->getOpen())
            ->setHigh($bar->getHigh())
            ->setLow($bar->getLow())
            ->setClose($bar->getClose())
            ->setVolume($bar->getVolume());

        foreach($this->bars as $bar) {
            $aggregatedBar->setHigh(max($aggregatedBar->getHigh(), $bar->getHigh()));
            $aggregatedBar->setLow(min($aggregatedBar->getLow(), $bar->getLow()));
            $aggregatedBar->setClose($bar->getClose());
            $aggregatedBar->setVolume($aggregatedBar->getVolume() + $bar->getVolume());
        }

        // Validate the current bar
        if ($aggregatedBar->getLow() > $aggregatedBar->getOpen()) {
            throw new RuntimeException("Inconsistency detected: open price below low price");
        }
        if ($aggregatedBar->getLow() > $aggregatedBar->getClose()) {
            throw new RuntimeException("Inconsistency detected: close price below low price");
        }
        if ($aggregatedBar->getHigh() < $aggregatedBar->getOpen()) {
            throw new RuntimeException("Inconsistency detected: open price above high price");
        }
        if ($aggregatedBar->getHigh() < $aggregatedBar->getClose()) {
            throw new RuntimeException("Inconsistency detected: close price above high price");
        }
        if ($aggregatedBar->getOpenDate() > $aggregatedBar->getCloseDate()) {
            throw new RuntimeException("Inconsistency detected: open date after close date");
        }

        return $aggregatedBar;
    }

    /**
     * Get the value of symbol
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set the value of symbol
     *
     * @return  self
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function finish() {

    }

    public function incrementBarsCount()
    {
        $this->barsCount++;

        return $this;
    }

    /**
     * Get the value of barsCount
     */
    public function getBarsCount()
    {
        return $this->barsCount;
    }

    /**
     * Set the value of barsCount
     *
     * @return  self
     */
    public function setBarsCount($barsCount)
    {
        $this->barsCount = $barsCount;

        return $this;
    }
}