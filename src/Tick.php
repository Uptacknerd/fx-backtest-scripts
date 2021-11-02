<?php


namespace Uptacknerd\FxBtScripts;

use DateTime;

class tick
{

    protected Datetime $date;
    protected float    $ask;
    protected float    $bid;
    protected float    $askVolume;
    protected float    $bidVolume;

    public function getDate(): Datetime {
        return $this->date;
    }

    public function setDate(Datetime $date): self  {
        $this->date = $date;
        return $this;
    }

    public function getAsk(): float {
        return $this->ask;
    }

    public function setAsk(float $ask): self  {
        $this->ask = $ask;
        return $this;
    }

    public function getBid(): float {
        return $this->bid;
    }

    public function setBid(float $bid): self  {
        $this->bid = $bid;
        return $this;
    }

    public function getAskVolume(): float {
        return $this->askVolume;
    }

    public function setAskVolume(float $askVolume): self  {
        $this->askVolume = $askVolume;
        return $this;
    }

    public function getBidVolume(): float {
        return $this->bidVolume;
    }

    public function setBidVolume(float $bidVolume): self  {
        $this->bidVolume = $bidVolume;
        return $this;
    }
}