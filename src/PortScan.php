<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

use Cityware\Utility\TcpPortScanner;
use Cityware\Utility\UdpPortScanner;

/**
 * Description of PortScan
 *
 * @author fsvxavier
 */
class PortScan {

    public function __construct($typeScan = 'tcp', $hostIP, $startPort = 1, $endPort = 1024, $timeout = 1) {
        if (strtolower($typeScan) == 'tcp') {
            $scanPort = new TcpPortScanner($hostIP, $startPort, $endPort, $timeout);
        } else {
            $scanPort = new UdpPortScanner($hostIP, $startPort, $endPort, $timeout);
        }
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
    public function getService($port, $protocol = "tcp") {
        return @getservbyport($port, $protocol);
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
    public function getPort($service, $protocol = "tcp") {
        return @getservbyname($service, $protocol);
    }

}
