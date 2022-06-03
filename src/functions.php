<?php

namespace Uptacknerd\FxBtScripts;

/**
 * Undocumented function
 *
 * @param string $pair Symbol to download
 * @param integer $firsttick  begin time
 * @param integer $endtime    end time
 * @param integer $increment  increment between chunks
 * @return array
 */
function downloadTask(string $baseDir, string $pair, int $firsttick, int $endtime, int $increment): array {
    $missingfilecount = 0; /* @var number of the file does not exist on the server */
    $failedfilecount = 0;  /* @var number of files failed to download */
    $successfilecount = 0; /* @var number of files successfully downloaded */
    $skippedfilecount = 0; /* @var the number of files to be skipped */
    $lasttime = 0; /* @var for each file before downloading the first day of a record of the current GMT time */
    $lastday = 0;  /* @var Day one day before the download number */

    $handle = curl_init();
    if ($handle === false) {
        error("Failed to prepare HTTP requests");
        exit(1);
    }
    for ($i = $firsttick; $i < $endtime - 3600; $i += ($increment * 3600)) {
        $year = gmstrftime('%Y', $i);
        $month = str_pad(gmstrftime('%m', $i) - 1, 2, '0', STR_PAD_LEFT); // format (month-1), such as the conversion of 00 January, February -> 01
        $day = gmstrftime('%d', $i);
        $hour = gmstrftime('%H', $i);
        $url = "http://datafeed.dukascopy.com/datafeed/$pair/$year/$month/$day/{$hour}h_ticks.bi5";

        // When the file begins to download before the first one day to $lasttime, $lastday recorded.  Prompt action is actually downloaded to the day, no other practical effect.
        if ($day != $lastday) {
            // If you download the previous day within three seconds BIN data was processed, that the previous day's data has been downloaded.
            if (time() - $lasttime < 3) {
                //error("BIN data already downloaded. Skipped.\r\n");
            }

            $lasttime = time();
            $lastday = $day;
            echo ("Info: Downloading BIN data of $pair " . gmstrftime("%m/%d/%Y", $i) . "\r\n");
        }

        // Calculate the local storage path
        $localpath = "$baseDir/$pair/$year/$month/$day/";
        $binlocalfile = $localpath . $hour . "h_ticks.bin";
        $localfile = $localpath . $hour . "h_ticks.bi5";

        // Only when the local file does not exist when it starts to download
        if (file_exists($localfile) || file_exists($binlocalfile)) {
            // Local file already exists, skip.  Logic programs to ensure every file download is complete.
            //error("Info: skipping $url, local file already exists.\r\n");
            $skippedfilecount++;
            continue;
        }
        // If path does not exists, create it
        if (!file_exists($localpath)) {
            @mkdir($localpath, 0777, true);
        }

        // If you can not connect to the server is continuously attempting to download, try up to three times
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, 0);

        $retryCount = 0;
        do {
            $result = curl_exec($handle);
            $retryCount++;
        } while ($retryCount <= 3 && curl_errno($handle));

        if (curl_errno($handle)) {
            error("FATAL: Couldn't download $url.\r\nError was: " . curl_error($handle) . "\r\n");
            $quitstring = "Unable to connect to server";
            exit(1);
        }

        // The server returns the data, but does not necessarily represent the download success
        switch (curl_getinfo($handle, CURLINFO_HTTP_CODE)) {
            case 404:
                // The server returns a 404 number to indicate you want to download the file does not exist
                $weekday = gmstrftime('%a', $i);
                if (strcasecmp($weekday, 'sun') == 0 || strcasecmp($weekday, 'sat') == 0) {
                    // Missing file on weekends data
                    error("Info: missing weekend file $url\r\n");
                } else {
                    error("WARNING: missing file $url ($i - " . gmstrftime("%m/%d/%Y %H:%M GMT", $i) . ")\r\n");
                }

                $missingfilecount++;
                break;

            case 200:
                // The server returns a 200 number, indicating that the file is complete download.
                file_put_contents($localfile, $result);
                //error("Info: successfully downloaded $url\r\n");
                $successfilecount++;
                break;

            default:
                // Returns the number of unknown, indicates that the file download an unknown error
                error("WARNING: did not download $url ($i - " . gmstrftime("%m/%d/%Y %H:%M GMT", $i) . ") - error code was " . curl_getinfo($handle, CURLINFO_HTTP_CODE) . "\r\nContent was: $result\r\n");
                $failedfilecount++;
        }
        // Here the end of a file to download, about to enter the next file

    }
    curl_close($handle);

    return [
        'missing' => $missingfilecount,
        'failed'  => $failedfilecount,
        'success' => $successfilecount,
        'skipped' => $skippedfilecount,
    ];
}
