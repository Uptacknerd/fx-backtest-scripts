<?php

namespace Uptacknerd\FxBtScripts;

use DateInterval;
use DateTime;
use DateTimeZone;
use RuntimeException;
use Generator;

class Bi5File extends AbstractFile
{
    /** @var string $instrument Instrument to use */
    private string $instrument;

    /** @var string $mode File opening mode */
    private string $mode;

    /** @var Datetime $position Position in the tick stream */
    private Datetime $position;

    private int $missingFileCount = 0;
    private int $failedFileCount = 0;

    private bool $isWindows = false;
    private $extractCommand = '';

    private string $point;

    private Datetime $today;

    private string $bi5TmpDir;

    private string $rawTmpDir;

    public function __construct(string $filename, bool $detectTimeframe = false)
    {
        $this->instrument = strtolower(basename($filename, '.bi5'));
        $this->prepareLzmaExtractCommand();
        $this->point = $this->getPoint();

        // Current date is used to know the last available date to seek
        $this->today = new Datetime('now', new DateTimeZone('UTC'));
        $this->today->setTime(0, 0, 0, 0);

        $this->bi5TmpDir = tempnam(sys_get_temp_dir(), 'tickdata-bi5');
        $this->rawTmpDir = tempnam(sys_get_temp_dir(), 'tickdata-raw');
        unlink($this->bi5TmpDir);
        unlink($this->rawTmpDir);
        @mkdir($this->bi5TmpDir);
        @mkdir($this->rawTmpDir);
    }

    public function isInjectable(): ?string {
        return null;
    }

    public function setTimeframe(int $timeframe)
    {
        // Tick data is 0
        $this->timeframe = 0;
    }

    public function open(string $mode = 'r')
    {
        // Don't open the file as Bi5 is stored as a directory / file structure in FS
        $this->mode = $mode;

        // Just prepare the HTTP client handle
        $this->handle = curl_init();
        if ($this->handle === false) {
            throw new RuntimeException("Failed to prepare HTTP client");
        }
    }

    public function close()
    {
        if ($this->handle === false) {
            return;
        }
        curl_close($this->handle);
    }

    public function produceFormat(): int
    {
        return self::PRODUCE_TICK;
    }

    public function consumeFormat(): int
    {
        return self::CONSUME_NONE;
    }

    private function readTicksFromOneFile()
    {
        $ticks = [];

        $cacheFile = $this->buildCacheFilename();
        if (!is_readable($cacheFile)) {
            $this->downloadcurrentHourFile();
        }
        if (!file_exists($cacheFile)) {
            // Failed to dowload data (or save it)
            return [];
        }
        $binData = file_get_contents($cacheFile);
        $size = strlen($binData);
        if ($size == 0) {
            throw new RuntimeException("Failed to read cached data for " . $this->instrument . " at " . $this->position->format('Y-m-d H:i:s'));
        }

        $hourTimestamp = $this->position->getTimestamp();
        $binData = gzuncompress($binData);
        $size = strlen($binData); // Use size of uncompressed data
        $idx = 0;
        while ($idx < $size) {
            //print "$idx $size\n";
            $q = unpack('@' . $idx . '/N', $binData);
            $deltat = $q[1];
            $timesec = (int) ($hourTimestamp + $deltat / 1000);
            $timems = $deltat % 1000;

            $q = unpack('@' . ($idx + 4) . "/N", $binData);
            $ask = $q[1] * $this->point;
            $q = unpack('@' . ($idx + 8) . "/N", $binData);
            $bid = $q[1] * $this->point;
            $q = unpack('@' . ($idx + 12) . "/C4", $binData);
            $s = pack('C4', $q[4], $q[3], $q[2], $q[1]);
            $q = unpack('f', $s);
            $askvol = $q[1] * 1000000; // Volume in millions in the source data
            $q = unpack('@' . ($idx + 16) . "/C4", $binData);
            $s = pack('C4', $q[4], $q[3], $q[2], $q[1]);
            $q = unpack('f', $s);
            $bidvol = $q[1] * 1000000; // Volume in millions in the source data

            if ($bid == intval($bid)) {
                $bid = number_format($bid, 1, '.', '');
            }
            if ($ask == intval($ask)) {
                $ask = number_format($ask, 1, '.', '');
            }
            // fwrite($outfd, gmstrftime("%Y.%m.%d %H:%M:%S", $timesec) . "." . str_pad($timems, 3, '0', STR_PAD_LEFT) . ",$bid,$ask," . number_format($bidvol, 2, '.', '') . "," . number_format($askvol, 2, '.', '') . "\n");
            $idx += 20;

            $date = DateTime::createFromFormat('U u', "$timesec $timems", new DateTimeZone('UTC'));
            $tick = new Tick();
            $tick->setDate($date)
                ->setAsk($ask)
                ->setBid($bid)
                ->setAskVolume($askvol)
                ->setBidVolume($bidvol);

            $this->position = clone $date;

            $ticks[] = $tick;
        }

        return $ticks;
    }

