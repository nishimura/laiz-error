<?php

namespace Laiz\Error;

class Tracer
{
    public static function trace($lines)
    {
        $ret = array();
        $count = count($lines);
        for ($i = 0; $i < $count; $i++){
            if (!isset($lines[$i]['file']))
                continue;

            if (__DIR__ === dirname($lines[$i]['file']))
                continue;
            if (isset($lines[$i]['class']) &&
                substr($lines[$i]['class'], 0, 10) === 'Laiz\Error')
                continue;

            $line = self::parseLine($lines[$i]);
            $ret[] = $line;
        }
        return $ret;
    }

    private static function parseLine($line)
    {
        if (!isset($line['line'])){ $line['line'] = ''; }
        if (!isset($line['args'])){ $line['args'] = array(); }

        // convert arguments to string
        $args = array();
        foreach ($line['args'] as $key => $arg){
            $type = gettype($arg);
            if (is_array($arg)){
                $arg = 'array array(' . count($arg) . ')';
            }else if ($type === 'object'){
                $arg = 'object ' . get_class($arg);
            }else if ($type === 'string'){
                $arg = "string '$arg'";
            }else if ($type === 'boolean'){
                $arg = "boolean " . ($arg ? 'true' : 'false');
            }else{
                $arg = "$type " . (string)$arg;
            }
            $args[$key] = $arg;
        }

        if (isset($line['class']) && isset($line['type'])){
            $function = "$line[class]$line[type]$line[function](".implode(', ', $args).')';
        }else{
            $function = "$line[function](".implode(', ', $args).')';
        }
        return "$function:\n\t in $line[file] on line $line[line]";
    }
}
