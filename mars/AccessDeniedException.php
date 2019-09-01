<?php
namespace mars;

class AccessDeniedException extends \Exception
{

    function __construct($subject, $permission, \Throwable $previous = null)
    {
        $gr = is_object($subject) ? get_class($gr) : $subject;
        parent::__construct("User does not have " . DatabaseObject::permissionName($permission) . " permissions on " . $gr, System::HS_Forbidden, $previous);
        $this->subject = $subject;
        $this->permission = $permission;
    }
    
    private $subject;
    function getSubject(){
        return $this->subject;
    }
    
    private $permission;
    function getPermission(){
        return $this->permission;
    }
}

