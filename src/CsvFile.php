<?php

namespace Uptacknerd\FxBtScripts;

use DateTime;
use DateTimeZone;
use RuntimeException;
use Generator;

class CsvFile extends AbstractFile
{
    private int $currentLine = 0;

    public function __construct(string $filename, bool $detectTimeframe = false) {
        parent::__construct($filename, $detectTimeframe);
    }

    public function __destruct() {
        if ($this->handle === null) {
            return;
        }
        fclose($this->handle);
    }

    public function open(string $mode = 'r') {
        $this->handle = fopen($this->filename, $mode);
        $this->currentLine = 0;
        if ($this->handle === false) {
            throw new RuntimeException("Failed to open " . $this->filename);
        }
    }

    public function seek(Datetime $date) {
        // Round date to midnight
        $date = clone $date;
        $date->setTime(0, 0, 0, 0);

        // Initialize dichotomic interval
        $minPosition = 0;
        $maxPosition = filesize($this->filename) - 1;
        if ($minPosition == $maxPosition) {
            // empty file
            return null;
        }

        // Read line of interval start
        fseek($this->handle, $minPosition);

        // Read line of interval end
        /** @var Bar $endBar */
        $this->findRecordNear($maxPosition);
        $maxPosition = ftell($this->handle);

        while ($minPosition != $maxPosition) {
            $middlePosition = (int) ($minPosition + ($maxPosition - $minPosition) / 2);
            $middlePosition = $this->findRecordNear($middlePosition);
            $middleBar = $this->readBar();

            /** @var Bar $middleBar */
            if ($middleBar->getOpenDate() <= $date) {
                if ($minPosition == $middlePosition) {
                    // we are on the closest record
                    break;
                }
                $minPosition = $middlePosition;
            }
            if ($middleBar->getOpenDate() > $date) {
                if ($maxPosition == $middlePosition) {
                    // we are on the closest record
                    break;
                }
                $maxPosition = $middlePosition;
            }
        }
        return;
    }

    public function seekFirst() {
        fseek($this->handle, 0);
    }

    public function seekLast() {
        $this->findRecordNear(filesize($this->filename) -1);
    }

    public function tell(): Datetime {
        $position = ftell($this->handle);
        $line = fgets($this->handle, 128);
        if ($line === false) {
            throw new RuntimeException("Failed to seek in file");
        }
        $bar = $this->decodeRecord($line);
        fseek($this->handle, $position);

        return $bar->getOpenDate();
    }

    /**
     * Seek in the file at the begining of the record
     */
    private function findRecordNear(int $position) {
        // Read line
        $matches = null;
        $bar = null;
        while ($bar === null) {
            if ($position > 0) {
                // check that before the line we find a LF char, marking the end
                // of a previous record
                fseek($this->handle, $position - 1);
                if (fgetc($this->handle) != "\n") {
                    $position--;
                    continue;
                }
            }
            fseek($this->handle, $position);
            $line = fgets($this->handle, 128);
            $pattern = '#^([^\n]*)(\r?\n)?$#';
            preg_match($pattern, $line, $matches);
            if (is_array($matches)) {
                $bar = $this->decodeRecord($matches[1]);
            }
        }

        fseek($this->handle, $position);
        return;
    }

    public function produceFormat(): int {
        return self::PRODUCE_BAR;
    }

    public function consumeFormat(): int {
        return self::CONSUME_BOTH;
    }

    public function readTicks(): Generator {
        throw new runtimeException("Not implemented");
    }

    public function readBars(): Generator {
        // A line should not exceed 128 bytes
        while (($bar = $this->readBar()) !== null) {
            yield $bar;
        }
    }

    public function readBar(): ?Bar {
        $line = fgets($this->handle, 128);
        if ($line === false) {
            return null;
        }
        return $this->decodeRecord($line);
    }

    public function addBar(Bar $bar) {
        $row = [];
        $row[]  = $bar->getOpenDate()->format('Y.m.d H:i:s');
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
        $row[]  = $bar->getOpenDate()->format('Y.m.d H:i:s');
        $row[] .= number_format($bar->getOpen(), 6, '.', '');
        $row[] .= number_format($bar->getHigh(), 6, '.', '');
        $row[] .= number_format($bar->getLow(), 6, '.', '');
        $row[] .= number_format($bar->getClose(), 6, '.', '');
        $row[] .= number_format($bar->getVolume(), 6, '.', '');
        $row[] .= $bar->getTickDate()->format('Y.m.d H:i:s.u');

        fwrite($this->handle, implode(',', $row) . PHP_EOL);
    }

    protected function decodeRecord(string $line): Bar {
        $line = str_replace (array("\r\n", "\n", "\r"), '', $line);
        $fields = explode(',', $line);

        // A line is
        // date time with milliseconds
        // bid price
        // ask price
        // bid volume
        // ask volume

        if (count($fields) != 5) {
            throw new RuntimeException("Fields count incorrect in line ");
        }

        $time = DateTime::createFromFormat('Y.m.d H:m:s.u', $fields[0], new DateTimeZone(('GMT')));
        if ($time === false) {
            throw new RuntimeException("Bad format in date time " . $fields[0]);
        }
        $bar = new Bar(1); // Timeframe = S1 (1 second)
        $date = Datetime::createFromFormat('Y.m.d H:i:s.u', $fields[0], new DateTimeZone('UTC'));
        $bar->setOpenDate($date)
            ->setOpen($fields[1])
            ->setHigh($fields[1])
            ->setLow($fields[1])
            ->setClose($fields[1])
            ->setVolume($fields[3] + $fields[4]);
        return $bar;
    }
}
