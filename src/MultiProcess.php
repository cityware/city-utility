<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of MultiProcess
 *
 * @author fabricio.xavier
 */
class MultiProcess {

    protected $processes = array();
    protected $workQueue = array();
    protected $callback; 

    public function __construct($data, $callback) {

        if (!function_exists('pcntl_fork')) {
            throw new \Exception('PCNTL functions not available on this PHP installation');
        }

        $this->workQueue = $data;
        $this->callback = $callback;
    }

    public function run($concurrent = 5) {

        $this->completed = 0;
        foreach ($this->workQueue as $data) {

            $pid = pcntl_fork(); // clone

            switch ($pid) {
                case -1:
                    throw new \Exception("Out of memory!");
                case 0:
                    // child process
                    call_user_func($this->callback, $data);
                    exit(0);
                default:
                    // parent process
                    $this->processes[$pid] = TRUE; // log the child process ID
            }

            // wait on a process to finish if we're at our concurrency limit
            while (count($this->processes) >= $concurrent) {
                $this->reapChild();
                usleep(500);
            }
        }

        // wait on remaining processes to finish
        while (count($this->processes) > 0) {
            $this->reapChild();
            usleep(500);
        }
    }

    protected function reapChild() {

        // check if any child process has terminated,
        // and if so remove it from memory
        $pid = pcntl_wait($status, WNOHANG);

        if ($pid < 0) {
            throw new \Exception("Error: out of memory!");
        } elseif ($pid > 0) {
            unset($this->processes[$pid]);
        }
    }

}


/*
Example
function myFunctionToExecuteMultipleTimes($dataArray){
    // code to execute
}

$oMultiProc = new Multiprocess($dataArray, ‘myFunctionToExecuteMultipleTimes’);
$oMultiProc->run(20); // run 20 processes on the same time

 */