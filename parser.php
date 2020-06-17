<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class parser {
    public static function parse($handle, callable $callback) {
        $order = '';
        while (($f = fgets($handle)) !== FALSE) {
            $line = rtrim($f, "\n\r");
            $matches = NULL;
            if (preg_match('/(.*)\\\s*$/', $line, $matches) === 1) {
                $order .= $matches[1];
            }
            else {
                $order .= $line;
                $matches = NULL;
                $callback($order);
                $order = '';
            }
        }
    }
}