<?php

use Uptacknerd\FxBtScripts\FxtFile;

return [
    'vfx' => [
        'server' => 'VantageFXInternational-Demo',
        'instrument' => [
            'XAUUSD' =>  [ // Data collected from symbol properties
                'digits'          => 2,
                'tickSize'        => 1e-2,
                'tickValue'       => 0,
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
                'baseCurrency'    => 'USD',
                'contractSize'    => 100,
                //'marginStopout'   => FxtFile::MARGIN
            ],
            'AUDCAD' =>  [ // Data collected from symbol properties
                'digits'          => 5,
                'tickSize'        => 1e-5,
                'tickValue'       => 0.760733,
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
            'EURCHF' =>  [ // Data collected from symbol properties
                'digits'          => 5,
                'tickSize'        => 1e-5,
                'tickValue'       => 0.760733,
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
    ],
];