    public function readTicks(): Generator
    {
        $startPosition = $this->tell();
        while ($startPosition < $this->today) {
            foreach ($this->readTicksFromOneFile() as $tick) {
                yield $tick;
            }
            $startPosition->add(new DateInterval("PT1H"));

            $this->seek($startPosition);
        }
    }

    public function readBars(): Generator
    {
        throw new RuntimeException("Unsupported method " . __METHOD__);
    }

    public function readBar(): ?Bar
    {
        throw new RuntimeException("Unsupported method " . __METHOD__);
    }

    public function addBar(Bar $bar)
    {
        throw new RuntimeException("Unsupported method " . __METHOD__);
    }

    public function addTick(Bar $bar)
    {
        throw new RuntimeException("Unsupported method " . __METHOD__);
    }

    public function writeBar(Bar $bar)
    {
        throw new RuntimeException("Unsupported method " . __METHOD__);
    }

    /**
     * Get the URL to download the tick for the hour at the current position
     */
    private function buildUrl(): string
    {
        $pair = strtoupper($this->instrument);
        $year = $this->position->format('Y');
        // Don't ask why, months are numbered from 0 to 11 ...
        $month = sprintf("%02d", $this->position->format('m') - 1);
        $day = $this->position->format('d');
        $hour = $this->position->format('H');
        $url = "http://datafeed.dukascopy.com/datafeed/$pair/$year/$month/$day/{$hour}h_ticks.bi5";
        return $url;
    }

    /**
     * get filename to cach the tick data in a format usable without 3rd party tool
     * no native LZMA decompression available for PHP
     */
    private function buildCacheFilename(): string
    {
        $dir = [dirname(__DIR__)];
        $dir[] = 'cache';
        $dir[] = 'php';
        $dir[] = strtoupper($this->instrument);
        $dir[] = $this->position->format('Y');
        $dir[] = $this->position->format('m');
        $dir[] = $this->position->format('d');
        $dir[] = $this->position->format('H') . 'h_ticks.gz';
        return implode(DIRECTORY_SEPARATOR, $dir);
    }

    /**
     * Get the position in the stream as a timestamp and the bar
     */
    public function seek(Datetime $date)
    {
        $this->position = clone $date;
        $this->position->setTime($this->position->format('H'), 0, 0, 0);
    }

    public function seekLast()
    {
        $this->position = new Datetime('now', new DateTimeZone('UTC'));
        $this->position->setTime(0, 0, 0, 0);
    }

    public function seekFirst()
    {
        $timestamp = $this->getInstrumentStartTimestamp();
        $this->position = Datetime::createFromFormat('U', $timestamp, new DateTimeZone('UTC'));
        $this->position->setTime($this->position->format('H'), 0, 0, 0);
    }

    public function tell(): Datetime
    {
        return clone $this->position;
    }

    private function downloadcurrentHourFile()
    {
        $url = $this->buildUrl();
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_HEADER, 0);

        // Try a  few times to download the ticks
        $retryCount = 0;
        do {
            $result = curl_exec($this->handle);
            $retryCount++;
        } while ($retryCount <= 3 && curl_errno($this->handle));
        if (curl_errno($this->handle)) {
            throw new RuntimeException("Failed to download ticks from $url");
        }

