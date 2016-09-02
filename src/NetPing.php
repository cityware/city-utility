<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of NetPing
 *
 * @author fsvxavier
 */
class NetPing {

    var $icmp_socket;
    var $request;
    var $request_len;
    var $reply;
    var $errstr;
    var $time;
    var $timer_start_time;

    public function __contruct() {
        $this->icmp_socket = socket_create(AF_INET, SOCK_RAW, 1);
        socket_set_block($this->icmp_socket);
    }

    public function ip_checksum($data) {
        $sum = 0;
        for ($i = 0; $i < strlen($data); $i += 2) {
            if ($data[$i + 1]){
                $bits = unpack('n*', $data[$i] . $data[$i + 1]);
            } else {
                $bits = unpack('C*', $data[$i]);
            }
            $sum += $bits[1];
        }

        while ($sum >> 16){
            $sum = ($sum & 0xffff) + ($sum >> 16);
        }
        $checksum = pack('n1', ~$sum);
        return $checksum;
    }

    public function start_time() {
        $this->timer_start_time = microtime();
    }

    public function get_time($acc = 2) {
        // format start time
        $start_time = explode(" ", $this->timer_start_time);
        $start_time = $start_time[1] + $start_time[0];
        // get and format end time
        $end_time = explode(" ", microtime());
        $end_time = $end_time[1] + $end_time[0];
        return number_format($end_time - $start_time, $acc);
    }

    public function Build_Packet() {
        $data = "abcdefghijklmnopqrstuvwabcdefghi"; // the actual test data
        $type = "\x08"; // 8 echo message; 0 echo reply message
        $code = "\x00"; // always 0 for this program
        $chksm = "\x00\x00"; // generate checksum for icmp request
        $id = "\x00\x00"; // we will have to work with this later
        $sqn = "\x00\x00"; // we will have to work with this later
        // now we need to change the checksum to the real checksum
        $chksm = $this->ip_checksum($type . $code . $chksm . $id . $sqn . $data);
        // now lets build the actual icmp packet
        $this->request = $type . $code . $chksm . $id . $sqn . $data;
        $this->request_len = strlen($this->request);
    }

    public function Ping($dst_addr, $src_addr = "", $timeout = 5, $precision = 3) {
        // lets catch dumb people
        if ($src_addr <> "") { //if there is no source, then ping via default interface
            if (!socket_bind($this->icmp_socket, $src_addr, 0)) {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                die("Couldn't bind socket: [$errorcode] $errormsg\n");
            }
        }
        if ((int) $timeout <= 0){
            $timeout = 5;
        }
        if ((int) $precision <= 0){
            $precision = 3;
        }
        // set the timeout
        socket_set_option($this->icmp_socket, SOL_SOCKET, // socket level
                SO_RCVTIMEO, // timeout option
                array(
            "sec" => $timeout, // Timeout in seconds
            "usec" => 0 // I assume timeout in microseconds
                )
        );
        if ($dst_addr) {
            if (@socket_connect($this->icmp_socket, $dst_addr, NULL)) {
                
            } else {
                $this->errstr = "Cannot connect to $dst_addr\n";
                return FALSE;
            }
            $this->Build_Packet();
            $this->start_time();
            socket_write($this->icmp_socket, $this->request, $this->request_len);
            if (@socket_recv($this->icmp_socket, $this->reply, 256, 0)) {
                $this->time = $this->get_time($precision);
                return $this->time;
            } else {
                $this->errstr = "Timed out";
                return FALSE;
            }
        } else {
            $this->errstr = "Destination address not specified";
            return FALSE;
        }
    }

}
