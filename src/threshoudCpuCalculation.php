<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of threshoudCpuCalculation
 *
 * @author fsvxavier
 */
final class threshoudCpuCalculation {
    
    /**
     * Return calculation Threshoud alert CPU
     * @param integer $numSlots
     * @param integer $numCores
     * @param string $ht
     * @return array
     */
    public static function calculate($numSlots = 1, $numCores = 1, $ht = 'N') {
        $totalCpu = ($ht == 'S') ? (($numSlots * $numCores) * 2) : ($numSlots * $numCores);

        $aReturn = Array();
        $aReturn['warning'] = Array();
        $aReturn['critical'] = Array();

        $aReturn['warning']['load1min'] = round(($totalCpu * 0.7), 2);
        $aReturn['warning']['load5min'] = round(($totalCpu * 0.65), 2);
        $aReturn['warning']['load15min'] = round(($totalCpu * 0.6), 2);

        $aReturn['critical']['load1min'] = round(($totalCpu * 1.0), 2);
        $aReturn['critical']['load5min'] = round(($totalCpu * 0.9), 2);
        $aReturn['critical']['load15min'] = round(($totalCpu * 0.8), 2);

        return $aReturn;
    }
}
