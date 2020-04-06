<?php

namespace EOV\Exception;

/**
 * Class RequestFailureException
 *
 * An exception thrown on failures in EOV api requests
 *
 * @package EOV\Exception
 * @author George Webb <george.webb1@pb.com>
 */
class RequestFailureException extends \Exception
{
    /**
     * The failure detail
     *
     * @var array
     */
    protected $detail;

    /**
     * RequestFailureException constructor.
     *
     * @param string $message   The exception message
     * @param int $code         The http status code
     * @param array $detail     Extra details of the failure - e.g. validation errors
     */
    public function __construct($message = '', $code = 0, $detail = array())
    {
        $this->message = $message;
        $this->detail = $detail;
        $this->code = $code;
    }

    /**
     * Returns the details of the failure.
     *
     * @return array    The failure detail
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
