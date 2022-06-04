<?php

namespace Uptacknerd\FxBtScripts;

use DateInterval;
use Datetime;
use RuntimeException;

class Bar
{
    private ?Datetime $openDate    = null; // Bar datetime
    private ?Datetime $closeDate   = null;
    private ?int      $timeframe   = null; // in seconds
    private ?float    $open        = null; // OHLCV values
    private ?float    $high        = null;
    private ?float    $low         = null;
    private ?float    $close       = null;
    private ?float    $volume      = null;
    private ?Datetime  $tickDate   = null;
    // private int   $flags;

    public function __construct(int $timeframe) {
        $this->timeframe = $timeframe;
    }

    /**
     * Get the value of open
     *
     * @return  float
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Set the value of open
     *
     * @param  float  $open
     *
     * @return  self
     */
    public function setOpen(float $open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get the value of low
     *
     * @return  float
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * Set the value of low
     *
     * @param  float  $low
     *
     * @return  self
     */
    public function setLow(float $low)
    {
        $this->low = $low;

        return $this;
    }

    /**
     * Get the value of high
     *
     * @return  float
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * Set the value of high
     *
     * @param  float  $high
     *
     * @return  self
     */
    public function setHigh(float $high)
    {
        $this->high = $high;

        return $this;
    }

    /**
     * Get the value of close
     *
     * @return  float
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * Set the value of close
     *
     * @param  float  $close
     *
     * @return  self
     */
    public function setClose(float $close)
    {
        $this->close = $close;

        return $this;
    }

    /**
     * Get the value of volume
     *
     * @return  float
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set the value of volume
     *
     * @param  float  $volume
     *
     * @return  self
     */
    public function setVolume(float $volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get the value of timeframe
     */
    public function getTimeframe()
    {
        return $this->timeframe;
    }

    /**
     * Get the value of openDate
     *
     * @return  null|Datetime
     */
    public function getOpenDate(): ?Datetime
    {
        if (!isset($this->openDate)) {
            return null;
        }

        return clone $this->openDate;
    }

    public function getCloseDate() {
        return $this->closeDate;
    }

    protected function setCloseDate() {
        if ($this->openDate === null) {
            return null;
        }

        $this->closeDate =  clone $this->openDate;
        $this->closeDate->add(new DateInterval("PT" . $this->timeframe . "S"));
    }

    /**
     * Set the value of openDate and find the close date with timeframe
     *
     * @param  Datetime  $openDate
     *
     * @return  self
     */
    public function setOpenDate(Datetime $openDate)
    {
        $this->openDate = clone $openDate;
        $this->roundOpenDate();
        $this->setCloseDate();
        return $this;
    }

    private function roundOpenDate() {
        if ($this->timeframe === null) {
            return;
        }

        $timestamp = $this->openDate->getTimestamp();
        $timestamp = $timestamp - ($timestamp % $this->timeframe);
        $this->openDate->setTimestamp($timestamp); // Microseconds also set to 0
    }

    public function getTickDate(): ?Datetime {
        return clone $this->tickDate;
    }

    public function setTickDate(Datetime $tickDate) {
        $this->tickDate = $tickDate;
        return $this;
    }
}