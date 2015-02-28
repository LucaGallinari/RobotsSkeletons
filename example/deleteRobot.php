<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 31/01/15
 * Time: 15.49
 */

use NetrobotsClient\Communications;

$comm = new Communications('192.168.1.13', '8080');
$comm->deleteRobot($argv[1]);
