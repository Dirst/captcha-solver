<?php

namespace Dirst\CaptchaSolver\Exception;

/**
 * Captcha Solver Exception
 *
 * @author Dirst <dirst.guy@gmail.com>
 * @version 1.0
 */
class CaptchaSolverException extends \Exception
{
    /**
     * Constructs CaptchaSolver exception.
     *
     * @param string $message
     *   Exception message.
     * @param \Throwable $previous
     *   Previous Exception.
     */
    public function __construct($message = "", \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
