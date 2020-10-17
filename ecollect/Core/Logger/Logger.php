<?php
namespace ecollect\Core\Logger;
/**
 * custom logger allows name changing to differentiate log call origin
 * Class Logger
 *
 * @package ecollect\Core\Logger
 */
class Logger
    extends \Monolog\Logger
{

    /**
     * Set logger name
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}