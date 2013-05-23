<?php

namespace Laiz\Error;

use Zend\Stdlib\RequestInterface as Request;

class Binder
{
    const APPLICATION_ENV = 'APPLICATION_ENV';
    const ENV_DEV = 'development';

    public function __construct(Request $request, $iniFile = 'config/error.ini')
    {
        $config = parse_ini_file($iniFile, true);
        $env = $request->getServer(self::APPLICATION_ENV, self::ENV_DEV);
        if (isset($config[$env]))
            $this->bind($config[$env]);
    }

    private function bind($config)
    {
        $mailto = $config['mail'];
        $level = $config['level'];

        $mailHandler = new Mail($mailto, $level);
        $mailHandler->bindError()->bindException();
    }
}
