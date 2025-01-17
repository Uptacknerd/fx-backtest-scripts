<?php

/**
 * Example configuration file
 * Copy this file as config.php and edit its content to your taste
 */

return [
    'Metatrader-path' => '',
    'dukascopy' => [
        'symbols' => [
            "ADSDEEUR"      => [
                'begin' => 1426201200, // starting from 2015.03.13 00:00
            ],
            "ALVDEEUR"      => [
                'begin' => 1429394400, // starting from 2015.04.19 00:00
            ],
            "AUSIDXAUD"     => [
                'begin' => 1402524000, // starting from 2014.06.12 00:00
            ],
            "BASDEEUR"      => [
                'begin' => 1429678800, // starting from 2015.04.22 07:00
            ],
            "BAYNDEEUR"     => [
                'begin' => 1426806000, // starting from 2015.03.20 00:00
            ],
            "BEIDEEUR"      => [
                'begin' => 1428444000, // starting from 2015.04.08 00:00
            ],
            "BMWDEEUR"      => [
                'begin' => 1427151600, // starting from 2015.03.24 00:00
            ],
            "BRENTCMDUSD"   => [
                'begin' => 1291244400, // starting from 2010.12.02 00:00
            ],
            "CHEIDXCHF"     => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "CONDEEUR"      => [
                'begin' => 1428444000, // starting from 2015.04.08 00:00
            ],
            "DAIDEEUR"      => [
                'begin' => 1427410800, // starting from 2015.03.27 00:00
            ],
            "DB1DEEUR"      => [
                'begin' => 1428962400, // starting from 2015.04.14 00:00
            ],
            "DBKDEEUR"      => [
                'begin' => 1427238000, // starting from 2015.03.25 00:00
            ],
            "DEUIDXEUR"     => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "DTEDEEUR"      => [
                'begin' => 1427752800, // starting from 2015.03.31 00:00
            ],
            "EOANDEEUR"     => [
                'begin' => 1429480800, // starting from 2015.04.20 00:00
            ],
            "ESPIDXEUR"     => [
                'begin' => 1427238000, // starting from 2015.03.25 00:00
            ],
            "EUSIDXEUR"     => [
                'begin' => 1433196000, // starting from 2015.06.02 00:00
            ],
            "FMEDEEUR"      => [
                'begin' => 1427839200, // starting from 2015.04.01 00:00
            ],
            "FRAIDXEUR"     => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "FREDEEUR"      => [
                'begin' => 1428616800, // starting from 2015.04.10 00:00
            ],
            "HEIDEEUR"      => [
                'begin' => 1427925600, // starting from 2015.04.02 00:00
            ],
            "HEN3DEEUR"     => [
                'begin' => 1429567200, // starting from 2015.04.21 00:00
            ],
            "HKGIDXHKD"     => [
                'begin' => 1370210400, // starting from 2013.06.03 00:00
            ],
            "IFXDEEUR"      => [
                'begin' => 1428876000, // starting from 2015.04.13 00:00
            ],
            "JPNIDXJPY"     => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "LHADEEUR"      => [
                'begin' => 1429567200, // starting from 2015.04.21 00:00
            ],
            "LIGHTCMDUSD"   => [
                'begin' => 1427839200, // starting from 2015.04.01 00:00
            ],
            "LINDEEUR"      => [
                'begin' => 1429567200, // starting from 2015.04.21 00:00
            ],
            "LXSDEEUR"      => [
                'begin' => 1429567200, // starting from 2015.04.21 00:00
            ],
            "MRKDEEUR"      => [
                'begin' => 1427151600, // starting from 2015.03.24 00:00
            ],
            "MUV2DEEUR"     => [
                'begin' => 1429567200, // starting from 2015.04.21 00:00
            ],
            "PAH3DEEUR"     => [
                'begin' => 1429567200, // starting from 2015.04.21 00:00
            ],
            "PSMDEEUR"      => [
                'begin' => 1429480800, // starting from 2015.04.20 00:00
            ],
            "RWEDEEUR"      => [
                'begin' => 1429135200, // starting from 2015.04.16 00:00
            ],
            "SAPDEEUR"      => [
                'begin' => 1429135200, // starting from 2015.04.16 00:00
            ],
            "SDFDEEUR"      => [
                'begin' => 1429048800, // starting from 2015.04.15 00:00
            ],
            "SIEDEEUR"      => [
                'begin' => 1429480800, // starting from 2015.04.20 00:00
            ],
            "TKADEEUR"      => [
                'begin' => 1428962400, // starting from 2015.04.14 00:00
            ],
            "TUI1DEEUR"     => [
                'begin' => 1429048800, // starting from 2015.04.15 00:00
            ],
            "USA30IDXUSD"   => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "USA500IDXUSD"  => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "USATECHIDXUSD" => [
                'begin' => 1356994800, // starting from 2013.01.01 00:00
            ],
            "VNADEEUR"      => [
                'begin' => 1428962400, // starting from 2015.04.14 00:00
            ],
            "VOW3DEEUR"     => [
                'begin' => 1428962400, // starting from 2015.04.14 00:00
            ],

            // commodities - energy
            #"E_Light" => 1324375200, // Light starting from 2011.12.20 10:00
            #"E_Brent" => 1326988800, // Brent starting from 2012.01.19 16:00
            // commodities - metals
            #"E_Copper" => 1326988800, // Copper starting from 2012.01.19 16:00
            #"E_Palladium" => 1326988800, // Palladium starting from 2012.01.19 16:00
            #"E_Platinum" => 1326988800, // Platinum starting from 2012.01.19 16:00
            // indices - Europe
            #"E_DJE50XX" => 1326988800, // Europe 50 starting from 2012.01.19 16:00
            #"E_CAAC40" => 1326988800, // France 40 starting from 2012.01.19 16:00
            #"E_Futsee100" => 1326988800, // UK 100 starting from 2012.01.19 16:00
            #"E_DAAX" => 1326988800, // Germany 30 starting from 2012.01.19 16:00
            #"E_SWMI" => 1326988800, // Switzerland 20 starting from 2012.01.19 16:00
            // indices - Americas
            #"E_NQcomp" => 1326988800, // US Tech Composite starting from 2012.01.19 16:00
            "E_Nysseecomp" => 1326988800, // US Composite starting from 2012.01.19 16:00
            #"E_DJInd" => 1326988800, // US 30 starting from 2012.01.19 16:00
            #"E_NQ100" => 1326988800, // US 100 Tech starting from 2012.01.19 16:00
            #"E_SandP500" => 1326988800, // US 500 starting from 2012.01.19 16:00
            #"E_AMMEKS" => 1326988800, // US Average starting from 2012.01.19 16:00
            // indices - Asia / Pacific
            #"E_HKong" => 1328475600, // Hong Kong 40 starting from 2012.02.05 21:00
            "E_SCKorea" => 1326988800, // Korea 200 starting from 2012.01.19 16:00
            #"E_N225Jap" => 1328486400, // Japan 225 starting from 2012.02.06 00:00
            // stocks - Australia
            #"E_ANZASX" => 1348146000, // Australia & Nz Banking starting from 2012.09.20 13:00
            #"E_BHPASX" => 1348156800, // Bhp Billiton starting from 2012.09.20 16:00
            #"E_CBAASX" => 1348156800, // Commonwealth Bank Of Australia starting from 2012.09.20 16:00
            #"E_NABASX" => 1348156800, // National Australia Bank starting from 2012.09.20 16:00
            #"E_WBCASX" => 1348156800, // Westpac Banking starting from 2012.09.20 16:00
            // stocks - Hungary
            #"E_EGISBUD" => 1348146000, // Egis Nyrt starting from 2012.09.20 13:00
            #"E_MOLBUD" => 1348146000, // Mol Hungarian Oil & Gas Nyrt starting from 2012.09.20 13:00
            #"E_MTELEKOMBUD" => 1348146000, // Magyar Telekom Telecommunications starting from 2012.09.20 13:00
            #"E_OTPBUD" => 1348146000, // Ot Bank Nyrt starting from 2012.09.20 13:00
            #"E_RICHTERBUD" => 1348146000, // Richter Gedeon Nyrt starting from 2012.09.20 13:00
            // stocks - France
            #"E_BNPEEB" => 1341594000, // BNP Paribas starting from 2012.07.06 17:00
            #"E_FPEEB" => 1341594000, // Total starting from 2012.07.06 17:00
            #"E_FTEEEB" => 1341594000, // France Telecom starting from 2012.07.06 17:00
            #"E_MCEEB" => 1341594000, // LVMH Moet Hennessy Louis Vuitton starting from 2012.07.06 17:00
            #"E_SANEEB" => 1341594000, // Sanofi starting from 2012.07.06 17:00
            // stocks - Netherlands
            #"E_MTEEB" => 1333101600, // ArcelorMittal starting from 2012.03.30 10:00
            #"E_PHIA" => 1341406800, // Koninklijke Philips Electronics starting from 2012.07.04 13:00
            #"E_RDSAEEB" => 1333101600, // Royal Dutch Shell starting from 2012.03.30 10:00
            #"E_UNAEEB" => 1333101600, // Unilever starting from 2012.03.30 10:00
            // stocks - Germany
            #"E_BAY" => 1330948800, // Bayer starting from 2012.03.05 12:00
            #"E_BMWXET" => 1333101600, // BMW starting from 2012.03.30 10:00
            #"E_EOANXET" => 1333101600, // E.On starting from 2012.03.30 10:00
            #"E_SIEXET" => 1341604800, // Siemens starting from 2012.07.06 20:00
            #"E_VOWXET" => 1341604800, // Volkswagen starting from 2012.07.06 20:00
            // stocks - Hong Kong
            #"E_0883HKG" => 1341781200, // CNOOC starting from 2012.07.08 21:00
            #"E_0939HKG" => 1341784800, // China Construction Bank starting from 2012.07.08 22:00
            #"E_0941HKG" => 1341781200, // China Mobile starting from 2012.07.08 21:00
            #"E_1398HKG" => 1341781200, // ICBC starting from 2012.07.08 21:00
            #"E_3988HKG" => 1341784800, // Bank Of China starting from 2012.07.08 22:00
            // stocks - UK
            #"E_BLTLON" => 1333101600, // BHP Billiton starting from 2012.03.30 10:00
            #"E_BP" => 1326988800, // BP starting from 2012.01.19 16:00
            #"E_HSBA" => 1326988800, // HSBC Holdings starting from 2012.01.19 16:00
            #"E_RIOLON" => 1333101600, // Rio Tinto starting from 2012.03.30 10:00
            #"E_VODLON" => 1333101600, // Vodafone starting from 2012.03.30 10:00
            // stocks - Spain
            #"E_BBVAMAC" => 1348149600, // BBVA starting from 2012.09.20 14:00
            #"E_IBEMAC" => 1348149600, // Iberdrola starting from 2012.09.20 14:00
            #"E_REPMAC" => 1348149600, // Repsol starting from 2012.09.20 14:00
            #"E_SANMAC" => 1348149600, // Banco Santander starting from 2012.09.20 14:00
            #"E_TEFMAC" => 1348149600, // Telefonica starting from 2012.09.20 14:00
            // stocks - Italy
            #"E_EN" => 1348146000, // Enel starting from 2012.09.20 13:00
            #"E_ENIMIL" => 1348146000, // Eni starting from 2012.09.20 13:00
            #"E_FIA" => 1348146000, // Fiat starting from 2012.09.20 13:00
            #"E_GMIL" => 1348146000, // Generali starting from 2012.09.20 13:00
            #"E_ISPMIL" => 1348146000, // Intesa Sanpaolo starting from 2012.09.20 13:00
            #"E_UCGMIL" => 1348146000, // Unicredit starting from 2012.09.20 13:00
            // stocks - Denmark
            #"E_CARL_BOMX" => 1348149600, // Carlsberg starting from 2012.09.20 14:00
            #"E_DANSKEOMX" => 1348149600, // Danske Bank starting from 2012.09.20 14:00
            #"E_MAERSK_BOMX" => 1348149600, // Moeller Maersk B starting from 2012.09.20 14:00
            #"E_NOVO_BOMX" => 1348149600, // Novo Nordisk starting from 2012.09.20 14:00
            #"E_VWSOMX" => 1348149600, // Vestas Wind starting from 2012.09.20 14:00
            // stocks - Sweden
            #"E_SHB_AOMX" => 1348149600, // Svenska Handelsbanken starting from 2012.09.20 14:00
            #"E_SWED_AOMX" => 1348149600, // Swedbank starting from 2012.09.20 14:00
            #"E_TLSNOMX" => 1348149600, // Teliasonera starting from 2012.09.20 14:00
            #"E_VOLV_BOMX" => 1348149600, // Volvo B starting from 2012.09.20 14:00
            #"E_NDAOMX" => 1348149600, // Nordea Bank starting from 2012.09.20 14:00
            // stocks - Norway
            #"E_DNBOSL" => 1348146000, // DNB starting from 2012.09.20 13:00
            #"E_SDRLOSL" => 1348146000, // Seadrill starting from 2012.09.20 13:00
            #"E_STLOSL" => 1348146000, // StatoilHydro starting from 2012.09.20 13:00
            #"E_TELOSL" => 1348146000, // Telenor starting from 2012.09.20 13:00
            #"E_YAROSL" => 1348146000, // Yara starting from 2012.09.20 13:00
            // stocks - Singapore
            #"E_C07SES" => 1348149600, // Jardine Matheson starting from 2012.09.20 14:00
            #"E_D05SES" => 1348149600, // DBS Group starting from 2012.09.20 14:00
            #"E_O39SES" => 1348153200, // Oversea-Chinese Banking starting from 2012.09.20 15:00
            #"E_U11SES" => 1348149600, // United Overseas Bank starting from 2012.09.20 14:00
            #"E_Z74SES" => 1348149600, // Singapore Telecommunications starting from 2012.09.20 14:00
            // stocks - Switzerland
            #"E_CSGN" => 1326988800, // Cs Group starting from 2012.01.19 16:00
            #"E_NESN" => 1326988800, // Nestle starting from 2012.01.19 16:00
            #"E_NOVNSWX" => 1333101600, // Novartis starting from 2012.03.30 10:00
            #"E_UBSN" => 1326988800, // UBS starting from 2012.01.19 16:00
            // stocks - Austria
            #"E_ANDRVIE" => 1348149600, // Andritz starting from 2012.09.20 14:00
            #"E_EBS" => 1348149600, // Erste Group Bank starting from 2012.09.20 14:00
            #"E_OMVVIE" => 1348149600, // OMV starting from 2012.09.20 14:00
            #"E_RBIVIE" => 1348149600, // Raiffeisen Bank starting from 2012.09.20 14:00
            #"E_VOE" => 1348149600, // Voestalpine starting from 2012.09.20 14:00
            // stocks - Poland
            #"E_KGHWAR" => 1348146000, // KGHM Polska Miedz starting from 2012.09.20 13:00
            #"E_PEOWAR" => 1348146000, // Bank Pekao starting from 2012.09.20 13:00
            #"E_PKNWAR" => 1348146000, // Polski Koncern Naftowy Orlen starting from 2012.09.20 13:00
            #"E_PKOBL1WAR" => 1348146000, // Powszechna Kasa Oszczednosci Bank Polski starting from 2012.09.20 13:00
            #"E_PZUWAR" => 1348146000, // Powszechny Zaklad Ubezpieczen starting from 2012.09.20 13:00
            // stocks - US
            #"E_AAPL" => 1333101600, // Apple starting from 2012.03.30 10:00
            #"E_AMZN" => 1324375200, // Amazon starting from 2011.12.20 10:00
            #"E_AXP" => 1326988800, // American Express starting from 2012.01.19 16:00
            #"E_BAC" => 1324375200, // Bank Of America starting from 2011.12.20 10:00
            #"E_CL" => 1333101600, // Colgate Palmolive starting from 2012.03.30 10:00
            #"E_CSCO" => 1324375200, // Cisco starting from 2011.12.20 10:00
            #"E_DELL" => 1326988800, // Dell starting from 2012.01.19 16:00
            #"E_DIS" => 1324375200, // Disney Walt starting from 2011.12.20 10:00
            #"E_EBAY" => 1326988800, // Ebay starting from 2012.01.19 16:00
            #"E_GE" => 1324375200, // General Electric starting from 2011.12.20 10:00
            #"E_GM" => 1324375200, // General Motors starting from 2011.12.20 10:00
            #"E_GOOGL" => 1324375200, // Google starting from 2011.12.20 10:00
            #"E_HD" => 1326988800, // Home Depot starting from 2012.01.19 16:00
            #"E_HPQ" => 1324375200, // Hewlett Packard starting from 2011.12.20 10:00
            #"E_IBM" => 1324375200, // IBM starting from 2011.12.20 10:00
            #"E_INTC" => 1324375200, // Intel starting from 2011.12.20 10:00
            #"E_JNJ" => 1324375200, // Johnson & Johnson starting from 2011.12.20 10:00
            #"E_JPM" => 1324375200, // JPMorgan Chase starting from 2011.12.20 10:00
            #"E_KO" => 1324375200, // Coca Cola starting from 2011.12.20 10:00
            #"E_MCD" => 1324375200, // McDonalds starting from 2011.12.20 10:00
            #"E_MMM" => 1324375200, // 3M starting from 2011.12.20 10:00
            #"E_MSFT" => 1324375200, // Microsoft starting from 2011.12.20 10:00
            #"E_ORCL" => 1324375200, // Oracle starting from 2011.12.20 10:00
            #"E_PG" => 1324375200, // Procter & Gamble starting from 2011.12.20 10:00
            #"E_PM" => 1333105200, // Philip Morris starting from 2012.03.30 11:00
            #"E_SBUX" => 1326988800, // Starbucks starting from 2012.01.19 16:00
            #"E_T" => 1324378800, // AT&T starting from 2011.12.20 11:00
            #"E_UPS" => 1333105200, // UPS starting from 2012.03.30 11:00
            "E_VIXX" => 1326988800, // Cboe Volatility Index starting from 2012.01.19 16:00
            #"E_WMT" => 1326988800, // Wal-Mart Stores starting from 2012.01.19 16:00
            #"E_XOM" => 1324375200, // Exxon Mobil starting from 2011.12.20 10:00
            #"E_YHOO" => 1326988800, // Yahoo starting from 2012.01.19 16:00
            // Currency pairs.
            "EURUSD" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "AUDNZD" => [
                'begin' => 1229961600, // starting from 2008.12.22 16:00
            ],
            "AUDUSD" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "AUDJPY" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "EURCHF" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "EURGBP" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "EURJPY" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "GBPCHF" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "GBPJPY" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "GBPUSD" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "NZDUSD" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "USDCAD" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "USDCHF" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "USDJPY" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "CADJPY" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "EURAUD" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "CHFJPY" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "EURCAD" => [
                'begin' => 1222167600, // starting from 2008.09.23 11:00
            ],
            "EURNOK" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "EURSEK" => [
                'begin' => 1175270400, // starting from 2007.03.30 16:00
            ],
            "USDNOK" => [
                'begin' => 1222639200, // starting from 2008.09.28 22:00
            ],
            "USDSEK" => [
                'begin' => 1222642800, // starting from 2008.09.28 23:00
            ],
            "USDSGD" => [
                'begin' => 1222642800, // starting from 2008.09.28 23:00
            ],
            "AUDCAD" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "AUDCHF" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "CADCHF" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "EURNZD" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "GBPAUD" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "GBPCAD" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "GBPNZD" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "NZDCAD" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "NZDCHF" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "NZDJPY" => [
                'begin' => 1266318000, // starting from 2010.02.16 11:00
            ],
            "XAGUSD" => [
                'begin' => 1289491200, // starting from 2010.11.11 16:00
            ],
            "XAUUSD" => [
                'begin' => 1305010800, // starting from 2011.05.10 07:00
            ],
        ],
    ],
];
