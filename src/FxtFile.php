<?php

/**
 *
 */

namespace Uptacknerd\FxBtScripts;

use RuntimeException;
use Datetime;
use DateTimeZone;
use Generator;

/**
 * FXT file format specification
 * Numbers are little endian
 * @link https://www.mql5.com/fr/code/viewcode/8648/46621/FXTHeader.mqh
 */
class FxtFile extends AbstractFile
{
    const LITTLE_ENDIAN_U_INT32 = 'L';
    const LITTLE_ENDIAN_INT32 = 'l';
    const LITTLE_ENDIAN_DOUBLE_FLOAT = 'e';
    const LITTLE_ENDIAN_U_INT64 = 'P';

    const TICK_MODEL_TICK = 0;
    const TICK_MODEL_CONTROL_POINT = 1;
    const TICK_MODEL_BAR_OPEN = 2;

    const ORDER_EXPIRATION_GTC = 1; // Good until cancelled

    const PROFIT_CALCULATION_FOREX = 0;
    const PROFIT_CALCULATION_CFD = 1;
    const PROFIT_CALCULATION_FUTURE = 2;

    const SWAP_CALCULATION_POINT = 0;
    const SWAP_CALCULATION_BASE_CURRENCY = 1;
    const SWAP_CALCULATION_INTEREST = 2;
    const SWAP_CALCULATION_MARGIN_CURRENCY = 3;

    const FREE_MARGIN_CALCULATION_NO = 0;
    const FREE_MARGIN_CALCULATION_ALL = 1;
    const FREE_MARGIN_CALCULATION_PROFIT = 2;
    const FREE_MARGIN_CALCULATION_LOSS = 3;

    const MARGIN_CALCULATION_MODE_FOREX = 0;
    const MARGIN_CALCULATION_MODE_CFD = 1;
    const MARGIN_CALCULATION_MODE_FUTURE = 2;
    const MARGIN_CALCULATION_MODE_CFD_INDEXES = 3;

    const MARGIN_TYPE_PERCENT = 0;
    const MARGIN_TYPE_CURRENCY = 1;

    const BASIC_COMMISSION_TYPE_MONEY = 0;
    const BASIC_COMMISSION_TYPE_PIPS = 1;
    const BASIC_COMMISSION_TYPE_PERCENT = 2;

    const COMMISSION_PER_LOT = 0;
    const COMMISSION_DEAL = 1;

    const RECORD_SIZE = 56;


    /** Tick record related constants
     * @link https://www.mql5.com/en/forum/75268
     */
    // Not sure it applies to MT4.
    const TICK_FLAG_BID     = 2;
    const TICK_FLAG_ASK     = 4;
    const TICK_FLAG_LAST    = 8;
    const TICK_FLAG_VOLUME  = 16;
    const TICK_FLAG_BUY     = 32;
    const TICK_FLAG_SELL    = 32;

    /**
     * Is the header written to the file ?
     */
    private bool $headerSent = false;

    // Header definition
    private int      $version = 405;
    private string   $copyright = '(C)opyright 2006, MetaQuotes Software Corp.';
    private string   $serverName = 'Default';
    protected string $symbol = '';
    protected int    $timeframe = 60; // M1
    private int      $tickModel = self::TICK_MODEL_TICK;
    private float    $quality = 99.9; // max is 99.9
    private string   $baseCurrency = 'USD';
    private int      $spread = 0; // in points
    private int      $digits = 5;
    private float    $pointSize = 0.00001;
    private int      $minLotSize = 1;     // in hundredths (1 means 0.01 lot)
    private int      $maxLotSize = 50000; // in hundredths (1 means 0.01 lot)
    private int      $lotStep    = 1;     // in hundredths (1 means 0.01 lot)
    private int      $stopLevel  = 0;     // Stops level value (orders stop distance in points).
    private int      $orderExpiration = self::ORDER_EXPIRATION_GTC;
    private float    $contractSize = 100000;
    private float    $tickValue = 0;      // Tick value in quote currency (empty).
    private float    $tickSize = 0;       // Size of one tick (empty).
    private int      $profitCalculation = self::PROFIT_CALCULATION_FOREX;
    private bool     $enableSwap = false;
    private int      $swapCalculation = self::SWAP_CALCULATION_POINT;
    private float    $swapBuy  = 0;       // Swap of the buy order - long overnight swap value
    private float    $swapSell = 0;       // Swap of the sell order - short overnight swap value
    private int      $threeDaysSwap = 3;  // 1 monday, 2 tuesday, 3 wednesday, and so on.
    private int      $leverage = 100;     // Account leverage
    private int      $freeMarginCalculation = self::FREE_MARGIN_CALCULATION_ALL;
    private int      $marginCalculation = self::MARGIN_CALCULATION_MODE_FOREX;
    private int      $marginStopoutLevel = 30;
    private int      $marginCheckMode = self::MARGIN_TYPE_PERCENT;
    private float    $marginRequirement = 0;
    private float    $marginMaintenanceRequirement = 0;
    private float    $marginHedgeRequirement = 50000;
    private float    $marginDivider = 1.25;
    private string   $marginCurrency = 'USD';

