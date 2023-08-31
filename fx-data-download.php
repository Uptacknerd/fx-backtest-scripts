#!/usr/bin/php
<?php
/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
   Script to download historical data from Dukascopy site.

  Usage:
    1. Before downloading define ourselves by what date the script can download the data, so enter the date that interests you.
       You can also fire up MT4 and throw the script data.mq4 on the chart.
       The communication we have given the correct date format that we use in the script (the date specified in the sample script below is - 1230768000.
       You can also use the Epoch Converter to change the format to format linux seconds-since-1970 format at epochconverter.com.

    2. Then, the resulting date format type in a file along with the selected currency pair. Here's an example:

    <?php
      $symbols = array (
        "USDJPY" => 1230768000
      );

    then we save the file. If you don't create a file, then by default EURUSD would be downloaded.

    3. Finally run like this:

      php fx-data-download.php

*/

use Amp\Parallel\Worker;
use Amp\Promise;

require __DIR__ . '/vendor/autoload.php';

(function () {
    $doc = <<<DOC
    Usage:
      fx-data-download.php [--begin=date] [--end=date] [--threads=count] --symbol=symbol ...
      fx-data-download.php --version

    Options:
      -h --help                Show this screen.
      --version                Show version.
      -b --begin=date          Date where the convertion should begin (UTC)
      -e --end=date            Date where the conversion should finish (UTC)
      --symbol=symbol          Symbol
      -c --compress            Compress data
      --threads=threads        Number of threads to use [default: 1]

    A symbol can be remaned using the syntax EURUSD=EURODOLLAR
    where EURUSD is the name of the symbol to download and EURODOLLAR is the name
    of the symbol's folder in the filesystem
    DOC;

    $args = Docopt::handle($doc, array('version' => 'FX data downloader v1.0'));

    if (empty($args['--begin'])) {
        $args['--begin'] = '2007-03-30';
        $beginDate = DateTime::createFromFormat('Y-m-d', '2007-03-30', new DateTimeZone('UTC'));
    }
    $beginDate = DateTime::createFromFormat('Y-m-d', $args['--begin'], new DateTimeZone('UTC'));
    $beginDate->setTime(0, 0, 0);

    if (empty($args['--end'])) {
        $endDate = (new DateTime('now', new DateTimeZone('UTC')));
    } else {
        $endDate = DateTime::createFromFormat('Y-m-d', $args['--end'], new DateTimeZone('UTC'));
    }
    $endDate->setTime(0, 0, 0);

    $compress = false;
    if ($args['--compress']) {
        $compress = true;
    }

    $threadsCount = $args['--threads'] ?? 1;

    foreach ($args['--symbol'] as $symbol) {
        downloadSymbol($symbol, $beginDate, $endDate, $compress, $threadsCount);
    }

})();

die();

function knownSymbols(): array
{
    if (file_exists(__DIR__ . '/symbols.php')) {
        require_once __DIR__ . '/symbols.php';
    }

    return $symbols;
}

/**
 * Download a single symbol
 *
 * @param string $symbol
 * @param DateTime $beginDate
 * @param DateTime $endDate
 * @return void
 */
function downloadSymbol(string $symbol, DateTime $beginDate, DateTime $endDate, bool $compress = false, int $threadsCount)
{
    $splitSymbol = splitSymbol($symbol);
    $downloadSymbol = $splitSymbol[0];
    $saveSymbol = $splitSymbol[1];

    $symbols = knownSymbols();
    if (isset($symbols[$downloadSymbol])) {
        if ($beginDate->getTimestamp() < $symbols[$downloadSymbol]) {
            $beginDate->setTimestamp($symbols[$downloadSymbol]);
        }
    }
    // $threadsCount = 4;
    error(sprintf("Info: Downloading %s starting at %s with %d threads",
        $downloadSymbol,
        $beginDate->format('Y-m-d H:i:s'),
        $threadsCount
    ) . PHP_EOL);

    $promises = [];
    for ($offset = 0; $offset < $threadsCount; $offset++) {
        $workerStartTime = new Datetime();
        $workerStartTime->setTimezone(new DateTimeZone('UTC'));
        $workerStartTime->setTimestamp($beginDate->getTimestamp() + (3600 * $offset));
        $promises[$offset] = Worker\enqueueCallable(
            'Uptacknerd\FxBtScripts\downloadTask',
            __DIR__ . '/cache/php',
            $downloadSymbol,
            $saveSymbol,
            $workerStartTime,
            $endDate,
            $threadsCount,
            $compress
        );
    }

    $globalPromise = Promise\all($promises);
    $responses = Promise\wait($globalPromise);

    $missingfilecount = 0; /* number of the file does not exist on the server */
    $failedfilecount = 0;  /* number of files failed to download */
    $successfilecount = 0; /* number of files successfully downloaded */
    $skippedfilecount = 0; /* the number of files to be skipped */
    foreach ($responses as $offset => $response) {
        $missingfilecount += $response['missing'];
        $failedfilecount +=  $response['failed'];
        $successfilecount += $response['success'];
        $skippedfilecount += $response['skipped'];
    }

    error(
        sprintf(
            "Info: %s: %d files missing, %d files failed, %d files downloaded, %d files skipped",
            $symbol,
            $missingfilecount,
            $failedfilecount,
            $successfilecount,
            $skippedfilecount
        ) . PHP_EOL
    );
}

function splitSymbol(string $symbol): array
{
    $parts = explode('=', strtoupper($symbol));
    if (count($parts) === 1) {
        return [$parts[0], $parts[0]];
    }

    return $parts;
}

function error($error)
{
    echo $error;
    $fd = fopen('error.log', 'a+');
    fwrite($fd, $error);
    fclose($fd);
}

/*
 * According to the number of seconds to return all day: hours, minutes, seconds format.
 */
function outtm($sec)
{
    $d = floor($sec / 86400);
    $tmp = $sec % 86400;
    $h = floor($tmp / 3600);
    $tmp %= 3600;
    $m = floor($tmp / 60);
    $s = $tmp % 60;
    return "[" . $d . "days" . $h . "h" . $m . "points" . $s . "s]";
}
