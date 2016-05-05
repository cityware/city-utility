<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of NetDisp
 *
 * @author fsvxavier
 */
class NetDisp {

    private $fileHosts;
    private $fileLinks;
    private $source;
    private $target;
    private $debug;

    public function __construct($fileHosts, $fileLinks, $source = null, $target = null, $debug = false) {
        $this->fileHosts = $fileHosts;
        $this->fileLinks = $fileLinks;
        $this->source = $source;
        $this->target = $target;
        $this->debug = $debug;

        $this->prepareData();
    }

    private function prepareData() {

        if (!is_writable($this->fileHosts)) {
            throw new \Exception('Arquivo de hosts não encontrado!');
        }

        if (!is_writable($this->fileLinks)) {
            throw new \Exception('Arquivo de links não encontrado!');
        }

        if (empty($this->source)) {
            throw new \Exception('Host de origem não definido!');
        }

        if (empty($this->target)) {
            throw new \Exception('Host de destino não definido!');
        }

        $fileHosts = \Zend\Config\Factory::fromFile($this->fileHosts);
        $aHosts = $fileHosts['hosts']['host'];

        $fileLinks = \Zend\Config\Factory::fromFile($this->fileLinks);
        $aLinks = $fileLinks['links']['link'];


        foreach ($aHosts['nome'] as $keyHostsDef => $valueHostsDef) {
            $disponibilidadeHost[$valueHostsDef] = $aHosts['disponibilidade'][$keyHostsDef];
            $statusHost[$valueHostsDef] = $aHosts['ativo'][$keyHostsDef];
        }

        foreach ($aLinks['nome'] as $keyLinksDef => $valueLinksDef) {
            $disponibilidadeLink[$valueLinksDef] = $aLinks['disponibilidade'][$keyLinksDef];
            $statusLink[$valueLinksDef] = $aLinks['ativo'][$keyLinksDef];
        }

        if ($this->debug) {
            echo '<pre>';
        }

        $connectionMatrix = $this->connectionMatrix($aHosts, $aLinks);
        $somaDisp = 0;
        $totalHosts = pow(2, count($aHosts['nome'])) - 1;
        $totalLinks = pow(2, count($aLinks['nome'])) - 1;

        if ($this->debug) {
            echo "\n\nTotal Hosts testados: {$totalHosts}\n";
            echo "\n\nBinario Hosts: {$this->dec2bin($totalHosts)}\n";
            echo "\n\nTotal Links testados: {$totalLinks}\n";
            echo "\n\nBinario Links: {$this->dec2bin($totalLinks)}\n";
            echo "\n\nPercentual a executar:\n";
        }

        for ($igl = $totalHosts; $igl >= 1; $igl--) {
            if ($this->debug) {
                $percentual = 100 * $igl / $totalHosts;
                echo sprintf("%.3f", $percentual) . "%   <br>";
            }
            if ($igl < 2147483647) {
                $binstrHost = $this->dec2bin($igl);
            } else {
                $binstrHost = $this->dec2binGd($igl);
            }

            foreach ($statusHost as $keyStatusHost => $valueStatusHost) {
                $statusHost[$keyStatusHost] = substr($binstrHost, -1);
                $binstrHost = substr($binstrHost, 0, strlen($binstrHost) - 1);
            }
            if ($statusHost[$this->source] == '0') {
                continue;
            }
            if ($statusHost[$this->target] == '0') {
                continue;
            }
            for ($jgl = $totalLinks; $jgl >= 1; $jgl--) {
                if ($jgl < 2147483647) {
                    $binstrLink = $this->dec2bin($jgl);
                } else {
                    $binstrLink = $this->dec2binGd($jgl);
                }

                foreach ($statusLink as $keyStatusLink => $valueStatusLink) {
                    $statusLink[$keyStatusLink] = substr($binstrLink, -1);
                    $binstrLink = substr($binstrLink, 0, strlen($binstrLink) - 1);
                }


                $conecta = $this->testaConexao($connectionMatrix, $statusLink, $statusHost);

                if (($jgl == $totalLinks) AND ( $conecta == 0)) {
                    continue 2;
                }

                if ($conecta == 1) {
                    $prodDisp = 1;

                    //echo '<pre>';
                    //print_r($statusHost);

                    foreach ($statusHost as $keyStatusHost => $valueNodeHost) {
                        if (intval($valueNodeHost) == 1) {
                            $prodDisp = bcmul($prodDisp, floatval($disponibilidadeHost[$keyStatusHost]), 64);
                        } else {
                            $prodDisp = bcmul($prodDisp, bcsub(1, floatval($disponibilidadeHost[$keyStatusHost]), 64), 64);
                        }
                        //echo $prodDisp." - Host: {$keyStatusHost} <br>";
                    }

                    foreach ($statusLink as $keyStatusLink => $valueStatusLink) {
                        if (intval($valueStatusLink) == 1) {
                            $prodDisp = bcmul($prodDisp, floatval($disponibilidadeLink[$keyStatusLink]), 64);
                        } else {
                            $prodDisp = bcmul($prodDisp, bcsub(1, floatval($disponibilidadeLink[$keyStatusLink]), 64), 64);
                        }
                        //echo $prodDisp." - Link: {$keyStatusLink} <br>";
                    }
                    //exit;
                    $somaDisp = bcadd($somaDisp, $prodDisp, 64);
                }
            }
        }


        print "<br><br>A Disponibilidade da rede entre os pontos " . $this->source . " e " . $this->target . " totaliza: " . round($somaDisp, 5) . "\n";
        print "<br><br>A Disponibilidade da rede entre os pontos " . $this->source . " e " . $this->target . " totaliza: " . $somaDisp . "\n";

        exit;
    }

