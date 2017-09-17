<?php

namespace Dirst\CaptchaSolver;

/**
 * Captcha Solver Interface.
 *
 * @author Dirst <dirst.guy@gmail.com>
 * @version 1.0
 */
interface CaptchaSolverInterface
{
  /**
   * Send Captcha Image to service.
   *
   * @param string $image
   *   Image loaded from captcha.
   *
   * @return array
   *   Data to get captcha solution.
   */
    public function sendCaptchaImage(&$image);

  /**
   * Get captcha solution.
   *
   * @param int $id
   *   Id of sended captcha to get the Solution.
   * @param int $waitSolutionSec
   *   Wait before request solution.
   * @param int $maxSolutionWaitSec
   *   Max solution wait in seconds.
   *
   * @return string
   *   Captcha solution as text.
   */
    public function getCaptchaSolution($id, $waitSolutionSec = 5, $maxSolutionWaitSec = 120);
}
