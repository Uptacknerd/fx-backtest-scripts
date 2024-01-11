<?php

namespace Uptacknerd\FxBtScripts;

use RuntimeException;

class Config {
    private array $config;

    public function __construct() {
        $filename = dirname(__DIR__) . '/config/config.php';
        if (!is_readable($filename)) {
            $filename = dirname(__DIR__) . '/config/config.example.php';
        }
        $this->config = require_once $filename;

        $filename = dirname(__DIR__) . '/config/brokers.php';
        if (!is_readable($filename)) {
            $filename = dirname(__DIR__) . '/config/brokers.example.php';
        }
        $this->config['broker'] = require_once $filename;
    }

    public function getInstrumentStartTimestamp(string $instrument) {
        return $this->config['dukascopy']['symbols'][strtoupper($instrument)];
    }

    public function getDigits(string $broker, string $instrument) {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['digits'])) {
            throw new RuntimeException("Digits not defined in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['digits'];
    }

    public function getPointSize(string $broker, string $instrument) {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['points'])) {
            throw new RuntimeException("Points not defined in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['points'];
    }

    public function getServer(string $broker): string {
        if (!isset($this->config['broker'][$broker]['server'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['server'];
    }

    public function getMinLot(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['minLot'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['minLot'];
    }

    public function getMaxLot(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['maxLot'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['maxLot'];
    }

    public function getLotStep(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['lotStep'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['lotStep'];
    }

    public function getStopLevel(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['stopLevel'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['stopLevel'];
    }

    public function getBaseCurrency(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['baseCurrency'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['baseCurrency'];
    }

    public function getSpread(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['spread'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['spread'];
    }

    public function getContractSize(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['contractSize'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['contractSize'];
    }

    public function getTickSize(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['tickSize'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['tickSize'];
    }

    public function getTickValue(string $broker, string $instrument): string {
        if (!isset($this->config['broker'][$broker]['instrument'][$instrument]['tickValue'])) {
            throw new RuntimeException("Broker not defined or incomplete in configuration file");
        }

        return $this->config['broker'][$broker]['instrument'][$instrument]['tickValue'];
    }
}