    /**
     * Função de criação da matrix de conexões
     * @param array $aHosts
     * @param array $aLinks
     * 
     * @return array
     */
    private function connectionMatrix(array $aHosts, array $aLinks) {

        $connectionMatrix = Array();

        if ($this->debug) {
            echo "Matriz de conexoes:<br><br>";
        }

        foreach ($aHosts['nome'] as $valueHosts) {
            $connectionMatrix[$valueHosts] = null;

            if ($this->debug) {
                echo "\n" . $valueHosts . ":";
            }

            foreach ($aLinks as $keyLinks => $valueLinks) {

                if ($keyLinks == 'destino') {
                    foreach ($aLinks['origem'] as $keyLinksOrigem => $valueLinksOrigem) {
                        if ($valueLinksOrigem == $valueHosts) {
                            $connectionMatrix[$valueHosts][$aLinks['nome'][$keyLinksOrigem]] = $valueLinks[$keyLinksOrigem];
                            if ($this->debug) {
                                echo $aLinks['nome'][$keyLinksOrigem] . "," . $valueLinks[$keyLinksOrigem] . ";";
                            }
                        }
                    }
                }

                if ($keyLinks == 'origem') {
                    foreach ($aLinks['destino'] as $keyLinksDestino => $valueLinksDestino) {
                        if ($valueLinksDestino == $valueHosts) {
                            $connectionMatrix[$valueHosts][$aLinks['nome'][$keyLinksDestino]] = $valueLinks[$keyLinksDestino];
                            if ($this->debug) {
                                echo $aLinks['nome'][$keyLinksDestino] . "," . $valueLinks[$keyLinksDestino] . ";";
                            }
                        }
                    }
                }
            }
            if ($this->debug) {
                echo "<br>";
            }
        }

        return $connectionMatrix;
    }

    /**
     * Conversor de Decimal para Binário maior que 32bits
     * @param integer $decimal
     * @return integer
     */
    private function dec2binGd($decimal) {
        //bcscale(0);
        $binary = null;
        do {
            $binary = bcmod($decimal, '2', 0) . $binary;
            $decimal = bcdiv($decimal, '2', 0);
        } while (bccomp($decimal, '0', 0));
        $binaryReturn = str_pad($binary, 64, "0", STR_PAD_LEFT);
        return $binaryReturn;
    }

    /**
     * Conversor de Decimal para Binário até 32bits
     * @param integer $value
     * @return integer
     */
    private function dec2bin($value) {
        $str = str_pad(decbin($value), 32, "0", STR_PAD_LEFT);
        return $str;
    }

    private function testaConexao($matriz, $statusLink, $statusHost) {
        $proximo = $this->source;
        $aux = 1;
        $anterior = Array();
        $testado = Array();
        $ultimo = null;


        while ($proximo != $this->target) {
            $achei = 0;

            if (isset($matriz[$proximo])) {
                foreach ($matriz[$proximo] as $linkSub => $destino) {

                    if (isset($testado[$linkSub])) {
                        continue;
                    }
                    if ($achei == 0) {
                        for ($j = $aux; $j > 0; $j--) {
                            if (isset($matriz[$proximo][$linkSub]) and isset($anterior[$j]) and ( $matriz[$proximo][$linkSub] == $anterior[$j])) {
                                continue 2;
                            }
                        }

                        if ((isset($statusLink[$linkSub]) and $statusLink[$linkSub] == 1 ) AND ( isset($statusHost[$matriz[$proximo][$linkSub]]) and $statusHost[$matriz[$proximo][$linkSub]] == 1)) {
                            $anterior[$aux] = $proximo;
                            $testado[$linkSub] = 1;
                            $ultimo = $proximo;
                            $proximo = $matriz[$proximo][$linkSub];
                            $achei = 1;
                            $aux++;
                        }
                    }

                    if (($achei == 1) AND (isset($statusLink[$linkSub]) and $statusLink[$linkSub] == 1)) {
                        if ($matriz[$ultimo][$linkSub] == $this->target) {
                            $proximo = $matriz[$ultimo][$linkSub];
                            return 1;
                        }
                    }
                }
            }
            if ($achei == 0) {
                if (($proximo == $this->source) OR ( $aux == 0)) {
                    return 0;
                }
                if (isset($anterior[$aux])) {
                    $proximo = $anterior[$aux];
                } else {
                    $proximo = null;
                }
                $aux--;
            }
            if ($proximo == $this->target) {
                return 1;
            }
        }
    }

}
