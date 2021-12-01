<?php

use Uptacknerd\FxBtScripts\FxtFile;

return [
    'vfx' => [
        'server' => 'VantageFXInternational-Demo',
        'instrument' => [
            'XAUUSD+' =>  [ // Data collected from symbol properties
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

            ]
        ],
    ],
];