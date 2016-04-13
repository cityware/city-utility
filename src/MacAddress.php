<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

final class MacAddress {

    public static function getMacAddres() {

        $condition = "/(?:[A-Fa-f0-9]{2}[:-]){5}(?:[A-Fa-f0-9]{2}){0,17}/im";
        
        $condition = "/(?:[a-fA-F0-9]{2}[:-]?){6}/im";

        switch (PHP_OS) {
            case 'WINDOWS':
            case 'Windows':
            case 'WINNT':
            case 'WinNT':
            case 'WIN':
            case 'Win':
                $command_name = "ipconfig /all ";
                break;
            default:
                $command_name = "/sbin/ifconfig ";
                break;
        }
        $command_result = $ifip = null;
        $match = Array();
        exec($command_name, $command_result);
        $ifmac = implode($command_result, "\n");

        preg_match_all($condition, $ifmac, $match, PREG_PATTERN_ORDER);

        if (isset($match[0]) and ! empty($match[0])) {
            foreach ($match[0] as $key => $value) {
                if (preg_match("/" . $value . "[:-]/im", $ifmac)) {
                    unset($match[0][$key]);
                }
            }
            return $match[0];
        } else {
            return false;
        }
    }

    public static function AddSeparator($mac, $separator = ':') {
        return join($separator, str_split($mac, 2));
    }

    public static function IsValid($mac) {
        return (preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac) == 1);
    }

}