        $httpResponse = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        switch ($httpResponse) {
            default:
                fwrite(STDERR, "Unhandled HTTP response code from $url : $httpResponse");
                $this->failedFileCount++;
                break;

            case 404:
                // Resource at URL does not exists
                $weekday = $this->position->format('N');
                if (in_array($weekday, [6, 7])) {
                    // saturday or sunday are not errors
                    fwrite(STDERR, "Info: missing week end tick data" . PHP_EOL);
                } else {
                    fwrite(STDERR, "WARNING: missing file $url" . PHP_EOL);
                }
                $this->missingFileCount++;
                break;
            case 200:
                // Download with success
                $resultSize = strlen($result);
                if ($resultSize < 1) {
                    // Resource at URL does not exists
                    $weekday = $this->position->format('N');
                    if (in_array($weekday, [6, 7])) {
                        // saturday or sunday are not errors
                        fwrite(STDERR, "Info: missing week end tick data" . PHP_EOL);
                        $this->saveToFile('');
                        return;
                    } else {
                        fwrite(STDERR, "WARNING: empty file $url" . PHP_EOL);
                        $this->saveToFile('');
                        return;
                    }
                }
                $binaryTicks = $this->unpackBi5($result);
                $this->saveToFile($binaryTicks);
        }
    }

    private function unpackBi5(string $bi5Data): string
    {
        $bi5Filename = $this->bi5TmpDir . DIRECTORY_SEPARATOR . $this->position->format('H') . 'h_ticks.bi5';
        if (!is_writable($bi5Filename)) {
            // throw new RuntimeException("Unable to write $bi5Filename");
        }
        $size = file_put_contents($bi5Filename, $bi5Data);
        if ($size != strlen($bi5Data)) {
            throw new RuntimeException("Failed to write temporary bi5 file");
        }

        if ($this->iswindows) {
            $cmd = sprintf($this->extractCommand, $this->rawTmpDir, $bi5Filename);
            shell_exec($cmd);
            $extracted = $this->rawTmpDir . DIRECTORY_SEPARATOR . basename($bi5Filename, '.bi5');
            if (!file_exists($extracted)) {
                throw new RuntimeException("Failed to extract temorary bi5 file");
            }
            $bin = file_get_contents($extracted);
            unlink($extracted);

            return $bin;
        }

        // Non-windows platforms
        $cmd = sprintf($this->extractCommand, $bi5Filename);
        $bin = shell_exec($cmd);
        if (strlen($bin) == 0) {
            throw new RuntimeException("Failed to extract temporary bi5 file");
        }

        if (strlen($bin) % 20 != 0 ) {
            fwrite(STDERR, "Warning: downlaod has an incorrect size: " . strlen($bin) . PHP_EOL);
        }

        return $bin;
    }

    private function saveToFile(string $binaryTicks)
    {
        $filename = $this->buildCacheFilename();
        @mkdir(dirname($filename), 0774, true);
        $gz = gzcompress($binaryTicks);
        $size = file_put_contents($filename, $gz);
        if ($size != strlen($gz)) {
            throw new RuntimeException("Failed write cache");
        }
    }

    /**
     * get timestamp of the first tick data for the instrument
     */
    private function getInstrumentStartTimestamp()
    {
        global $CONFIG;

        return $CONFIG->getInstrumentStartTimestamp($this->instrument)['begin'];
    }

    /**
     * get point for instrument
     */
    private function getPoint()
    {
        $point = 0.00001;
        if (
            stripos($this->instrument, 'jpy') !== false ||
            strcasecmp($this->instrument, 'usdrub') == 0 ||
            strcasecmp($this->instrument, 'xagusd') == 0 ||
            strcasecmp($this->instrument, 'xauusd') == 0
        ) {
            $point = 0.001;
        } else if (stripos($this->instrument, 'rub') !== false) {
            $point = 0.001;
        }

        return $point;
    }

    private function prepareLzmaExtractCommand()
    {
        $extract = '';
        $this->iswindows = false;
        if (stripos(PHP_OS, 'win') === false || stripos(PHP_OS, 'darwin') !== false) {
            exec('lzma -h 2>/dev/null', $output);
            if (count($output) > 0) {
                $extract = 'lzma -kdc -S bi5 %s';
            } else {
                exec('xz -h 2>/dev/null', $output);
                if (count($output) > 0) {
                    $extract = 'xz -dc %s';
                }
            }
        } else {
            $this->iswindows = true;
            exec('7za 2>NUL', $output);
            if (count($output) > 0) {
                $extract = '7za e -o"%s" %s';
            }
        }

        $this->extractCommand = $extract;
    }

    public function setServerName(string $serveRName) {}

    public function getMinLotSize() {}

    public function setMinLotSize($minLotSize) {}

    public function getMaxLotSize() {}

    public function setMaxLotSize($maxLotSize) {}

    public function getLotStep() {}

    public function setLotStep($lotStep) {}
    public function setStopLevel($stopLevel) {}

    private array $instrumentData = [];
}
