<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of PortScanner
 *
 * @author fsvxavier
 */
class PortScanner {

    private $startPort;
    private $endPort;
    private $hostIP;
    private $timeout;
    private $openPorts = array();
    private $hostTest = array();
    private $typePort;

    // TODO: accept IPv6 addresses
    // TODO: accept an array of host ips
    // TODO: allow a hostname to be supplied
    // TODO: accept an array of hostnames
    // TODO: validate that the starting port is between 1 and 65536
    // TODO: validate that the ending port is between 1 and 65536
    // TODO: validate that the ending port is after the starting port
    public function __construct($hostIP = '127.0.0.1', $startPort = 1, $endPort = 1024, $typePort = 'tcp', $timeout = 1) {
        $this->startPort = $startPort;
        $this->endPort = $endPort;
        $this->hostIP = $hostIP;
        $this->timeout = $timeout;
        $this->typePort = $typePort;
        set_time_limit(0);
    }

    public function setTypePort($typePort) {
        $this->typePort = $typePort;
        return $this;
    }

    public function setStartPort($startPort) {
        $this->startPort = $startPort;
        return $this;
    }

    public function setEndPort($endPort) {
        $this->endPort = $endPort;
        return $this;
    }

    public function setHostIP($hostIP) {
        $this->hostIP = $hostIP;
        return $this;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    public function setOpenPorts($openPorts) {
        $this->openPorts = $openPorts;
        return $this;
    }

    public function getOpenPorts() {
        return $this->openPorts;
    }
    
    /**
     * Define host to test port
     * @param string $host
     * @param integer $port
     * @param string $typePort
     * @return \Cityware\Utility\PortScanner
     */
    public function addHostTest($host, $port, $typePort) {
        $this->hostTest[] = array($host, $port, $typePort);
        return $this;
    }

    /**
     * Port test for the defined host
     * @return array
     */
    public function portTest() {

        $errno = $errstr = null;

        foreach ($this->hostTest as $keyHostTest => $valueHostTest) {
            list($host, $portNumber, $typePort) = $valueHostTest;

            if (strtolower($typePort) == 'tcp') {
                $hostIp = $host;
            } else {
                $hostIp = "udp://$host";
            }
            
            $this->openPorts[$keyHostTest];

            $handle = @fsockopen($hostIp, $portNumber, $errno, $errstr, $this->timeout);
            if ($handle) {
                $service = $this->getService($portNumber, strtolower($typePort));
                $this->openPorts[$keyHostTest][$portNumber] = (!empty($service)) ? $service : null;
                fclose($handle);
            }
        }
        return $this->openPorts;
    }

    /**
     * Scan range ports in host
     * @return array
     * @throws \Exception
     */
    public function scanPortRange() {
        if (strtolower($this->typePort) == 'tcp') {
            $hostIp = $this->hostIP;
        } else if (strtolower($this->typePort) == 'udp') {
            $hostIp = "udp://$this->hostIP";
        } else {
            throw new \Exception('Port Type undefined!');
        }

        $errno = $errstr = null;
        for ($portNumber = $this->startPort; $portNumber <= $this->endPort; $portNumber++) {
            $handle = @fsockopen($hostIp, $portNumber, $errno, $errstr, $this->timeout);
            if ($handle) {
                $service = $this->getService($portNumber, strtolower($this->typePort));
                $this->openPorts[$portNumber] = (!empty($service)) ? $service : null;
                fclose($handle);
            }
        }
        return $this->openPorts;
    }

    /**
     * Get name of the service that is listening on a certain port.
     *
     * @param integer $port     Portnumber
     * @param string  $protocol Protocol (Is either tcp or udp. Default is tcp.)
     *
     * @access public
     *
     * @return string  Name of the Internet service associated with $service
     */
    public function getService($port) {
        return @getservbyport($port, strtolower($this->typePort));
    }

    // }}}
    // {{{ getPort()
    /**
     * Get port that a certain service uses.
     *
     * @param string $service  Name of the service
     * @param string $protocol Protocol (Is either tcp or udp. Default is tcp.)
     *
     * @access public
     *
     * @return integer Internet port which corresponds to $service
     */
    public function getPort($service) {
        return @getservbyname($service, strtolower($this->typePort));
    }

}
