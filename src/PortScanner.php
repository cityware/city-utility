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
    private $typePort = 'tcp';

    // TODO: accept IPv6 addresses
    // TODO: accept an array of host ips
    // TODO: allow a hostname to be supplied
    // TODO: accept an array of hostnames
    // TODO: validate that the starting port is between 1 and 65536
    // TODO: validate that the ending port is between 1 and 65536
    // TODO: validate that the ending port is after the starting port
    public function __construct($hostIP = '127.0.0.1', $startPort = 1, $endPort = 1024, $timeout = 1) {
        $this->startPort = $startPort;
        $this->endPort = $endPort;
        $this->hostIP = $hostIP;
        $this->timeout = $timeout;
        set_time_limit(0);
    }
    
    public function setTypePort($typePort) {
        $this->typePort = $typePort;
        return $this;
    }

    public function getOpenPorts() {
        return $this->openPorts;
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

    
    /*
     *
     * Scans the host IP
     *
     *
     */

    public function scan() {
        set_time_limit(0);
        
        if(strtolower($this->typePort) == 'tcp'){
            $hostIp = $this->hostIP;
        } else if(strtolower($this->typePort) == 'udp'){
            $hostIp = "udp://$this->hostIP";
        } else {
            throw new \Exception('Port Type undefined!');
        }
        
        $errno = $errstr = null;
        for ($portNumber = $this->startPort; $portNumber <= $this->endPort; $portNumber++) {
            $handle = @fsockopen($this->hostIP, $portNumber, $errno, $errstr, $this->timeout);
            if ($handle) {
                $service = getservbyport($portNumber, strtolower($this->typePort));
                $this->openPorts[$portNumber] = "$service";
                fclose($handle);
            }
        }
        return $this->openPorts;
    }

}
