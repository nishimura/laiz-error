<?php

namespace Laiz\Error;

class Mail
{
    private $mailto;
    private $level;
    public function __construct($mailto, $level)
    {
        $this->mailto = $mailto;
        $this->level = $level;
    }

    public function errorHandler($errno, $errstr,
                                 $errfile = null, $errline = null,
                                 $errcontext = null)
    {
        $reporting = error_reporting();
        $level = $this->level & $reporting & $errno;
        if ($level === 0)
            return true;

        $error = E_ERROR | E_USER_ERROR;
        $warning = E_WARNING | E_USER_WARNING;
        $notice = E_NOTICE | E_USER_NOTICE;
        $strict = E_STRICT;
        $deprecated = E_DEPRECATED;
        $label = 'Unknown Error';
        switch (true){
        case ($level & $error):
            $label = 'Error';
            break;
        case ($level & $warning):
            $label = 'Warning';
            break;
        case ($level & $notice):
            $label = 'Notice';
            break;
        case ($level & $strict):
            $label = 'Strict';
            break;
        case ($level & $deprecated):
            $label = 'Deprecated';
            break;
        }

        $data = Tracer::trace(debug_backtrace());
        $this->send($label, $errstr, $errfile, $errline, $data);
        return true;
    }

    public function exceptionHandler($exception)
    {
        $data = Tracer::trace($exception->getTrace());
        $this->send('Exception',
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $data);
    }
    public function shutdownHandler()
    {
        $error = error_get_last();
        if ($error === null)
            return;

        $catch = E_ERROR | E_PARSE | E_CORE_ERROR |
            E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
        if ($error & $catch){
            $data = array('Fatal Error',
                          'See server log');
            $this->send('Shutdown', $error['message'],
                        $error['file'], $error['line'], $data);
        }
    }

    private function send($level, $msg, $file, $line, $data)
    {
        $subject = "[$level]: $msg in $file at $line";
        $body = implode("\n", $data);

        if (isset($_SERVER)){
            $body .= "\n\n";
            $body .= '$_SERVER:' . "\n";
            $body .= var_export($_SERVER, true);
        }
        if (isset($_REQUEST)){
            $body .= "\n\n";
            $body .= '$_REQUEST:' . "\n";
            $body .= var_export($_REQUEST, true);
        }

        return mb_send_mail($this->mailto, $subject, $body);
    }

    public function bindError()
    {
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'shutdownHandler'));
        return $this;
    }
    public function bindException()
    {
        set_exception_handler(array($this, 'exceptionHandler'));
        return $this;
    }
}
