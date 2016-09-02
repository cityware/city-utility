<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Ping for PHP.
 *
 * This class pings a host.
 *
 * The ping() method pings a server using 'exec', 'socket', or 'fsockopen', and
 * and returns FALSE if the server is unreachable within the given ttl/timeout,
 * or the latency in milliseconds if the server is reachable.
 *
 * Quick Start:
 * @code
 *   include 'path/to/Ping/JJG/Ping.php';
 *   use \JJG\Ping as Ping;
 *   $ping = new Ping('www.example.com');
 *   $latency = $ping->ping();
 * @endcode
 *
 * @version 1.1.0
 * @author Jeff Geerling.
 */
class PingNet {

    private $host;
    private $ttl;
    private $testCount = 10;
    private $typeCon = 'tcp';
    private $port = 80;
    private $data = 'Ping';
    private $commandOutput;
    private $timeout;

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
    public function __construct($host, $ttl = 255, $timeout = 1) {
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
    public function ping($method = 'exec') {
        $latency = false;
        switch ($method) {
            case 'exec':
                $latency = $this->pingExec();
                break;
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
     * The exec method uses the possibly insecure exec() function, which passes
     * the input to the system. This is potentially VERY dangerous if you pass in
     * any user-submitted data. Be SURE you sanitize your inputs!
     *
     * @return int
     *   Latency, in ms.
     */
    private function pingExec() {
        $latency = false;
        $ttl = escapeshellcmd($this->ttl);
        $timeout = escapeshellcmd($this->timeout);
        $host = escapeshellcmd($this->host);
        $testCount = escapeshellcmd($this->testCount);
        // Exec string for Windows-based systems.
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // -n = number of pings; -i = ttl; -w = timeout (in milliseconds).
            $exec_string = 'ping -n ' . $testCount . ' -i ' . $ttl . ' -w ' . ($timeout * 1000) . ' ' . $host;
        }
        // Exec string for Darwin based systems (OS X).
        else if (strtoupper(PHP_OS) === 'DARWIN') {
            // -n = numeric output; -c = number of pings; -m = ttl; -t = timeout.
            $exec_string = 'ping -n -c ' . $testCount . ' -m ' . $ttl . ' -t ' . $timeout . ' ' . $host;
        }
        // Exec string for other UNIX-based systems (Linux).
        else {
            // -n = numeric output; -c = number of pings; -t = ttl; -W = timeout
            $exec_string = 'ping -n -c ' . $testCount . ' -t ' . $ttl . ' -W ' . $timeout . ' ' . $host;
        }
        exec($exec_string, $output, $return);

        echo '<pre>';
        print_r($output);
        print_r($this->_parseResult($output));
        exit;
        // Strip empty lines and reorder the indexes from 0 (to make results more
        // uniform across OS versions).
        $this->commandOutput = implode($output, '');
        $output = array_values(array_filter($output));
        // If the result line in the output is not empty, parse it.
        if (!empty($output[1])) {
            // Search for a 'time' value in the result line.
            //$response = preg_match("/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/", $output[1], $matches);

            $totalLatency = $lostPacket = 0;
            for ($index = 0; $index < $testCount; $index++) {
                $response = preg_match("/tempo(?:=|<)(?<tempo>[\.0-9]+)(?:|\s)ms/", $output[($index + 1)], $matches);
                // If there's a result and it's greater than 0, return the latency.
                if ($response > 0 && isset($matches['tempo'])) {
                    $latency = round($matches['tempo']);
                    $totalLatency += $latency;
                } else {
                    $lostPacket++;
                }
            }
        }
        return Array('latencyAvg' => $totalLatency / $testCount, 'totalLostPacket' => $lostPacket);
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


        if (strtolower($this->typeCon) != 'udp') {
            $this->setPort(7);
            $this->setHost("udp://$this->host");
        }

        $packetsReceived = $lostPacket = 0;
        $errno = $errstr = null;
        $latency = array();
        // fsockopen prints a bunch of errors if a host is unreachable. Hide those
        // irrelevant errors and deal with the results instead.
        for ($index = 0; $index < $this->testCount; $index++) {

            $start = microtime(true);

            $fp = \fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if (!$fp) {
                $lostPacket++;
            } else {
                stream_set_timeout($fp, $this->timeout);
                fwrite($fp, "\xFF\xFF\xFF\xFFTSource Engine Query" . chr(0));
                $status = (!fread($fp, 5)) ? 'Offline' : 'Online';
                fclose($fp);
                $latency[] = (microtime(true) - $start);
                $packetsReceived++;
            }
        }

        return Array('latencyAvg' => (array_sum($latency) / $this->testCount), 'latencyMin' => min($latency), 'latencyMax' => max($latency), 'totalLostPacket' => $lostPacket, 'totalPacketsReceived' => $packetsReceived);
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
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 10, 'usec' => 0));
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

    /**
     * Parses the raw output from the ping utility.
     *
     * @access private
     */
    function _parseResult($data) {
        // MAINTAINERS:
        //
        //   If you're in this class fixing or extending the parser
        //   please add another file in the 'tests/test_parser_data/'
        //   directory which exemplafies the problem. And of course
        //   you'll want to run the 'tests/test_parser.php' (which
        //   contains easy how-to instructions) to make sure you haven't
        //   broken any existing behaviour.
        // operate on a copy of the raw output since we're going to modify it
        // 
        // 
        $upper = $lower = null;
        // remove leading and trailing blank lines from output
        $this->_parseResultTrimLines($data);
        // separate the output into upper and lower portions,
        // and trim those portions
        $this->_parseResultSeparateParts($data, $upper, $lower);
        $this->_parseResultTrimLines($upper);
        $this->_parseResultTrimLines($lower);
        // extract various things from the ping output . . .
        $this->_target_ip = $this->_parseResultDetailTargetIp($upper);
        $this->_bytes_per_request = $this->_parseResultDetailBytesPerRequest($upper);
        $this->_ttl = $this->_parseResultDetailTtl($upper);
        $this->_icmp_sequence = $this->_parseResultDetailIcmpSequence($upper);

        $this->_parseResultDetailTransmitted($lower);
        if (isset($this->_transmitted)) {
            $this->_bytes_total = $this->_transmitted * $this->_bytes_per_request;
        }
        echo '<pre>';
        print_r(max($this->_icmp_sequence));
        echo "<br>";
        print_r(min($this->_icmp_sequence));
        echo "<br>";
        print_r(array_sum($this->_icmp_sequence) / $this->testCount);
        exit;
    }

    /* function _parseResult() */

    /**
     * determinces the number of bytes sent by ping per ICMP ECHO
     *
     * @access private
     */
    function _parseResultDetailBytesPerRequest($upper) {
        // The ICMP ECHO REQUEST and REPLY packets should be the same
        // size. So we can also find what we want in the output for any
        // succesful ICMP reply which ping printed.
        for ($i = 1; $i < count($upper); $i++) {
            // anything like "64 bytes " at the front of any line in $upper??
            if (preg_match('/^\s*(\d+)\s*bytes/i', $upper[$i], $matches)) {
                return( (int) $matches[1] );
            }
            // anything like "bytes=64" in any line in the buffer??
            if (preg_match('/bytes=(\d+)/i', $upper[$i], $matches)) {
                return( (int) $matches[1] );
            }
        }
        // Some flavors of ping give two numbers, as in "n(m) bytes", on
        // the first line. We'll take the first number and add 8 for the
        // 8 bytes of header and such in an ICMP ECHO REQUEST.
        if (preg_match('/(\d+)\(\d+\)\D+$/', $upper[0], $matches)) {
            return( (int) (8 + $matches[1]) );
        }
        // Ok we'll just take the rightmost number on the first line. It
        // could be "bytes of data" or "whole packet size". But to
        // distinguish would require language-specific patterns. Most
        // ping flavors just put the number of data (ie, payload) bytes
        // if they don't specify both numbers as n(m). So we add 8 bytes
        // for the ICMP headers.
        if (preg_match('/(\d+)\D+$/', $upper[0], $matches)) {
            return( (int) (8 + $matches[1]) );
        }
        // then we have no idea...
        return( NULL );
    }

    /**
     * determines the round trip time (RTT) in milliseconds for each
     * ICMP ECHO which returned. Note that the array is keyed with the
     * sequence number of each packet; If any packets are lost, the
     * corresponding sequence number will not be found in the array keys.
     *
     * @access private
     */
    function _parseResultDetailIcmpSequence($upper) {
        // There is a great deal of variation in the per-packet output
        // from various flavors of ping. There are language variations
        // (time=, rtt=, zeit=, etc), field order variations, and some
        // don't even generate sequence numbers.
        //
        // Since our goal is to build an array listing the round trip
        // times of each packet, our primary concern is to locate the
        // time. The best way seems to be to look for an equals
        // character, a number and then 'ms'. All the "time=" versions
        // of ping will match this methodology, and all the pings which
        // don't show "time=" (that I've seen examples from) also match
        // this methodolgy.
        $results = array();
        for ($i = 1; $i < count($upper); $i++) {
            // by our definition, it's not a success line if we can't
            // find the time
            if (preg_match('/=\s*([\d+\.]+)\s*ms/i', $upper[$i], $matches)) {
                // float cast deals neatly with values like "126." which
                // some pings generate
                $rtt = (float) $matches[1];
                // does the line have an obvious sequence number?
                if (preg_match('/icmp_seq\s*=\s*([\d+]+)/i', $upper[$i], $matches)) {
                    $results[$matches[1]] = $rtt;
                } else {
                    // we use the number of the line as the sequence number
                    $results[($i - 1)] = $rtt;
                }
            }
        }
        return( $results );
    }

    /**
     * Locates the "packets lost" percentage in the ping output
     *
     * @access private
     */
    function _parseResultDetailLoss($lower) {
        for ($i = 1; $i < count($lower); $i++) {
            if (preg_match('/(\d+)%/', $lower[$i], $matches)) {
                $this->_loss = (int) $matches[1];
                return;
            }
        }
    }

    /**
     * Locates the "packets received" in the ping output
     *
     * @access private
     */
    function _parseResultDetailReceived($lower) {
        for ($i = 1; $i < count($lower); $i++) {
            // the second number on the line
            if (preg_match('/^\D*\d+\D+(\d+)/', $lower[$i], $matches)) {
                $this->_received = (int) $matches[1];
                return;
            }
        }
    }

    /**
     * determines the mininum, maximum, average and standard deviation
     * of the round trip times.
     *
     * @access private
     */
    function _parseResultDetailRoundTrip($lower) {
        // The first pattern will match a sequence of 3 or 4
        // alaphabet-char strings separated with slashes without
        // presuming the order. eg, "min/max/avg" and
        // "min/max/avg/mdev". Some ping flavors don't have the standard
        // deviation value, and some have different names for it when
        // present.
        $p1 = '[a-z]+/[a-z]+/[a-z]+/?[a-z]*';
        // And the pattern for 3 or 4 numbers (decimal values permitted)
        // separated by slashes.
        $p2 = '[0-9\.]+/[0-9\.]+/[0-9\.]+/?[0-9\.]*';
        $results = array();
        $matches = array();
        for ($i = (count($lower) - 1); $i >= 0; $i--) {
            if (preg_match('|(' . $p1 . ')[^0-9]+(' . $p2 . ')|i', $lower[$i], $matches)) {
                break;
            }
        }
        // matches?
        if (count($matches) > 0) {
            // we want standardized keys in the array we return. Here we
            // look for the values (min, max, etc) and setup the return
            // hash
            $fields = explode('/', $matches[1]);
            $values = explode('/', $matches[2]);
            for ($i = 0; $i < count($fields); $i++) {
                if (preg_match('/min/i', $fields[$i])) {
                    $results['min'] = (float) $values[$i];
                } else if (preg_match('/max/i', $fields[$i])) {
                    $results['max'] = (float) $values[$i];
                } else if (preg_match('/avg/i', $fields[$i])) {
                    $results['avg'] = (float) $values[$i];
                } else if (preg_match('/dev/i', $fields[$i])) { # stddev or mdev
                    $results['stddev'] = (float) $values[$i];
                }
            }
            return( $results );
        }
        // So we had no luck finding RTT info in a/b/c layout. Some ping
        // flavors give the RTT information in an "a=1 b=2 c=3" sort of
        // layout.
        $p3 = '[a-z]+\s*=\s*([0-9\.]+).*';
        for ($i = (count($lower) - 1); $i >= 0; $i--) {
            if (preg_match('/min.*max/i', $lower[$i])) {
                if (preg_match('/' . $p3 . $p3 . $p3 . '/i', $lower[$i], $matches)) {
                    $results['min'] = $matches[1];
                    $results['max'] = $matches[2];
                    $results['avg'] = $matches[3];
                }
                break;
            }
        }
        // either an array of min, max and avg from just above, or still
        // the empty array from initialization way above
        return( $results );
    }

    /**
     * determinces the target IP address actually used by ping
     *
     * @access private
     */
    function _parseResultDetailTargetIp($upper) {
        // Grab the first IP addr we can find. Most ping flavors
        // put the target IP on the first line, but some only list it
        // in successful ping packet lines.
        for ($i = 0; $i < count($upper); $i++) {
            if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $upper[$i], $matches)) {
                return( $matches[0] );
            }
        }
        // no idea...
        return( NULL );
    }

    /**
     * Locates the "packets received" in the ping output
     *
     * @access private
     */
    function _parseResultDetailTransmitted($lower) {
        for ($i = 1; $i < count($lower); $i++) {
            // the first number on the line
            if (preg_match('/^\D*(\d+)/', $lower[$i], $matches)) {
                $this->_transmitted = (int) $matches[1];
                return;
            }
        }
    }

    /**
     * determinces the time to live (TTL) actually used by ping
     *
     * @access private
     */
    function _parseResultDetailTtl($upper) {
        //extract TTL from first icmp echo line
        for ($i = 1; $i < count($upper); $i++) {
            if (preg_match('/ttl=(\d+)/i', $upper[$i], $matches) && (int) $matches[1] > 0
            ) {
                return( (int) $matches[1] );
            }
        }
        // No idea what ttl was used. Probably because no packets
        // received in reply.
        return( NULL );
    }

    /**
     * Modifies the array to temoves leading and trailing blank lines
     *
     * @access private
     */
    function _parseResultTrimLines(&$data) {
        if (!is_array($data)) {
            print_r($this);
            exit;
        }
        // Trim empty elements from the front
        while (preg_match('/^\s*$/', $data[0])) {
            array_splice($data, 0, 1);
        }
        // Trim empty elements from the back
        while (preg_match('/^\s*$/', $data[(count($data) - 1)])) {
            array_splice($data, -1, 1);
        }
    }

    /**
     * Separates the upper portion (data about individual ICMP ECHO
     * packets) and the lower portion (statistics about the ping
     * execution as a whole.)
     *
     * @access private
     */
    function _parseResultSeparateParts($data, &$upper, &$lower) {
        $upper = array();
        $lower = array();
        // find the blank line closest to the end
        $dividerIndex = count($data) - 1;
        while (!preg_match('/^\s*$/', $data[$dividerIndex])) {
            $dividerIndex--;
            if ($dividerIndex < 0) {
                break;
            }
        }
        // This is horrible; All the other methods assume we're able to
        // separate the upper (preamble and per-packet output) and lower
        // (statistics and summary output) sections.
        if ($dividerIndex < 0) {
            $upper = $data;
            $lower = $data;
            return;
        }
        for ($i = 0; $i < $dividerIndex; $i++) {
            $upper[] = $data[$i];
        }
        for ($i = (1 + $dividerIndex); $i < count($data); $i++) {
            $lower[] = $data[$i];
        }
    }

}
