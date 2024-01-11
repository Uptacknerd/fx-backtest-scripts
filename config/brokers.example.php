<?php

use Uptacknerd\FxBtScripts\FxtFile;

return [
    'vfx' => [
        'server' => 'VantageInternational-Demo',
        'instrument' => [
            'XAUUSD' =>  [ // Data collected from symbol properties
                'digits'          => 2,
                'points'          => 0.02,
                'minLot'          => 0.01,
                'maxLot'          => 100,
                'lotStep'         => 0.01,
                'stopLevel'       => 20,
                'spread'          => 20,
                'orderExpiration' => FxtFile::ORDER_EXPIRATION_GTC,
                'contractSize'    => 100,
                'profitCalc'      => FxtFile::PROFIT_CALCULATION_FOREX,
                '3daysSwap'       => 3,
                'freeMargin'      => FxtFile::MARGIN_CALCULATION_MODE_CFD,
                'baseCurrency'    => 'XAU',
                'contractSize'    => 100,
                //'marginStopout'   => FxtFile::MARGIN

            ],
            'AUDCAD' =>  [ // Data collected from symbol properties
                'digits'          => 5,
                'points'          => 0.02,
                'minLot'          => 0.01,
                'maxLot'          => 100,
                'lotStep'         => 0.01,
                'stopLevel'       => 0,
                'spread'          => 5,
                'orderExpiration' => FxtFile::ORDER_EXPIRATION_GTC,
                'contractSize'    => 100,
                'profitCalc'      => FxtFile::PROFIT_CALCULATION_FOREX,
                '3daysSwap'       => 3,
                'freeMargin'      => FxtFile::MARGIN_CALCULATION_MODE_FOREX,
                'baseCurrency'    => 'AUD',
                'contractSize'    => 100000,
                //'marginStopout'   => FxtFile::MARGIN
        ],
    ],
];
