<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 31/01/15
 * Time: 15.49
 */

include 'Communications.php';

$comm = new Communications();
$comm->deleteRobot($argv[1]);