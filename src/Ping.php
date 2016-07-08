<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

class Ping {

    private $host;
    private $ttl;
    private $timeout;
    private $typeCon = 'tcp';
    private $port = 80;
    private $data = 'Ping';
    private $commandOutput;

    /**
     * Called when the Ping object is created.
     *
     * @param string $host
     *   The host to be pinged.
     * @param int $ttl
     *   Time-to-live (TTL) (You may get a 'Time to live exceeded' error if this
     *   value is set too low. The TTL value indicates the scope or range in which
     *   a packet may be forwarded. By convention:
     *     - 0 = same host
     *     - 1 = same subnet
     *     - 32 = same site
     *     - 64 = same region
     *     - 128 = same continent
     *     - 255 = unrestricted
     * @param int $timeout
     *   Timeout (in seconds) used for ping and fsockopen().
     * @throws \Exception if the host is not set.
     */
    public function __construct($host, $ttl = 255, $timeout = 10) {
        if (!isset($host)) {
            throw new \Exception("Error: Host name not supplied.");
        }
        $this->host = $host;
        $this->ttl = $ttl;
        $this->timeout = $timeout;
    }

    /**
     * Set the ttl (in hops).
     *
     * @param int $ttl
     *   TTL in hops.
     */
    public function setTtl($ttl) {
        $this->ttl = $ttl;
    }

    /**
     * Get the ttl.
     *
     * @return int
     *   The current ttl for Ping.
     */
    public function getTtl() {
        return $this->ttl;
    }

    /**
     * Set the timeout.
     *
     * @param int $timeout
     *   Time to wait in seconds.
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    /**
     * Get the timeout.
     *
     * @return int
     *   Current timeout for Ping.
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Set the host.
     *
     * @param string $host
     *   Host name or IP address.
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Get the host.
     *
     * @return string
     *   The current hostname for Ping.
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Set the port (only used for fsockopen method).
     *
     * Since regular pings use ICMP and don't need to worry about the concept of
     * 'ports', this is only used for the fsockopen method, which pings servers by
     * checking port 80 (by default).
     *
     * @param int $port
     *   Port to use for fsockopen ping (defaults to 80 if not set).
     */
    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * Get the port (only used for fsockopen method).
     *
     * @return int
     *   The port used by fsockopen pings.
     */
    public function getPort() {
        return $this->port;
    }

    public function getTypeCon() {
        return $this->typeCon;
    }

    public function setTypeCon($typeCon) {
        $this->typeCon = $typeCon;
        return $this;
    }

    /**
     * Return the command output when method=exec.
     * @return string
     */
    public function getCommandOutput() {
        return $this->commandOutput;
    }

    /**
     * Matches an IP on command output and returns.
     * @return string
     */
    public function getIpAddress() {
        $out = array();
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $this->commandOutput, $out)) {
            return $out[0];
        }
        return null;
    }

    /**
     * Ping a host.
     *
     * @param string $method
     *   Method to use when pinging:
     *     - exec (default): Pings through the system ping command. Fast and
     *       robust, but a security risk if you pass through user-submitted data.
     *     - fsockopen: Pings a server on port 80.
     *     - socket: Creates a RAW network socket. Only usable in some
     *       environments, as creating a SOCK_RAW socket requires root privileges.
     *
     * @return mixed
     *   Latency as integer, in ms, if host is reachable or FALSE if host is down.
     */
    public function ping($method = 'fsockopen') {
        $latency = false;
        switch ($method) {
            case 'fsockopen':
                $latency = $this->pingFsockopen();
                break;
            case 'socket':
                $latency = $this->pingSocket();
                break;
        }
        // Return the latency.
        return $latency;
    }

    /**
     * The fsockopen method simply tries to reach the host on a port. This method
     * is often the fastest, but not necessarily the most reliable. Even if a host
     * doesn't respond, fsockopen may still make a connection.
     *
     * @return int
     *   Latency, in ms.
     */
    private function pingFsockopen() {
        $start = microtime(true);

        if (strtolower($this->typeCon) != 'tcp') {
            $this->setPort(7);
            $this->setHost("udp://$this->host");
        }

        // fsockopen prints a bunch of errors if a host is unreachable. Hide those
        // irrelevant errors and deal with the results instead.
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$fp) {
            $latency = false;
        } else {
            $latency = microtime(true) - $start;
            $latency = round($latency * 1000);
        }
        return $latency;
    }

    /**
     * The socket method uses raw network packet data to try sending an ICMP ping
     * packet to a server, then measures the response time. Using this method
     * requires the script to be run with root privileges, though, so this method
     * only works reliably on Windows systems and on Linux servers where the
     * script is not being run as a web user.
     *
     * @return int
     *   Latency, in ms.
     */
    private function pingSocket() {
        // Create a package.
        $type = "\x08";
        $code = "\x00";
        $checksum = "\x00\x00";
        $identifier = "\x00\x00";
        $seq_number = "\x00\x00";
        $package = $type . $code . $checksum . $identifier . $seq_number . $this->data;
        // Calculate the checksum.
        $checksum = $this->calculateChecksum($package);
        // Finalize the package.
        $package = $type . $code . $checksum . $identifier . $seq_number . $this->data;
        // Create a socket, connect to server, then read socket and calculate.
        if ($socket = socket_create(AF_INET, SOCK_RAW, 1)) {
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 10,'usec' => 0));
            // Prevent errors from being printed when host is unreachable.
            @socket_connect($socket, $this->host, null);
            $start = microtime(true);
            // Send the package.
            @socket_send($socket, $package, strlen($package), 0);
            if (socket_read($socket, 255) !== false) {
                $latency = microtime(true) - $start;
                $latency = round($latency * 1000);
            } else {
                $latency = false;
            }
        } else {
            $latency = false;
        }
        // Close the socket.
        socket_close($socket);
        return $latency;
    }

    /**
     * Calculate a checksum.
     *
     * @param string $data
     *   Data for which checksum will be calculated.
     *
     * @return string
     *   Binary string checksum of $data.
     */
    private function calculateChecksum($data) {
        if (strlen($data) % 2) {
            $data .= "\x00";
        }
        $bit = unpack('n*', $data);
        $sum = array_sum($bit);
        while ($sum >> 16) {
            $sum = ($sum >> 16) + ($sum & 0xffff);
        }
        return pack('n*', ~$sum);
    }

}
