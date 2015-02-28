<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 31/01/15
 * Time: 15.31
 *
 * Skeleton PHP project for creating your personal robot
 */

require_once 'Communications.php';
require_once 'TargetLostException.php';

class Main
{
    /** @var Communications */
    private $comm = null;
    private $status;

    public function run()
    {
        $this->comm = new Communications('192.168.1.13', '8080');
        $this->comm->createRobot();
        echo "ciao";
        try {
            while (true) {
                $this->status = $this->comm->getStatus();
                echo $this->status->token;
                $direction = rad2deg(atan2(500 - $this->status->y, 500 - $this->status->x));
                $scan = $this->comm->scan($direction, 90);
                if (!$this->status->reloading && $scan->distance > 40) {
                    $this->comm->cannon($direction, $scan->distance);
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage().PHP_EOL;
        }
        $this->comm->deleteRobot();
    }

    public static function distance($x1, $y1, $x2, $y2)
    {
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        return sqrt($dx*$dx + $dy*$dy);
    }
}

$m = new Main();
$m->run();
