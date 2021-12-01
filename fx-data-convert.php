#!/usr/bin/php
<?php

namespace Uptacknerd\FxBtScripts;

use Docopt;
use Datetime;
use DateTimeZone;
use RuntimeException;

if (PHP_SAPI != "cli") {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

(function () {
    global $CONFIG;
//      fx-data-convert.php --input in_file --timeframe=timeframe --output-type=model [--output=out_file] [--spread=spread] [--begin=date] [--end=date]

    $doc = <<<DOC
    Naval Fate.

    Usage:
      fx-data-convert.php --input in_file --timeframe=timeframe --symbol=symbol --output-type=model [--output=out_file] [--spread=spread] [--begin=date] [--end=date]
      fx-data-convert.php --version

    Options:
      -h --help                Show this screen.
      --version                Show version.
      -c --config config       Name of a server configuration (not implemented yet)
      -i --input=in_file       file to read data from
      -b --begin=date          Date where the convertion should begin
      -e --end=date            Date where the conversion should finish
      -o --output=file         Filename where data are written
      --symbol=symbol          Symbol
      -t --timeframe=timeframe Timeframe
      -ot --output-type=type   tick | bar : output ticks or bars
      -s --spread=spread       Spread
    DOC;

    $CONFIG = new Config();

    $args = Docopt::handle($doc, array('version'=>'FX data converter v1.0'));

    $args['--output'] = $args['--output'] ?? 'null';
    if (!in_array($args['--output-type'], ['tick', 'bar'])) {
        echo "Error: output type must be tick or bar";
        die();
    }

    $producer = getProducer($args['--input']);
    $producer->open('r');

    // Determine timeframe
    $outTimeframe = strtolower($args['--timeframe']);
    $outTimeframe = Timeframe::convertTimeframe($outTimeframe);

    // find end date
    $producer->seekLast();
    $endDate = $producer->tell();

    if (isset($args['--end'])) {
        $requestedEndDate = Datetime::createFromFormat('Y-m-d', $args['--end'], new DateTimeZone('UTC'));
        if ($requestedEndDate === false) {
            throw new RuntimeException("Invalid end date. Check your typing Use YYYY-MM-DD");
        }
        $requestedEndDate->setTime(0, 0, 0, 0);
        if ($endDate > $requestedEndDate) {
            $endDate = $requestedEndDate;
        }
        $endBar = new Bar($outTimeframe);
        $endDate = $endBar->setOpenDate($endDate)->getCloseDate();
    }

    // find begin date
    $producer->seekFirst();
    $beginDate = $producer->tell();

    if (isset($args['--begin'])) {
        $requestedBeginDate = Datetime::createFromFormat('Y-m-d', $args['--begin'], new DateTimeZone('UTC'));
        if ($requestedBeginDate === false) {
            throw new RuntimeException("Invalid begin date. Check your typing Use YYYY-MM-DD");
        }
        $requestedBeginDate->setTime(0, 0, 0, 0);
        if ($beginDate < $requestedBeginDate) {
            $beginDate = $requestedBeginDate;
        }
        $beginBar = new Bar($outTimeframe);
        $beginDate = $beginBar->setOpenDate($beginDate)->getOpenDate();
    }

    $consumer = getConsumer($args['--output']);
    $consumer->setStartDate($beginDate);
    $consumer->setEndDate($endDate);
    $consumer->setSymbol($args['--symbol']);
    // $consumer->setPoint(detectPoint($consumer->getSymbol()));
    $consumer->setTimeframe(Timeframe::convertTimeframe($args['--timeframe']));
    $consumer->open('w+');

    $productionType = $producer->negociateOutputType($consumer, $args['--output-type'] == 'tick');
    $consommationType = $consumer->negociateInputType($producer, $args['--output-type'] == 'tick');

    if ($productionType == AbstractFile::PRODUCE_TICK) {
        if ($consommationType == AbstractFile::CONSUME_TICK) {
            // produce tick and consume tick
            loopTicktoTick($producer, $consumer, $beginDate, $endDate, $outTimeframe);
        } else {
            // produce tick and consume bar
            loopTicktoBar($producer, $consumer, $beginDate, $endDate, $outTimeframe);
        }
    } else {
        if ($consommationType == AbstractFile::CONSUME_TICK) {
            // produce bar and consume tick
            throw new RuntimeException("Cannot convert bars into ticks");
        } else {
            // produce bar and consume bar
            //TODO: check that input timeframe is lower or equal to output timeframe
            loopBartoBar($producer, $consumer, $beginDate, $endDate, $outTimeframe);
        }
    }

    // Finalize the output file
    $consumer->finish();
    $consumer->close();
})();

function loopTicktoTick(FileInterface $producer, FileInterface $consumer, Datetime $beginDate, Datetime $endDate, $timeframe) {
    $producer->seek($beginDate);
    $progressDate = clone $beginDate;
    $percent = 0;

    $bar = new Bar($timeframe);
    foreach ($producer->readTicks() as $tick) {
        if ($consumer->getStartDate() > $tick->getDate()) {
            // We are not yet to the begin date
            continue;
        }

        if ($consumer->getEndDate() !== null && $tick->getDate() >= $consumer->getEndDate()) {
            // End date reached
            break;
        }

        if ($bar->getOpenDate() === null) {
            // The bar does not contains any data, initialize its open date
            $bar->setOpenDate($tick->getDate());
            $bar->setOpen($tick->getBid());
            $bar->setHigh($tick->getBid());
            $bar->setLow($tick->getBid());
            $bar->setClose($tick->getBid());
            $bar->setVolume($tick->getBidVolume() + $tick->getAskVolume());
            $bar->setTickDate($tick->getDate());
            $consumer->addTick($bar);
            $consumer->incrementBarsCount();
        } else {
            if ($tick->getDate() < $bar->getOpenDate()) {
                //throw new RuntimeException("Invalid tick : tick date < bar date");
                fwrite(STDERR, "Invalid tick : tick date (" . $tick->getDate()->format('Y:m:d H:i:s.u') . ") < bar date (" . $bar->getOpenDate()->format('Y-m-d H:i:s.u'). ")" . PHP_EOL) ;
            }
            if ($tick->getDate() >= $bar->getCloseDate()) {
                $consumer->addTick($bar);

                $bar = new Bar($timeframe);
                $bar->setOpenDate($tick->getDate());
                $bar->setOpen($tick->getBid());
                $bar->setHigh($tick->getBid());
                $bar->setLow($tick->getBid());
                $bar->setClose($tick->getBid());
                $bar->setVolume($tick->getBidVolume() + $tick->getAskVolume());
                $bar->setTickDate($tick->getDate());
                $consumer->addTick($bar);
                $consumer->incrementBarsCount();
            } else {
                // Aggregate tick to the bar
                $bar->setHigh(max($bar->getHigh(), $tick->getBid()));
                $bar->setLow(min($bar->getLow(), $tick->getBid()));
                $bar->setClose($tick->getBid());
                $bar->setVolume($bar->getVolume() + $tick->getBidVolume() + $tick->getAskVolume());
                $bar->setTickDate($tick->getDate());
                $consumer->addTick($bar);
            }
        }

        // Show progress
        if (true) {
            $currentDate = clone $tick->getDate();
            $currentDate->setTime($currentDate->format('H'), 0, 0, 0);
            if ($progressDate < $currentDate) {
                $percent = ($currentDate->getTimestamp() - $beginDate->getTimestamp()) / ($endDate->getTimestamp() - $beginDate->getTimestamp()) * 100;
                $percent = number_format($percent, 3);
                $progressDate = $currentDate;
                $progressSummary = sprintf("processing date: %s progress: %s/%% mem: %s",
                    $currentDate->format("Y-m-d H:i:s"),
                    $percent,
                    memory_get_usage(),
                );
                fwrite (STDERR, $progressSummary . PHP_EOL);
            }
        }
    }
}

function loopTicktoBar(FileInterface $producer, FileInterface $consumer, Datetime $beginDate, Datetime $endDate, $timeframe) {
    $producer->seek($beginDate);
    $progressDate = clone $beginDate;
    $percent = 0;

    $bar = new Bar($timeframe);
    foreach ($producer->readTicks() as $tick) {
        if ($consumer->getStartDate() > $tick->getDate()) {
            // We are not yet to the begin date
            continue;
        }

        if ($consumer->getEndDate() !== null && $tick->getDate() >= $consumer->getEndDate()) {
            // End date reached
            break;
        }

        if ($bar->getOpenDate() === null) {
            // The bar does not contains any data, initialize its open date
            $bar->setOpenDate($tick->getDate());
            $bar->setOpen($tick->getBid());
            $bar->setHigh($tick->getBid());
            $bar->setLow($tick->getBid());
            $bar->setClose($tick->getBid());
            $bar->setVolume($tick->getBidVolume() + $tick->getAskVolume());
        } else {
            if ($tick->getDate() < $bar->getOpenDate()) {
                //throw new RuntimeException("Invalid tick : tick date < bar date");
                fwrite(STDERR, "Invalid tick : tick date (" . $tick->getDate()->format('Y:m:d H:i:s.u') . ") < bar date (" . $bar->getOpenDate()->format('Y-m-d H:i:s.u'). ")" . PHP_EOL) ;
            }
            if ($tick->getDate() >= $bar->getCloseDate()) {
                $consumer->addBar($bar);
                $consumer->incrementBarsCount();

                $bar = new Bar($timeframe);
                $bar->setOpenDate($tick->getDate());
                $bar->setOpen($tick->getBid());
                $bar->setHigh($tick->getBid());
                $bar->setLow($tick->getBid());
                $bar->setClose($tick->getBid());
                $bar->setVolume($tick->getBidVolume() + $tick->getAskVolume());
            } else {
                // Aggregate tick to the bar
                $bar->setHigh(max($bar->getHigh(), $tick->getBid()));
                $bar->setLow(max($bar->getLow(), $tick->getBid()));
                $bar->setClose($tick->getBid());
                $bar->setVolume($bar->getVolume() + $tick->getBidVolume() + $tick->getAskVolume());
            }
        }

        // Show progress
        if (true) {
            $currentDate = clone $bar->getOpenDate();
            $currentDate->setTime($currentDate->format('H'), 0, 0, 0);
            if ($progressDate < $currentDate) {
                $percent = ($currentDate->getTimestamp() - $beginDate->getTimestamp()) / ($endDate->getTimestamp() - $beginDate->getTimestamp()) * 100;
                $percent = number_format($percent, 3);
                $progressDate = $currentDate;
                $progressSummary = sprintf("processing date: %s progress: %s/100 mem: %s",
                    $currentDate->format("Y-m-d H:i:s"),
                    $percent,
                    memory_get_usage(),
                );
                fwrite (STDERR, $progressSummary . PHP_EOL);
            }
        }
    }
}

function loopBartoBar(FileInterface $producer, FileInterface $consumer, Datetime $beginDate, Datetime $endDate, $timeframe) {
    throw new RuntimeException ("Bar to Bar not implemented");
}

function getProducer(string $filename): FileInterface {
    if ($filename == '' || $filename == '-') {
        return new StdoutFile('STDOUT', true);
    }

    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    switch (strtolower($extension)) {
        case 'fxt':
            return new FxtFile($filename, true);

        case 'csv':
            return new CsvFile($filename, true);

        case 'bi5':
            return new Bi5File($filename, true);
    }

    throw new RuntimeException("Unsupported file format: $extension");
}


function getConsumer(string $filename): FileInterface {
    if ($filename == 'null') {
        return new NullFile($filename);
    }

    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    switch (strtolower($extension)) {
        case 'fxt':
            return new FxtFile($filename);

        case 'csv':
            return new CsvFile($filename);

        case '':
            return new StdoutFile('STDOUT');
    }

    throw new RuntimeException("Unsupported file format: $extension");
}

function isConsumerCompatible(FileInterface $producer, FileInterface $consumer) {
    if ($producer->consumeFormat() == AbstractFile::PRODUCE_NONE) {
        return false;
    }

    if ($consumer->consumeFormat() == AbstractFile::CONSUME_NONE) {
        return false;
    }

    if ($producer->produceFormat() == $consumer->consumeFormat()) {
        return true;
    }

    return false;
}
