<?php
class MadnessDebug {
    protected $_debug = 0;

    const DEBUG_MYSQL = 1;
    const DEBUG_CONDITION = 2;
    const DEBUG_CHECKER = 4;
    const DEBUG_REPORT = 8;
    const DEBUG_ERROR = 16;
    const DEBUG_DEBUG = 32;

    protected $_colors = [
        0=> "\e[38;5;255m",//white
        MadnessDebug::DEBUG_MYSQL => "\e[38;5;196m", //red
        MadnessDebug::DEBUG_CONDITION => "\e[38;5;165m", //purple
        MadnessDebug::DEBUG_CHECKER => "\e[38;5;208m", //orange
        MadnessDebug::DEBUG_REPORT => "\e[38;5;63m" //blue
    ];


    public function setDebug($debug) {
        $this->_debug = $debug;
    }

    public function checkDebug($lvl) {
        return (($this->_debug & $lvl) || ($this->_debug == -1));
    }

    public function debugCall($name, $args) {
        $real_name = $name;
        if($name[0] != "_") {
            $real_name = "_".$name;
        }

        if(method_exists($this, $real_name)) {
            if($this->checkDebug(MadnessDebug::DEBUG_DEBUG)) {
                $str_args = [];
                $tmp_color = $this->_colors;
                foreach($args as $a) {
                    $str_args[] = next($tmp_color).var_export($a, true).$tmp_color[0];
                }

                $caller_str = "";
                $line = "undefined";
                $trace = debug_backtrace();
                for($i = 1, $l = count($trace); $i<$l; $i++) {
                    $caller = $trace[$i];
                    $function = $caller['function'];
                    $class = (isset($caller['class']) ? $caller['class']."::" : "");
                    if(stripos("__call", $function) === false && (get_class($this)."::".$name) != $class.$caller['function']) {
                        break;
                        $caller_str = $class.$caller['function'];
                    }
                }
                if(array_key_exists('line', $caller)){
                    $line = $caller['line'];
                }                

                $this->displayDebug("run ".get_class($this)."::".$name." with args (".implode(', ', $str_args).") \n FROM : ".$caller_str."  at line : ".$line);
                unset($str_args);
            }
            $start = $this->microtime_float();
            
            $ret = call_user_func_array(array($this, $real_name), $args);
            $end = $this->microtime_float();
            
            if($this->checkDebug(MadnessDebug::DEBUG_DEBUG)) {
                $this->displayDebug(get_class($this)."::".$name." terminate in : ".( ($end*10000)-($start*10000) )."ms  with this result \e[38;5;46m".var_export($ret, true)."\e[38;5;255m");   
            }
            return $ret;
        } else {
            die("trow error   :  ".$name);
        }
    }

    public function displayDebug($str, $type = 0) {
        if($type > 0) {
            $str = preg_replace('/\[([A-Z0-9 ]+)\]/i', $this->_colors[$type]."[$1]".$this->_colors[0], $str);
        }
        echo "\n\e[38;5;46m [DEBUG]: \e[38;5;255m".$str."\n";
    }

    public function __call($name, $args) {
        return $this->debugCall($name, $args);
    }
    
    public function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
