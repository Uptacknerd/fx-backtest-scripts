<?php

namespace Uptacknerd\FxBtScripts;

use DateTime;
use DateTimeZone;

/**
 * Undocumented function
 *
 * @param string $baseDir
 * @param string $pair
 * @param DateTime $starttime timestamp
 * @param DateTime $endtime timestamp
 * @param integer $increment (unit is hour)
 * @return array
 *
 * @see https://www.dukascopy.com/swiss/english/marketwatch/historical/
 */
function downloadTask(string $baseDir, string $pair, string $renamedPair, DateTime $starttime, DateTime $endtime, int $increment, bool $compress = true): array {
    $missingfilecount = 0; /* @var number of the file does not exist on the server */
    $failedfilecount  = 0; /* @var number of files failed to download */
    $successfilecount = 0; /* @var number of files successfully downloaded */
    $skippedfilecount = 0; /* @var the number of files to be skipped */
    $lasttime = 0; /* @var for each file before downloading the first day of a record of the current GMT time */
    $lastday  = 0;  /* @var Day one day before the download number */

    $handle = curl_init();
    if ($handle === false) {
        throw new \RuntimeException("Failed to prepare HTTP requests");
    }

    $startTimestamp = (int) $starttime->format('U');
    $endtime->setTimestamp($endtime->format('U') - 3600);
    $endTimestamp = (int) $endtime->format('U');
    for ($i = $startTimestamp; $i < $endTimestamp; $i += ($increment * 3600)) {
        $processingTimestamp = new DateTime();
        $processingTimestamp->setTimezone(new DateTimeZone('UTC'));
        $processingTimestamp->setTimestamp($i);
        $year = $processingTimestamp->format('Y');
        // Don't ask why, months are numbered from 0 to 11 ...
        $month = sprintf("%02d", $processingTimestamp->format('m') - 1);
        $day = $processingTimestamp->format('d');
        $hour = $processingTimestamp->format('H');
        $url = "http://datafeed.dukascopy.com/datafeed/$pair/$year/$month/$day/{$hour}h_ticks.bi5";

        // When the file begins to download before the first one day to $lasttime, $lastday recorded.  Prompt action is actually downloaded to the day, no other practical effect.
        if ($day != $lastday) {
            // If you download the previous day within three seconds BIN data was processed, that the previous day's data has been downloaded.
            if (time() - $lasttime < 3) {
                // \error("BIN data already downloaded. Skipped.\r\n");
            }

            $lasttime = time();
            $lastday = $day;
        }
        $message = "Info: Downloading BIN data of $pair " . $processingTimestamp->format('Y-m-d H') . "h ";

        // Calculate the local storage path
        // $localpath = "$baseDir/$renamedPair/$year/$month/$day/";
        // $binlocalfile = $localpath . $hour . "h_ticks.bin";
        // $localfile = $localpath . $hour . "h_ticks.gz";

        $dir = [$baseDir];
        $dir[] = strtoupper($renamedPair);
        $dir[] = $processingTimestamp->format('Y');
        $dir[] = $processingTimestamp->format('m');
        $dir[] = $processingTimestamp->format('d');
        $dir[] = $processingTimestamp->format('H') . 'h_ticks';
        $localfile = implode(DIRECTORY_SEPARATOR, $dir);

        if ($compress) {
            $localfile .= '.gz';
        } else {
            $localfile .= '.bi5';
        }

        // Only when the local file does not exist when it starts to download
        if (file_exists($localfile)) {
            // Local file already exists, skip.  Logic programs to ensure every file download is complete.
            // \error("Info: skipping $url, local file already exists.\r\n");
            $skippedfilecount++;
            $message .= "[ ALREADY DOWNLOADED ]" . PHP_EOL;
            echo $message;
            continue;
        } else {
            // If path does not exists, create it
            if (!file_exists(dirname($localfile))) {
                @mkdir(dirname($localfile), 0774, true);
            }
        }

        // If you can not connect to the server is continuously attempting to download, try up to three times
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, 0);

        $retryCount = 0;
        do {
            $result = curl_exec($handle);
            $retryCount++;
            if ($error = curl_errno($handle)) {
                sleep(1);
            }
        } while ($retryCount <= 3 && $error);

        if (curl_errno($handle)) {
            $message = "FATAL: Couldn't download $url.\r\nError was: " . curl_error($handle) . "\r\n";
            echo $message . PHP_EOL;
            $failedfilecount++;
            continue;
        }

        // The server returns the data, but does not necessarily represent the download success
        switch (curl_getinfo($handle, CURLINFO_HTTP_CODE)) {
            case 404:
                // The server returns a 404 number to indicate you want to download the file does not exist
                $weekday = $processingTimestamp->format('N');
                if (in_array($weekday, [6, 7])) { // 6 and 7 are the weekend days
                    // Missing file on weekends data
                    // \error("Info: missing weekend file $url\r\n");
                    $message .= "[ MISSING (week end) ]" . PHP_EOL;
                } else {
                    $message .= "[ MISSING ]" . PHP_EOL;
                    // \error("WARNING: missing file $url ($i - " . $processingTimestamp->format('m-d-Y') . ")\r\n");
                }

                $missingfilecount++;
                break;

            case 200:
                // The server returns a 200 number, indicating that the file is complete download.
                if ($compress) {
                    $result = gzcompress($result);
                }
                $size = file_put_contents($localfile, $result);
                $message .= "[ OK ]" . PHP_EOL;
                if ($size != strlen($result)) {
                    $message .= "[ FAILED TO WRITE TO DISK ]" . PHP_EOL;
                }
                // \error("Info: successfully downloaded $url\r\n");
                $successfilecount++;
                break;

            default:
                // Returns the number of unknown, indicates that the file download an unknown error
                // $message = "WARNING: did not download $url ($i - " . $processingTimestamp->format('m-d-Y') . ") - error code was " . curl_getinfo($handle, CURLINFO_HTTP_CODE) . "\r\nContent was: $result\r\n";
                $message .= "[ FAILED with HTTP " . curl_getinfo($handle, CURLINFO_HTTP_CODE) . " ]" . PHP_EOL;
                $failedfilecount++;
        }
        // Here the end of a file to download, about to enter the next file
        echo $message;
    }
    curl_close($handle);

    return [
        'missing' => $missingfilecount,
        'failed'  => $failedfilecount,
        'success' => $successfilecount,
        'skipped' => $skippedfilecount,
    ];
}
