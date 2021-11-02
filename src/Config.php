<?php

namespace Uptacknerd\FxBtScripts;

class Config {
    private array $config;

    public function __construct() {
        $this->config = require_once dirname(__DIR__) . '/config/config.php';
    }

    public function getInstrumentStartTimestamp(string $instrument) {
        return $this->config['dukascopy']['symbols'][strtoupper($instrument)];
    }
}