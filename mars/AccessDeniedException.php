<?php
namespace mars;

class AccessDeniedException extends \Exception
{

    function __construct(DatabaseObject $object, $permission, \Throwable $previous)
    {
        parent::__construct("User does not have " . DatabaseObject::permissionName($permission) . " permissions on " . get_class($object));
        $this->obj = $object;
        $this->permission = $permission;
    }
    
    private $obj;
    function getObject(){
        return $this->obj;
    }
    
    private $permission;
    function getPermission(){
        return $this->permission;
    }
}

