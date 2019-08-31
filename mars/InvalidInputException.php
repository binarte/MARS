<?php
namespace mars;

class InvalidInputException extends \Exception
{

    const EmptyValue = 1;

    const WrongType = 2;

    function __construct($errortype, $arg, $expected = null, \Throwable $prev)
    {
        $errortype = (int) $errortype;
        $expected = $expected === null ? null : (string) $expected;
        switch ($errortype) {
            case self::EmptyValue:
                $msg = "Argument $arg cannot be empty";
                break;
            case self::WrongType:
                $msg = "Argument $arg should be $expected";
                break;
            default:
                $msg = "Unknown input error $errortype on argument $arg";
        }
        parent::__construct($msg, $errortype, $prev);
        $this->errortype = $errortype;
        $this->expected = $expected;
    }

    private $errortype;

    function getErrorType()
    {
        return $this->errortype;
    }

    private $expected;

    function getExpected()
    {
        return $this->expected;
    }
}

