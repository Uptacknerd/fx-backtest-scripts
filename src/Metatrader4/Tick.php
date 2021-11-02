<?php

namespace Uptacknerd\FxBtScripts\Metatrader4;

use RuntimeException;
use Datetime;
use DateTimeZone;

class Tick {
    private Datetime $date; // Tick datetime with milliseconds
    private float $bid;
    private float $ask;
    private float $bidVolume = 1;
    private float $askVolume;

    public function __construct(datetime $date, float $bid, float $ask, float $bidVolume, float $askVolume) {
        $this->date = $date;
        $this->bid = $bid;
        $this->ask = $ask;
        $this->bidVolume = $bidVolume;
        $this->askVolume = $askVolume;
    }

    public function __toString()
    {
        return $this->time->format('Y-m-d H:i:s u') . '    '
        . number_format($this->bid, 6, '.', ',') . '    '
        . number_format($this->ask, 6, '.', ',') . '    '
        . number_format($this->bidVolume, 6, '.', ',') . '    '
        . number_format($this->askVolume, 6, '.', ',') . PHP_EOL;
    }

    /**
     *
     *
     * Getters and setters
     *
     *
     */

    public function getDate() {
        return $this->date;
    }

    public function setDate(Datetime $date): Tick {
        $this->date = $date;
        return $this;
    }

    /**
     * Get the value of ask
     *
     * @return  float
     */
    public function getAsk()
    {
        return $this->ask;
    }

    /**
     * Set the value of ask
     *
     * @param  float  $ask
     *
     * @return  self
     */
    public function setAsk(float $ask)
    {
        $this->ask = $ask;

        return $this;
    }

    /**
     * Get the value of bidVolume
     *
     * @return  float
     */
    public function getBidVolume()
    {
        return $this->bidVolume;
    }

    /**
     * Set the value of bidVolume
     *
     * @param  float  $bidVolume
     *
     * @return  self
     */
    public function setBidVolume(float $bidVolume)
    {
        $this->bidVolume = $bidVolume;

        return $this;
    }

    /**
     * Get the value of askVolume
     *
     * @return  float
     */
    public function getAskVolume()
    {
        return $this->askVolume;
    }

    /**
     * Set the value of askVolume
     *
     * @param  float  $askVolume
     *
     * @return  self
     */
    public function setAskVolume(float $askVolume)
    {
        $this->askVolume = $askVolume;

        return $this;
    }

    /**
     * Get the value of bid
     *
     * @return  float
     */
    public function getBid()
    {
        return $this->bid;
    }

    /**
     * Set the value of bid
     *
     * @param  float  $bid
     *
     * @return  self
     */
    public function setBid(float $bid)
    {
        $this->bid = $bid;

        return $this;
    }
}