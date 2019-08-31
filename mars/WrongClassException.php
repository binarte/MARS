<?php
namespace mars;

class WrongClassException extends \Exception
{

    /**
     *
     * @param object $got
     *            The object received
     * @param string $expected
     *            The class expected from the request
     * @param \Throwable $previous
     */
    function __construct(object $got, $expected, \Throwable $previous)
    {
        $expected = (string) $expected;
        parent::__construct("Got '" . get_class($got) . "', expected '$expected'", 0, $previous);
    }

    /**
     *
     * @var string
     */
    private $got;

    function getGot()
    {
        return $this->got;
    }

    private $received;

    function getReceived()
    {
        return $this->received;
    }
}

