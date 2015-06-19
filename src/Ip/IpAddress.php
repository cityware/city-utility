<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility\Ip;

/**
 * Description of IpAddress
 *
 * @author fabricio.xavier
 */
class IpAddress
{
    public function getRealIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public function getLocation($ip = "")
    {
        if ($ip == "") {
            $ip = $this->getRealIP();
        }
        if (!class_exists("phpFastCache")) {
            die("Please required phpFastCache Class");
        }
        // you should change this to cURL()
        $data = phpFastCache::get("codehelper_ip_" . md5($ip));
        // caching 1 week
        if ($data == null) {
            $url = "http://api.codehelper.io/ips/?php&ip=" . $ip;
            $json = file_get_contents($url);
            $data = json_decode($json, true);
            phpFastCache::set("codehelper_ip_" . md5($ip), $data, 3600 * 24 * 7);
        }

        return $data;
    }

    public function getIpAddress1()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if (validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    public function validateIp1($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        return true;
    }

    public function getIpAddress2()
    {
        global $REMOTE_ADDR;
        global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
        global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;
        global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

        if (empty($HTTP_X_FORWARDED_FOR)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'];
            } elseif (@getenv('HTTP_X_FORWARDED_FOR')) {
                $HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
            }
        } // end if
        if (empty($HTTP_X_FORWARDED)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $HTTP_SERVER_VARS['HTTP_X_FORWARDED'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $HTTP_ENV_VARS['HTTP_X_FORWARDED'];
            } elseif (@getenv('HTTP_X_FORWARDED')) {
                $HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
            }
        } // end if
        if (empty($HTTP_FORWARDED_FOR)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $HTTP_ENV_VARS['HTTP_FORWARDED_FOR'];
            } elseif (@getenv('HTTP_FORWARDED_FOR')) {
                $HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
            }
        } // end if
        if (empty($HTTP_FORWARDED)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $HTTP_SERVER_VARS['HTTP_FORWARDED'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $HTTP_ENV_VARS['HTTP_FORWARDED'];
            } elseif (@getenv('HTTP_FORWARDED')) {
                $HTTP_FORWARDED = getenv('HTTP_FORWARDED');
            }
        } // end if
        if (empty($HTTP_VIA)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
                $HTTP_VIA = $_SERVER['HTTP_VIA'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
                $HTTP_VIA = $_ENV['HTTP_VIA'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_VIA'])) {
                $HTTP_VIA = $HTTP_SERVER_VARS['HTTP_VIA'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_VIA'])) {
                $HTTP_VIA = $HTTP_ENV_VARS['HTTP_VIA'];
            } elseif (@getenv('HTTP_VIA')) {
                $HTTP_VIA = getenv('HTTP_VIA');
            }
        } // end if
        if (empty($HTTP_X_COMING_FROM)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $HTTP_SERVER_VARS['HTTP_X_COMING_FROM'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $HTTP_ENV_VARS['HTTP_X_COMING_FROM'];
            } elseif (@getenv('HTTP_X_COMING_FROM')) {
                $HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
            }
        } // end if
        if (empty($HTTP_COMING_FROM)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
            } elseif (!empty($HTTP_COMING_FROM) && isset($HTTP_SERVER_VARS['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $HTTP_SERVER_VARS['HTTP_COMING_FROM'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $HTTP_ENV_VARS['HTTP_COMING_FROM'];
            } elseif (@getenv('HTTP_COMING_FROM')) {
                $HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
            }
        } // end if
        // Get some server/environment variables values
        if (empty($REMOTE_ADDR)) {
            if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
            } elseif (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
            } elseif (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $HTTP_SERVER_VARS['REMOTE_ADDR'];
            } elseif (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $HTTP_ENV_VARS['REMOTE_ADDR'];
            } elseif (@getenv('REMOTE_ADDR')) {
                $REMOTE_ADDR = getenv('REMOTE_ADDR');
            }
        } // end if
        // Gets the proxy ip sent by the user
        $proxy_ip = '';
        if (!empty($HTTP_X_FORWARDED_FOR)) {
            $proxy_ip = $HTTP_X_FORWARDED_FOR;
        } elseif (!empty($HTTP_X_FORWARDED)) {
            $proxy_ip = $HTTP_X_FORWARDED;
        } elseif (!empty($HTTP_FORWARDED_FOR)) {
            $proxy_ip = $HTTP_FORWARDED_FOR;
        } elseif (!empty($HTTP_FORWARDED)) {
            $proxy_ip = $HTTP_FORWARDED;
        } elseif (!empty($HTTP_VIA)) {
            $proxy_ip = $HTTP_VIA;
        } elseif (!empty($HTTP_X_COMING_FROM)) {
            $proxy_ip = $HTTP_X_COMING_FROM;
        } elseif (!empty($HTTP_COMING_FROM)) {
            $proxy_ip = $HTTP_COMING_FROM;
        } // end if... else if...
        // Returns the true IP if it has been found, else FALSE

        /*
          if (!empty($REMOTE_ADDR)) {
          return $REMOTE_ADDR;
          } else {
         *
         */
        //return FALSE;
        // NOTE: the proxy addresses can be faked - shouldn't be depended on
        // for security - perhaps plugins like karma, et al. might want to use
        // this value, but all ip-based-blocking need the REMOTE_ADDR
        if (empty($proxy_ip)) {
            // True IP without proxy
            return $REMOTE_ADDR;
        } else {
            $is_ip = preg_match('/^([0-9]{1,3}\.){3,3}[0-9]{1,3}/i', $proxy_ip, $regs);
            if ($is_ip && (count($regs) > 0)) {
                // True IP behind a proxy
                return $regs[0];
            } else {
                // Can't define IP: there is a proxy but we don't have
                // information about the true IP
                return FALSE;
            }
        } // end if... else...
        //}
    }

    /**
     * FUnção de verificação de IP dentro de uma faixa pré determinada
     * @param  type  $ip
     * @param  type  $rangStart
     * @param  type  $rangFinish
     * @return array
     */
    public function checkIpRange($ip, $rangStart, $rangFinish)
    {
        $sub = explode(".", $ip);
        //rotinas para verificar se é um ip válido...
        if (count($sub) != 4) {
            return array(false, "IP Inválido");
        }
        for ($i = 0; $i < count($sub); $i++) {
            if ($sub[$i] < 0 || $sub[$i] > 255 || !is_numeric($sub[$i])) {
                return array(false, "IP Inválido");
            }
        }
        $ini = explode(".", $rangStart);
        //rotinas para verificar se é um ip válido...
        if (count($ini) != 4) {
            return array(false, "Faixa inicial inválida");
        }
        for ($i = 0; $i < count($ini); $i++) {
            if ($ini[$i] < 0 || $ini[$i] > 255 || !is_numeric($ini[$i])) {
                return array(false, "Faixa inicial inválida");
            }
        }
        $fim = explode(".", $rangFinish);
        //rotinas para verificar se é um ip válido...
        if (count($fim) != 4) {
            return array(false, "Faixa final inválida");
        }
        for ($i = 0; $i < count($fim); $i++) {
            if ($fim[$i] < 0 || $fim[$i] > 255 || !is_numeric($fim[$i])) {
                return array(false, "Faixa inicial inválida");
            }
        }
        // verificar se as faixas estao corretas
        if ($ini[0] > $fim[0]) {
            return array(false, "Faixa inicial deve ser menor que faixa final");
        }
        if ($ini[1] > $fim[1] && $ini[0] == $fim[0]) {
            return array(false, "Faixa inicial deve ser menor que faixa final");
        }
        if ($ini[2] > $fim[2] && $ini[0] == $fim[0] && $ini[1] == $fim[1]) {
            return array(false, "Faixa inicial deve ser menor que faixa final");
        }

        if ($ini[3] > $fim[3] && $ini[0] == $fim[0] && $ini[1] == $fim[1] && $ini[2] == $fim[2]) {
            return array(false, "Faixa inicial deve ser menor que faixa final");
        }

        //enfim verifica se o ip passado está dentro desta faixa...
        if ($sub[0] < $ini[0] || $sub[0] > $fim[0]) {
            return array(false, "IP fora da faixa");
        }
        if ($sub[1] < $ini[1] || $sub[1] > $fim[1]) {
            if ($sub[0] > !$fim[0] || $sub[0] < !$ini[0]) {
                return array(false, "IP Fora da Faixa");
            }
        }
        if ($sub[2] < $ini[2] || $sub[2] > $fim[2]) {
            if ($sub[1] > !$fim[1] || $sub[1] < !$ini[1]) {
                if ($sub[0] > !$fim[0] || $sub[0] < !$ini[0]) {
                    return array(false, "IP Fora da Faixa");
                }
            }
        }

        if ($sub[3] < $ini[3] || $sub[3] > $fim[3]) {
            if ($sub[2] > !$fim[2] || $sub[2] < !$ini[2]) {
                if ($sub[1] > !$fim[1] || $sub[1] < !$ini[1]) {
                    if ($sub[0] > !$fim[0] || $sub[0] < !$ini[0]) {
                        return array(false, "IP Fora da Faixa");
                    }
                }
            }
        }

        return array(true);
    }

}
