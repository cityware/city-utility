<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

class UpTime {

    public static function calculationUpTime($localDateTime, $lastBootUpTime) {

        $fLocalDateTime = $this->convertToDateTime($localDateTime);
        $fLastBootUpTime = $this->convertToDateTime($lastBootUpTime);
        $interval = strtotime($fLocalDateTime) - strtotime($fLastBootUpTime);
        return $interval;
    }

    private function convertToDateTime($wmiData) {

        $aWmiData = explode(".", $wmiData);
        $dateTimeWmi = $aWmiData[0];

        $dateTime['Y'] = substr($dateTimeWmi, 0, 4);
        $dateTime['M'] = substr($dateTimeWmi, 4, 2);
        $dateTime['D'] = substr($dateTimeWmi, 6, 2);
        $dateTime['H'] = substr($dateTimeWmi, 8, 2);
        $dateTime['I'] = substr($dateTimeWmi, 10, 2);
        $dateTime['S'] = substr($dateTimeWmi, 12, 2);

        return $dateTime['Y'] . '-' . $dateTime['M'] . '-' . $dateTime['D'] . ' ' . $dateTime['H'] . ':' . $dateTime['I'] . ':' . $dateTime['S'];
    }

}
