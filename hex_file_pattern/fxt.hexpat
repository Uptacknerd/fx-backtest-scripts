#pragma description Metaquotes Metatrader 4 Tick data (FXT file)
#pragma URL https://www.metatrader4.com/en/trading-platform/help/autotrading/tester/tester_fxt

#pragma endian little

struct Header {
    u32    Version;
    char   Copyright[64];
    char   ServerName[128];
    char   Symbol[12];
    u32    Timferame;
    u32    TickModel;
    u32    BarsCount;
    u32    StartDate;
    u32    EndDate;
    u32    Padding1;
    double ModelingQuality;
    char   BaseCurrency[12];
    u32    Spread;
    u32    Digits;
    u32    Padding2;
    double PointSize;
    u32    MinLot;
    u32    MaxLot;
    u32    LotStep;
    u32    StopLevel;
    u32    OrderExpiration;

    u32    Padding3;

    double ContractSize;
    double TickValue;
    double TickSize;
    u32    ProfitCalculationMode;

    u32    SwapMode;
    u32    SwapCalculation;
    u32    Padding4;
    double SwapBuy;
    double SwapSell;
    u32    ThreeDaysSwap;

    u32    Leverage;

    u32    FreeMarginCalculation;
    u32    MarginCalculation;
    u32    MarginStopoutLevel;
    u32    MarginCheckMode;
    double MarginRequirement;
    double MarginMaintenanceRequirement;
    double MarginHedgeRequirement;
    double MarginDivider;
    char   MarginCurrency[12];

    u32    Padding5;

    double BasicCommission;
    u32    CommissionType;
    u32    Commission;
    u32    FirstModelingBarIndex;
    u32    LastModelingBarIndex;
    u32    ModelingBarIndex_M1;
    u32    ModelingBarIndex_M5;
    u32    ModelingBarIndex_M15;
    u32    ModelingBarIndex_M30;
    u32    ModelingBarIndex_H1;
    u32    ModelingBarIndex_H4;

    u32    TesterBeginDate; // Must be 0
    u32    TesterEndDate;   // Must be 0

    u32     OrderFreezeLevel;
    u32     GenerationErrorsCount;
};

struct Record {
    u32    OpeningDate;
    u32    Padding;
    double Open;
    double High;
    double Low;
    double Close;
    u64    Volume;
    u32    TickDate;
    u32    Flag;
};

struct FXT {
    Header header;

    u32    Padding[60];

    Record records[header.BarsCount];
};

FXT fxt @ 0x00;