    private float    $basicCommission = 0;
    private int      $commissionType = self::BASIC_COMMISSION_TYPE_PIPS;
    private int      $commission = self::COMMISSION_PER_LOT;

    private int      $firstModelingBarIndex = 1; // Index of the first bar at which modeling started (0 for the first bar).
    private int      $LasttModelingBarIndex = 0; // Index of the last bar at which modeling started (0 for the last bar).
    private int      $ModelingBarIndexM1 = 0;    // Bar index where modeling started using M1 bars (0 for the first bar).
    private int      $ModelingBarIndexM5 = 0;    // Bar index where modeling started using M5 bars (0 for the first bar).
    private int      $ModelingBarIndexM15 = 0;   // Bar index where modeling started using M15 bars (0 for the first bar).
    private int      $ModelingBarIndexM30 = 0;   // Bar index where modeling started using M30 bars (0 for the first bar).
    private int      $ModelingBarIndexH1 = 0;    // Bar index where modeling started using H1 bars (0 for the first bar).
    private int      $ModelingBarIndexH4 = 0;    // Bar index where modeling started using H4 bars (0 for the first bar).
    private int      $orderFreezeLevel = 0;      // Order's freeze level in points.
    private int      $generationErrorsCount = 0;

    private Datetime $today;

    public function __construct(string $filename, bool $detectTimeframe = false)
    {
        parent::__construct($filename, $detectTimeframe);
        $this->startDate = new Datetime('now', new DateTimeZone('GMT'));
        $this->endDate = new Datetime('now', new DateTimeZone('GMT'));

        $this->today = new Datetime('now', new DateTimeZone('UTC'));
        $this->today->setTime(0, 0, 0, 0);
    }

    /*
    public function isInjectable(): ?string {
        return 'MT4';
    }

    public function suggestFilename(): string {
        $timeframe = $this->getTimeframe();

        $symbol = $this->getSymbol();

        // switch ($this->getTickModel()) {
        //     case self::TICK_MODEL_TICK:
        //         break;
        //     case self::TICK_MODEL_CONTROL_POINT:
        //         break;
        //     case self::TICK_MODEL_BAR_OPEN:
        //         break;
        // }
        $model = $this->getTickModel();

        return sprintf("%s%s_%s.fxt", $symbol, $timeframe / 60, $model);
    }
    */

    public function setOptions($args): bool {
        return true;
    }

    // public function setOptions($args)
    // {
    //     if (isset($args['--model'])) {
    //         switch (strtolower($args['--model'])) {
    //             case 'tick':
    //                 $this->setTickModel(self::TICK_MODEL_TICK);
    //                 break;

    //             case 'point':
    //                 $this->setTickModel(self::TICK_MODEL_CONTROL_POINT);
    //                 break;

    //             case 'open':
    //                 $this->setTickModel(self::TICK_MODEL_BAR_OPEN);
    //                 break;
    //         }
    //     }

    //     if (!isset($args['--symbol'])) {
    //         throw new RuntimeException("Symbol specification is mandatory");
    //     }
    //     $this->setSymbol($args['--symbol']);

    //     if (!isset($args['--timeframe'])) {
    //         throw new RuntimeException("Timeframe specification is mandatory");
    //     }
    //     $timeframe = strtolower($args['--timeframe']);
    //     $timeframe = Timeframe::convertTimeframe($timeframe);
    //     $this->setTimeframe(Timeframe::convertTimeframe($args['--timeframe']));

    //     if (!isset($args['--model'])) {
    //         throw new RuntimeException("Model specification is mandatory");
    //     }
    //     switch ($args['--model']) {
    //         case 'tick':
    //             $this->setTickModel(self::TICK_MODEL_TICK);
    //             break;

    //         case 'control-point':
    //             $this->setTickModel(self::TICK_MODEL_CONTROL_POINT);
    //             break;

    //         case 'open-point':
    //             $this->setTickModel(self::TICK_MODEL_BAR_OPEN);
    //             break;

    //         default:
    //             throw new RuntimeException("Invalid model specification. Use tick, control-point or open-point");;
    //     }

    //     if (isset($args['--spread'])) {
    //         $this->setSpread($args['--spread']);
    //     }
    // }

