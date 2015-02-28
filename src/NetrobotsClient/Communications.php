<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 18/10/14
 * Time: 15.24
 */

namespace NetrobotsClient;

class Communications
{
    const MAX_SPEED = 27;

    /**
     * @var string
     */
    private $server;

    private $connection;

    private $token = null;

    private $status;

    public function __construct($serverAddress, $serverPort)
    {
        $this->server = 'http://'. $serverAddress .':'. $serverPort .'/v1/';
        $this->connection = curl_init();
    }

    /**
     * Default config is the following.
     *
     *  'max_hit_points' => 100
     *  'max_speed' => 27
     *  'acceleration' => 5
     *  'decelleration' => -5
     *  'max_sterling_speed' => 12
     *  'max_scan_distance' => 700
     *  'max_fire_distance' => 700
     *  'bullet_speed' => 500
     *  'bullet_damage' => (40, 3); (20, 2); (5, 5)
     *  'reloading_time' => 2
     *
     * @param $name
     */
    public function createRobot($name)
    {
        $config = array();
        $config['name'] = $name;
        $res = $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/",
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $config,
            )
        );

        $this->token = $res->token;
        echo $this->token.PHP_EOL;
    }

    public function deleteRobot($token = null)
    {
        $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/".($token ?: $this->token),
                CURLOPT_CUSTOMREQUEST => "DELETE",
            )
        );

        $this->token = null;
    }

    /**
     * Returns the current robot status.
     *
     *  name
     *  hp
     *  heading
     *  speed
     *  x
     *  y
     *  dead
     *  winner
     *  max_speed
     *  scanning
     *  reloading
     *  bursting
     *  reloading_timer
     *
     * @return \stdClass Current robot status
     * @throws \Exception
     */
    public function getStatus()
    {
        $res = $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/".$this->token,
                CURLOPT_CUSTOMREQUEST => "GET",
            )
        );

        $this->status = $res->robot;

        if ($this->status->dead) {
            throw new \Exception('Dead :(');
        }

        return $this->status;
    }

    public function drive($speed, $heading)
    {
        return $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/".$this->token.'/drive',
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => array(
                    'speed' => round($speed),
                    'degree' => round($heading),
                ),
            )
        );
    }

    public function scan($direction, $semiaperture)
    {
        return $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/".$this->token.'/scan',
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => array(
                    'degree' => round($direction),
                    'resolution' => round($semiaperture),
                ),
            )
        );
    }

    public function cannon($direction, $distance)
    {
        return $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/".$this->token.'/cannon',
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => array(
                    'degree' => round($direction),
                    'distance' => round($distance),
                ),
            )
        );
    }

    public function driveTo($x, $y, $speed = self::MAX_SPEED)
    {
        $this->x = $x;
        $this->y = $y;

        $prevTime = null;

        $startX = $this->status->x;
        $startY = $this->status->y;

        $heading = rad2deg(atan2($y - $startY, $x - $startX));
        $this->drive($speed, $heading);
    }

    /**
     * @param array $params
     * @throws \Exception
     * @return \stdClass
     */
    private function doRequest(array $params)
    {
        curl_setopt_array($this->connection, $params);

        $res = curl_exec($this->connection);

        if ($res === false) {
            throw new \Exception('Curl error (code: '.curl_errno($this->connection).')');
        }

        $httpStatusCode = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);

        if ($httpStatusCode !== 200) {
            throw new \Exception("Http error received ($httpStatusCode). Body: $res", $httpStatusCode);
        }

        if (!$res) {
            throw new \Exception('Unknown error in request!');
        }

        $resObject = json_decode($res);

        if (!$resObject) {
            throw new \Exception("Could not decode json $res");
        }

        if ($resObject->status !== 'OK') {
            throw new \Exception("Not ok");
        }

        return $resObject;
    }
} 
