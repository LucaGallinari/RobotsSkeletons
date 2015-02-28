<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 18/10/14
 * Time: 15.24
 */

class Communications
{
    /**
     * @var string
     */
    private $server;

    private $connection;

    private $token = null;

    private $defaultConfig = array (
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

    public function __construct($serverAddress, $serverPort)
    {
        $this->server = 'http://'. $serverAddress .':'. $serverPort .'/v1/';
        $this->connection = curl_init();
    }

    public function createRobot(array $config = array())
    {
        $config = array_merge($this->defaultConfig, $config);
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

    public function getStatus()
    {
        $res = $this->doRequest(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->server."robot/".$this->token,
                CURLOPT_CUSTOMREQUEST => "GET",
            )
        );

        $robot = $res->robot;

        if ($robot->dead) {
            throw new Exception('Dead :(');
        }

        return $robot;
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

    /**
     * @param array $params
     * @return stdClass
     * @throws Exception
     */
    private function doRequest(array $params)
    {
        curl_setopt_array($this->connection, $params);

        $res = curl_exec($this->connection);

        if ($res === false) {
            throw new Exception('Curl error (code: '.curl_errno($this->connection).')');
        }

        $httpStatusCode = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);

        if ($httpStatusCode !== 200) {
            throw new Exception("Http error received ($httpStatusCode). Body: $res", $httpStatusCode);
        }

        if (!$res) {
            throw new Exception('Unknown error in request!');
        }

        $resObject = json_decode($res);

        if (!$resObject) {
            throw new Exception("Could not decode json $res");
        }

        if ($resObject->status !== 'OK') {
            throw new Exception("Not ok");
        }

        return $resObject;
    }
} 
