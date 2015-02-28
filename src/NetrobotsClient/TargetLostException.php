<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 31/01/15
 * Time: 17.16
 */

namespace NetrobotsClient;

class TargetLostException extends \Exception
{
    private $direction;
    private $distance;

    public function __construct($direction, $distance)
    {
        $this->direction = $direction;
        $this->distance = $distance;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
