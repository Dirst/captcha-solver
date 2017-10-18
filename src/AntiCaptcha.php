<?php

namespace Dirst\CaptchaSolver;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Dirst\CaptchaSolver\Exception\CaptchaSolverRequestException;
use Dirst\CaptchaSolver\Exception\CaptchaSolverErrorException;

/**
 * AntiCaptcha Service Solver
 *
 * @author Dirst <dirst.guy@gmail.com>
 * @version 1.0
 */
class AntiCaptcha implements CaptchaSolverInterface
{
    // @var string captcha api endpoint.
    protected $apiEndpoint = 'https://api.anti-captcha.com';

    // @var string Api key for app.
    private $apiKey;
    
    // @var ClientInterface Http Client
    private $client;
    
    // @var array anti captcha parameters.
    private $parameters;
    
    /**
     * Constructs AntiCaptcha Object.
     *
     * @param string $apiKey
     *   Api Key for service.
     * @param ClientInterface $client
     *   Http Client.
     * @param array $antiCaptchaParams
     *   Parameters for anticaptcha.
     *   ImageToTextTask parameters Only.
     */
    public function __construct($apiKey, ClientInterface $client, $antiCaptchaParams = [])
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
        $this->parameters = $antiCaptchaParams;
    }

    /**
     * {@inheritdoc}
     */
    public function sendCaptchaImage(&$image)
    {
        $captchaTask['clientKey'] = $this->apiKey;
        $captchaTask['task'] = [
            "type" => "ImageToTextTask",
            "body" => base64_encode($image),
            "phrase" => false,
            "case" => true,
            "numeric" => false,
            "math" => 0,
            "minLength" => 0,
            "maxLength" => 0
        ];
        
        // Merge parameters.
        $captchaTask = array_merge($captchaTask, $this->parameters);

        // Try to send captcha to service.
        try {
            return $this->requestToCaptchaApi("createTask", $captchaTask);
        } catch (RequestException $e) {
            $requestStr = Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                $responseStr = Psr7\str($e->getResponse());
            }
            
            // Throw CaptchaSolver Exception
            throw new CaptchaSolverRequestException(
                "Request is failed: Request - $requestStr. Response - $responseStr"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptchaSolution($id, $waitSolutionSec = 5, $maxSolutionWaitSec = 120)
    {
        $captchaTask = [];

        // Do while processing is still running.
        $timeToStop = time() + $maxSolutionWaitSec;

        // Check if time limit is not exceed. Needs to prevent infinite loop just in case.
        while ($timeToStop > time()) {
            // Wait before get solution.
            sleep($waitSolutionSec);

            // Get the result of captcha.
            $captchaTask['clientKey'] = $this->apiKey;
            $captchaTask['taskId'] = $id;
            $responseResult = $this->requestToCaptchaApi('getTaskResult', $captchaTask);
 
            // Check if status is ready.
            if ($responseResult['status'] == 'ready') {
                return $responseResult['solution']['text'];
            }
        }

        // If time limit has exceed.
        throw new CaptchaSolverErrorException(
            "Time for solution wait has exceed. "
            . "Status of the task is {$responseResult['status']}"
        );
    }


    /**
     * Request To captcha Api.
     *
     * @param string $method
     *   Method to request in service.
     * @param array $data
     *   Data to send to service.
     *
     * @return array
     *   Array of data from service.
     *
     * @throws CaptchaNotSolvedException
     *   Thrown if Not possible to solve a Captcha.
     */
    protected function requestToCaptchaApi($method, array $data)
    {
        $responseResult = $this->client->post($this->apiEndpoint . "/" . $method, ['json' => $data])->getBody();
        $responseResult = json_decode($responseResult, true);

        // Check if error is occured.
        if ($responseResult['errorId'] != 0) {
            throw new CaptchaSolverErrorException(
                "Couldn't solve captcha. Error {$responseResult['errorCode']} -"
                . " {$responseResult['errorDescription']}"
            );
        } else {
            return $responseResult;
        }
    }
}