    public function open(string $mode = 'r')
    {
        parent::open($mode);
        switch ($mode) {
            case 'w':
                if (fwrite($this->handle, $this->getHeader()) < 728) {
                    throw new RuntimeException("Failed to write header");
                };
                break;

            case 'r':
            case 'w+':
                $headerSize = strlen($this->getHeader());
                fseek($this->handle, 0);
                if  (filesize($this->getFilename()) >= $headerSize) {
                    $header = fread($this->handle, $headerSize);
                    if (strlen($header) > 0 && strlen($header) < $headerSize) {
                        throw new RuntimeException("Failed to read header");
                    };
                    if (strlen($header) == $headerSize) {
                        $this->readHeader($header);
                    }
                } else {
                    if (fwrite($this->handle, $this->getHeader()) < $headerSize) {
                        throw new RuntimeException("Failed to write header");
                    }
                }
                break;
        }
    }

    public function close() {
        parent::close();
        chmod($this->getFilename(), 0444);
    }

    /**
     * Get the FXT header
     */
    public function getHeader(): string
    {
        $header = '';
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->version);
        $header .= mb_convert_encoding(str_pad(substr($this->copyright, 0, 64), 64, "\x00"), 'ASCII');
        $header .= mb_convert_encoding(str_pad(substr($this->serverName, 0, 128), 128, "\x00"), 'ASCII');
        $header .= mb_convert_encoding(str_pad(substr(strtoupper($this->symbol), 0, 12), 12, "\x00"), 'ASCII');
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, (int) ($this->timeframe / 60)); // Timeframe in minute
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->tickModel);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->barsCount);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->startDate->getTimestamp());
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->endDate->getTimestamp());
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // 4 bytes Padding. This potentially can be totalTicks
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->quality);

        //  General parameters
        $header .= mb_convert_encoding(str_pad(substr($this->baseCurrency, 0, 12), 12, "\x00"), 'ASCII');
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->spread); // Not propagated from cmd line yet
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->digits);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // 4 bytes Padding

        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->pointSize);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->minLotSize);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->maxLotSize);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->lotStep);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->stopLevel);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->orderExpiration);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // 4 bytes Padding

        // Profit Calculation parameters
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->contractSize);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->tickValue);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->tickSize);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->profitCalculation);

        // Swap calculation
        $header .= pack(self::LITTLE_ENDIAN_INT32, $this->enableSwap ? 1 : 0);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->swapCalculation);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // 4 bytes Padding
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->swapBuy);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->swapSell);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->threeDaysSwap);

        // Margin calculation
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->leverage);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->freeMarginCalculation);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->marginCalculation);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->marginStopoutLevel);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->marginCheckMode);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->marginRequirement);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->marginMaintenanceRequirement);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->marginHedgeRequirement);
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->marginDivider);
        $header .= mb_convert_encoding(str_pad(substr($this->marginCurrency, 0, 12), 12, "\x00"), 'ASCII');
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // 4 bytes Padding

        // Commission calculation
        $header .= pack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $this->basicCommission);
        $header .= pack(self::LITTLE_ENDIAN_INT32, $this->commissionType);
        $header .= pack(self::LITTLE_ENDIAN_INT32, $this->commission);

        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->firstModelingBarIndex);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->LasttModelingBarIndex);

        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->ModelingBarIndexM1);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->ModelingBarIndexM5);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->ModelingBarIndexM15);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->ModelingBarIndexM30);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->ModelingBarIndexH1);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->ModelingBarIndexH4);

        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // Begin date from tester settings (must be zero).
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0); // End date from tester settings (must be zero).

        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->orderFreezeLevel);
        $header .= pack(self::LITTLE_ENDIAN_U_INT32, $this->generationErrorsCount);

        for ($i = 0; $i < 60; $i++) {
            $header .= pack(self::LITTLE_ENDIAN_U_INT32, 0);
        }
        if (strlen($header) != 240 + 56 + 28 + 32 + 68 + 16 + 8 + 24 + 8 + 8 + 240) {
            throw new RuntimeException("Header build error: wrong size in checkpoint 1");
        }
        return $header;
    }

    private function readHeader(string $value)
    {
        if ($this->handle === false) {
            throw new RuntimeException("unable to read header from not opened file");
        }

        // Detect header size from code to generate it, to ensure consistency
        $headerSize = strlen($this->getHeader());

        // Version
        if (strlen($value) != $headerSize) {
            throw new RuntimeException("Unexpected end of file " . $this->getFilename());
        }
        $offset = 0;

        $this->setVersion(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setCopyright(rtrim(mb_convert_encoding(substr($value, $offset, 64), 'auto', 'ASCII')));
        $offset += 64;

        $this->setServerName(rtrim(mb_convert_encoding(substr($value, $offset, 128), 'auto', 'ASCII')));
        $offset += 128;

        $this->setSymbol(rtrim(mb_convert_encoding(substr($value, $offset, 12), 'auto', 'ASCII')));
        $offset += 12;

        $this->setTimeframe(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset) * 60); // Convert to seconds unit
        $offset += 4;

        $this->setTickModel(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setBarsCount(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setStartDate(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setEndDate(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        // 4 bytes padding
        $offset += 4;

        $this->setQuality(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));

        $this->setBaseCurrency(rtrim(mb_convert_encoding(substr($value, $offset, 12), 'auto', 'ASCII')));
        $offset += 12;

        $this->setSpread(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setDigits(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        // 4 bytes padding
        $offset += 4;

        $this->setPointsize(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setMinLotSize(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setMaxLotSize(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setLotStep(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setStopLevel(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setOrderExpiration(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        // 4 bytes padding
        $offset += 4;

        $this->setContractSize(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setTickValue(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setTickSize(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setProfitCalculation(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setEnableSwap(unpack(self::LITTLE_ENDIAN_INT32, $value, $offset));
        $offset += 4;

        $this->setSwapCalculation(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        // 4 bytes padding
        $offset += 4;

        $this->setSwapBuy(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setSwapSell(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setThreeDaysSwap(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setLeverage(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setFreeMarginCalculation(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setMarginCalculation(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setMarginStopoutLevel(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setMarginCheckMode(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setMarginRequirement(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setMarginMaintenanceRequirement(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setMarginHedgeRequirement(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setMarginDivider(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setMarginCurrency(rtrim(mb_convert_encoding(substr($value, $offset, 12), 'auto', 'ASCII')));
        $offset += 12;

        // 4 bytes padding
        $offset += 4;

        $this->setBasicCommission(unpack(self::LITTLE_ENDIAN_DOUBLE_FLOAT, $value, $offset));
        $offset += 8;

        $this->setCommissionType(unpack(self::LITTLE_ENDIAN_INT32, $value, $offset));
        $offset += 4;

        $this->setCommission(unpack(self::LITTLE_ENDIAN_INT32, $value, $offset));
        $offset += 4;

        $this->setFirstModelingBarIndex(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setLasttModelingBarIndex(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setModelingBarIndexM1(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setModelingBarIndexM15(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setModelingBarIndexM30(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setModelingBarIndexH1(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;

        $this->setModelingBarIndexH4(unpack(self::LITTLE_ENDIAN_U_INT32, $value, $offset));
        $offset += 4;
    }

    public function produceFormat(): int
    {
        return self::PRODUCE_BAR;
    }

    public function consumeFormat(): int
    {
        return self::CONSUME_BOTH;
    }

    /**
     * Not tested
     */
    public function seek(Datetime $date)
    {
        // Round date to midnight
        $date->setTime(0, 0, 0, 0);

        // Initialize dichotomic interval
        $minPosition = strlen($this->getHeader());
        $maxPosition = filesize($this->getFilename()) - self::RECORD_SIZE; // Last record (a record is 56 bytes length)
        if (($maxPosition - $minPosition) % self::RECORD_SIZE != 0) {
            throw new RuntimeException("Incorrect filesize: header and record size don't match");
        }
        if ($minPosition == $maxPosition) {
            // empty file
            return null;
        }

        // Read line of interval start
        fseek($this->handle, $minPosition);

        // Read line of interval end
        fseek($this->handle, $maxPosition);
        /** @var Bar $endBar */
        $endBar = $this->readBar();

        while ($minPosition != $maxPosition) {
            $recordCount = ($maxPosition - $minPosition) / 56; // 56 bytes per record
            $middlePosition = (int) ($recordCount / 2);
            $middlePosition = strlen($this->getHeader()) + $middlePosition;
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
    }

    /**
     * Seek to first record of the file
     *
     * @return void
     */
    public function seekFirst()
    {
        $minPosition = strlen($this->getHeader());
        fseek($this->handle, $minPosition);
    }

    /**
     * Seek to last record of the file
     *
     * @return void
     */
    public function seekLast()
    {
        $minPosition = strlen($this->getHeader());
        $maxPosition = filesize($this->getFilename()) - 56; // Last record (a record is 56 bytes length)
        if (($maxPosition - $minPosition) % self::RECORD_SIZE != 0) {
            throw new RuntimeException("Incorrect filesize: header and record size don't match");
        }
        fseek($this->handle, $maxPosition);
    }

    public function tell(): Datetime
    {
        $position = ftell($this->handle);
        $date = new Datetime();
        $bar = $this->readBar();
        $date = $bar->getOpenDate();
        fseek($this->handle, $position);

        return $date;
    }

    public function readTicks(): Generator
    {
        throw new RuntimeException("Not implemented");
        if ($this->timeframe != 0) {
            throw new RuntimeException("Does not contains ticks");
        }
    }

    public function readTick(): ?Bar {
        // FXT file contains bars. A tick is modelized as a bar
        return $this->readBar();
    }

    public function readBars(): Generator
    {
        while ($this->tell() < $this->today) {
            yield $this->readBar();
        }
    }

    public function readBar(): ?Bar
    {
        $position = ftell($this->handle);
        $record = fread($this->handle, self::RECORD_SIZE);
        if (strlen($record) != self::RECORD_SIZE) {
            $error = sprintf(
                "Failed to read a record in %s at %i bytes. Expected %i bytes, got %i bytes.",
                $this->getFilename(),
                $position,
                self::RECORD_SIZE,
                strlen($record)
            );
            throw new RuntimeException($error);
        }

        // Decode the bar

        $format =
        // format                               // fieldname (as key in output array)
        self::LITTLE_ENDIAN_U_INT32           . 'openDate/' .
        self::LITTLE_ENDIAN_U_INT32           . 'padding/' .
        self::LITTLE_ENDIAN_DOUBLE_FLOAT      . 'open/' .
        self::LITTLE_ENDIAN_DOUBLE_FLOAT      . 'high/' .
        self::LITTLE_ENDIAN_DOUBLE_FLOAT      . 'low/' .
        self::LITTLE_ENDIAN_DOUBLE_FLOAT      . 'close/' .
        self::LITTLE_ENDIAN_U_INT64           . 'volume/' .
        self::LITTLE_ENDIAN_U_INT32           . 'tickDate/' .
        self::LITTLE_ENDIAN_U_INT32           . 'flag';

        $fields = unpack($format, $record);

        // Compute timeframe
        $timeframe = $this->getTimeframe();
        $bar = new Bar($timeframe);

        $bar->setOpenDate(Datetime::createFromFormat('U', $fields['openDate'], new DateTimeZone('UTC')))
            ->setOpen($fields['open'])
            ->setHigh($fields['high'])
            ->setLow($fields['low'])
            ->setVolume($fields['volume'])
            ->setTickDate(Datetime::createFromFormat('U', $fields['tickDate'], new DateTimeZone('UTC')));

        return $bar;
    }

    public function addBar(Bar $bar)
    {
    }

    /**
     * Write the current bar with its tick timestamp
     */
    public function addTick(Bar $bar, Tick $tick)
    {
        $format = self::LITTLE_ENDIAN_U_INT32 .
            self::LITTLE_ENDIAN_U_INT32 .
            self::LITTLE_ENDIAN_DOUBLE_FLOAT .
            self::LITTLE_ENDIAN_DOUBLE_FLOAT .
            self::LITTLE_ENDIAN_DOUBLE_FLOAT .
            self::LITTLE_ENDIAN_DOUBLE_FLOAT .
            self::LITTLE_ENDIAN_U_INT64 .
            self::LITTLE_ENDIAN_U_INT32 .
            self::LITTLE_ENDIAN_U_INT32;

        fwrite(
            $this->handle,
            pack(
                $format,
                $bar->getOpenDate()->getTimestamp(),
                0,
                $bar->getOpen(),
                $bar->getHigh(),
                $bar->getLow(),
                $bar->getClose(),
                max($bar->getVolume(), 1),
                $bar->getTickDate()->getTimestamp(),
                4 // A flag telling how to wake the EA. Unclear at the moment. Values 0 and 4 observed in original tool and other tools
            )
        );
    }

    public function finish()
    {
        // Adjust start and end dates
        $this->seekFirst();
        $this->firstBar = $this->readBar();
        $this->setStartDate($this->firstBar->getOpenDate());

        $this->seekLast();
        $this->lastBar = $this->readBar();
        $this->setEndDate($this->lastBar->getCloseDate());

        $this->setBarsCount($this->barsCount);

        fseek($this->handle, 0);
        fwrite($this->handle, $this->getHeader());

        $finalSize = filesize($this->getFilename());
        if (($finalSize - strlen($this->getHeader())) % self::RECORD_SIZE != 0) {
            throw new RuntimeException("Output file size does not match header + an integer number of records ");
        }
    }

    /**
     * Get the value of serverName
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Set the value of serverName
     *
     * @return  self
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;

        return $this;
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
        global $CONFIG;

        $this->symbol = $symbol;
        $symbol = substr($symbol, 0, 6);
        $this->setServerName($CONFIG->getServer('vfx'));
        $this->setDigits($CONFIG->getDigits('vfx', $symbol));
        $this->setTickSize($CONFIG->getTickSize('vfx', $symbol));
        $this->setTickValue($CONFIG->getTickValue('vfx', $symbol));
        $this->setPointSize($CONFIG->getPointSize('vfx', $symbol));
        $this->setMinLotSize($CONFIG->getMinLot('vfx', $symbol));
        $this->setMaxLotSize($CONFIG->getMaxLot('vfx', $symbol));
        $this->setLotStep($CONFIG->getLotStep('vfx', $symbol));
        $this->setStopLevel($CONFIG->getStopLevel('vfx', $symbol));
        $this->setBaseCurrency($CONFIG->getBaseCurrency('vfx', $symbol));
        $this->setMarginCurrency($CONFIG->getBaseCurrency('vfx', $symbol));
        $spread = $args['--spread'] ?? $CONFIG->getSpread('vfx', $symbol);
        $this->setSpread($spread);
        $this->setContractSize($CONFIG->getContractSize('vfx', $symbol));
        return $this;
    }

    /**
     * Get the value of tickModel
     */
    public function getTickModel()
    {
        return $this->tickModel;
    }

    /**
     * Set the value of tickModel
     *
     * @return  self
     */
    public function setTickModel($tickModel)
    {
        $this->tickModel = $tickModel;

        return $this;
    }



    /**
     * Get the value of baseCurrency
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * Set the value of baseCurrency
     *
     * @return  self
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    /**
     * Get the value of minLotSize
     */
    public function getMinLotSize()
    {
        return $this->minLotSize;
    }

    /**
     * Set the value of minLotSize
     *
     * @return  self
     */
    public function setMinLotSize($minLotSize)
    {
        $this->minLotSize = $minLotSize * 100;

        return $this;
    }

    /**
     * Get the value of maxLotSize
     */
    public function getMaxLotSize()
    {
        return $this->maxLotSize;
    }

    /**
     * Set the value of maxLotSize
     *
     * @return  self
     */
    public function setMaxLotSize($maxLotSize)
    {
        $this->maxLotSize = $maxLotSize * 100;

        return $this;
    }

    /**
     * Get the value of lotStep
     */
    public function getLotStep()
    {
        return $this->lotStep;
    }

    /**
     * Set the value of lotStep
     *
     * @return  self
     */
    public function setLotStep($lotStep)
    {
        $this->lotStep = $lotStep * 100;

        return $this;
    }

    /**
     * Get the value of stopLevel
     */
    public function getStopLevel()
    {
        return $this->stopLevel;
    }

    /**
     * Set the value of stopLevel
     *
     * @return  self
     */
    public function setStopLevel($stopLevel)
    {
        $this->stopLevel = $stopLevel;

        return $this;
    }

    /**
     * Get the value of orderExpiration
     */
    public function getOrderExpiration()
    {
        return $this->orderExpiration;
    }

    /**
     * Set the value of orderExpiration
     *
     * @return  self
     */
    public function setOrderExpiration($orderExpiration)
    {
        $this->orderExpiration = $orderExpiration;

        return $this;
    }

    /**
     * Get the value of contractSize
     */
    public function getContractSize()
    {
        return $this->contractSize;
    }

    /**
     * Set the value of contractSize
     *
     * @return  self
     */
    public function setContractSize($contractSize)
    {
        $this->contractSize = $contractSize;

        return $this;
    }

    /**
     * Get the value of tickValue
     */
    public function getTickValue()
    {
        return $this->tickValue;
    }

    /**
     * Set the value of tickValue
     *
     * @return  self
     */
    public function setTickValue($tickValue)
    {
        $this->tickValue = $tickValue;

        return $this;
    }

    /**
     * Get the value of tickSize
     */
    public function getTickSize()
    {
        return $this->tickSize;
    }

    /**
     * Set the value of tickSize
     *
     * @return  self
     */
    public function setTickSize($tickSize)
    {
        $this->tickSize = $tickSize;

        return $this;
    }

    /**
     * Get the value of profitCalculation
     */
    public function getProfitCalculation()
    {
        return $this->profitCalculation;
    }

    /**
     * Set the value of profitCalculation
     *
     * @return  self
     */
    public function setProfitCalculation($profitCalculation)
    {
        $this->profitCalculation = $profitCalculation;

        return $this;
    }

    /**
     * Get the value of enableSwap
     */
    public function getEnableSwap()
    {
        return $this->enableSwap;
    }

    /**
     * Set the value of enableSwap
     *
     * @return  self
     */
    public function setEnableSwap($enableSwap)
    {
        $this->enableSwap = $enableSwap;

        return $this;
    }

    /**
     * Get the value of swapCalculation
     */
    public function getSwapCalculation()
    {
        return $this->swapCalculation;
    }

    /**
     * Set the value of swapCalculation
     *
     * @return  self
     */
    public function setSwapCalculation($swapCalculation)
    {
        $this->swapCalculation = $swapCalculation;

        return $this;
    }

    /**
     * Get the value of swapBuy
     */
    public function getSwapBuy()
    {
        return $this->swapBuy;
    }

    /**
     * Set the value of swapBuy
     *
     * @return  self
     */
    public function setSwapBuy($swapBuy)
    {
        $this->swapBuy = $swapBuy;

        return $this;
    }

    /**
     * Get the value of swapSell
     */
    public function getSwapSell()
    {
        return $this->swapSell;
    }

    /**
     * Set the value of swapSell
     *
     * @return  self
     */
    public function setSwapSell($swapSell)
    {
        $this->swapSell = $swapSell;

        return $this;
    }

    /**
     * Get the value of threeDaysSwap
     */
    public function getThreeDaysSwap()
    {
        return $this->threeDaysSwap;
    }

    /**
     * Set the value of threeDaysSwap
     *
     * @return  self
     */
    public function setThreeDaysSwap($threeDaysSwap)
    {
        $this->threeDaysSwap = $threeDaysSwap;

        return $this;
    }

    /**
     * Get the value of leverage
     */
    public function getLeverage()
    {
        return $this->leverage;
    }

    /**
     * Set the value of leverage
     *
     * @return  self
     */
    public function setLeverage($leverage)
    {
        $this->leverage = $leverage;

        return $this;
    }

    /**
     * Get the value of freeMarginCalculation
     */
    public function getFreeMarginCalculation()
    {
        return $this->freeMarginCalculation;
    }

    /**
     * Set the value of freeMarginCalculation
     *
     * @return  self
     */
    public function setFreeMarginCalculation($freeMarginCalculation)
    {
        $this->freeMarginCalculation = $freeMarginCalculation;

        return $this;
    }

    /**
     * Get the value of marginCalculation
     */
    public function getMarginCalculation()
    {
        return $this->marginCalculation;
    }

    /**
     * Set the value of marginCalculation
     *
     * @return  self
     */
    public function setMarginCalculation($marginCalculation)
    {
        $this->marginCalculation = $marginCalculation;

        return $this;
    }

    /**
     * Get the value of marginCheckMode
     */
    public function getMarginCheckMode()
    {
        return $this->marginCheckMode;
    }

    /**
     * Set the value of marginCheckMode
     *
     * @return  self
     */
    public function setMarginCheckMode($marginCheckMode)
    {
        $this->marginCheckMode = $marginCheckMode;

        return $this;
    }

    /**
     * Get the value of marginRequirement
     */
    public function getMarginRequirement()
    {
        return $this->marginRequirement;
    }

    /**
     * Set the value of marginRequirement
     *
     * @return  self
     */
    public function setMarginRequirement($marginRequirement)
    {
        $this->marginRequirement = $marginRequirement;

        return $this;
    }

    /**
     * Get the value of marginMaintenanceRequirement
     */
    public function getMarginMaintenanceRequirement()
    {
        return $this->marginMaintenanceRequirement;
    }

    /**
     * Set the value of marginMaintenanceRequirement
     *
     * @return  self
     */
    public function setMarginMaintenanceRequirement($marginMaintenanceRequirement)
    {
        $this->marginMaintenanceRequirement = $marginMaintenanceRequirement;

        return $this;
    }

    /**
     * Get the value of marginHedgeRequirement
     */
    public function getMarginHedgeRequirement()
    {
        return $this->marginHedgeRequirement;
    }

    /**
     * Set the value of marginHedgeRequirement
     *
     * @return  self
     */
    public function setMarginHedgeRequirement($marginHedgeRequirement)
    {
        $this->marginHedgeRequirement = $marginHedgeRequirement;

        return $this;
    }

    /**
     * Get the value of marginDivider
     */
    public function getMarginDivider()
    {
        return $this->marginDivider;
    }

    /**
     * Set the value of marginDivider
     *
     * @return  self
     */
    public function setMarginDivider($marginDivider)
    {
        $this->marginDivider = $marginDivider;

        return $this;
    }

    /**
     * Get the value of marginCurrency
     */
    public function getMarginCurrency()
    {
        return $this->marginCurrency;
    }

    /**
     * Set the value of marginCurrency
     *
     * @return  self
     */
    public function setMarginCurrency($marginCurrency)
    {
        $this->marginCurrency = $marginCurrency;

        return $this;
    }

    /**
     * Get the value of version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @return  self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the value of copyright
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Set the value of copyright
     *
     * @return  self
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * Get the value of quality
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set the value of quality
     *
     * @return  self
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get the value of spread
     */
    public function getSpread()
    {
        return $this->spread;
    }

    /**
     * Set the value of spread
     *
     * @return  self
     */
    public function setSpread($spread)
    {
        $this->spread = $spread;

        return $this;
    }

    /**
     * Get the value of digits
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * Set the value of digits
     *
     * @return  self
     */
    public function setDigits($digits)
    {
        $this->digits = $digits;

        return $this;
    }

    /**
     * Get the value of pointSize
     */
    public function getPointSize()
    {
        return $this->pointSize;
    }

    /**
     * Set the value of pointSize
     *
     * @return  self
     */
    public function setPointSize($pointSize)
    {
        $this->pointSize = $pointSize;

        return $this;
    }

    /**
     * Get the value of marginStopoutLevel
     */
    public function getMarginStopoutLevel()
    {
        return $this->marginStopoutLevel;
    }

    /**
     * Set the value of marginStopoutLevel
     *
     * @return  self
     */
    public function setMarginStopoutLevel($marginStopoutLevel)
    {
        $this->marginStopoutLevel = $marginStopoutLevel;

        return $this;
    }

    /**
     * Get the value of basicCommission
     */
    public function getBasicCommission()
    {
        return $this->basicCommission;
    }

    /**
     * Set the value of basicCommission
     *
     * @return  self
     */
    public function setBasicCommission($basicCommission)
    {
        $this->basicCommission = $basicCommission;

        return $this;
    }

    /**
     * Get the value of commissionType
     */
    public function getCommissionType()
    {
        return $this->commissionType;
    }

    /**
     * Set the value of commissionType
     *
     * @return  self
     */
    public function setCommissionType($commissionType)
    {
        $this->commissionType = $commissionType;

        return $this;
    }

    /**
     * Get the value of commission
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * Set the value of commission
     *
     * @return  self
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * Get the value of firstModelingBarIndex
     */
    public function getFirstModelingBarIndex()
    {
        return $this->firstModelingBarIndex;
    }

    /**
     * Set the value of firstModelingBarIndex
     *
     * @return  self
     */
    public function setFirstModelingBarIndex($firstModelingBarIndex)
    {
        $this->firstModelingBarIndex = $firstModelingBarIndex;

        return $this;
    }

    /**
     * Get the value of LasttModelingBarIndex
     */
    public function getLasttModelingBarIndex()
    {
        return $this->LasttModelingBarIndex;
    }

    /**
     * Set the value of LasttModelingBarIndex
     *
     * @return  self
     */
    public function setLasttModelingBarIndex($LasttModelingBarIndex)
    {
        $this->LasttModelingBarIndex = $LasttModelingBarIndex;

        return $this;
    }

    /**
     * Get the value of ModelingBarIndexM1
     */
    public function getModelingBarIndexM1()
    {
        return $this->ModelingBarIndexM1;
    }

    /**
     * Set the value of ModelingBarIndexM1
     *
     * @return  self
     */
    public function setModelingBarIndexM1($ModelingBarIndexM1)
    {
        $this->ModelingBarIndexM1 = $ModelingBarIndexM1;

        return $this;
    }

    /**
     * Get the value of ModelingBarIndexM5
     */
    public function getModelingBarIndexM5()
    {
        return $this->ModelingBarIndexM5;
    }

    /**
     * Set the value of ModelingBarIndexM5
     *
     * @return  self
     */
    public function setModelingBarIndexM5($ModelingBarIndexM5)
    {
        $this->ModelingBarIndexM5 = $ModelingBarIndexM5;

        return $this;
    }

    /**
     * Get the value of ModelingBarIndexM15
     */
    public function getModelingBarIndexM15()
    {
        return $this->ModelingBarIndexM15;
    }

    /**
     * Set the value of ModelingBarIndexM15
     *
     * @return  self
     */
    public function setModelingBarIndexM15($ModelingBarIndexM15)
    {
        $this->ModelingBarIndexM15 = $ModelingBarIndexM15;

        return $this;
    }

    /**
     * Get the value of ModelingBarIndexM30
     */
    public function getModelingBarIndexM30()
    {
        return $this->ModelingBarIndexM30;
    }

    /**
     * Set the value of ModelingBarIndexM30
     *
     * @return  self
     */
    public function setModelingBarIndexM30($ModelingBarIndexM30)
    {
        $this->ModelingBarIndexM30 = $ModelingBarIndexM30;

        return $this;
    }

    /**
     * Get the value of ModelingBarIndexH1
     */
    public function getModelingBarIndexH1()
    {
        return $this->ModelingBarIndexH1;
    }

    /**
     * Set the value of ModelingBarIndexH1
     *
     * @return  self
     */
    public function setModelingBarIndexH1($ModelingBarIndexH1)
    {
        $this->ModelingBarIndexH1 = $ModelingBarIndexH1;

        return $this;
    }

    /**
     * Get the value of ModelingBarIndexH4
     */
    public function getModelingBarIndexH4()
    {
        return $this->ModelingBarIndexH4;
    }

    /**
     * Set the value of ModelingBarIndexH4
     *
     * @return  self
     */
    public function setModelingBarIndexH4($ModelingBarIndexH4)
    {
        $this->ModelingBarIndexH4 = $ModelingBarIndexH4;

        return $this;
    }

    protected function detectPoint(): int {
        $pair = strtolower($this->getSymbol());

        $point = 0.00001;
        if (
            stripos($pair, 'jpy') !== false ||
            strcasecmp($pair, 'usdrub') == 0 ||
            strcasecmp($pair, 'xagusd') == 0 ||
            strcasecmp($pair, 'xauusd') == 0
        ) {
            $point = 0.001;
        } else if (stripos($pair, 'rub') !== false) {
            $point = 0.001;
        }
        return $point;
    }
}
