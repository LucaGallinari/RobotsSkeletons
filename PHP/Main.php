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

    private $config = array (
        'name' => 'Skeleton',
        'max_hit_points' => null,           // 100
        'max_speed' => null,                // 27
        'acceleration' => null,             // 5
        'decelleration' => null,            // -5
        'max_sterling_speed' => null,       // 12
        'max_scan_distance' => null,        // 700
        'max_fire_distance' => null,        // 700
        'bullet_speed' => null,             // 500
        'bullet_damage' => null,            // ..
        'reloading_time' => null            // 2
    );

    public function run()
    {

        $this->comm = new Communications();
        $this->comm->createRobot($this->config);
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