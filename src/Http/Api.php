<?php

namespace App\Http;

class Api
{
    /**
     * You could create separate description properties for each
     * Section if you wanted as well if you don't want to be so repetitive
     * @var string
     */
    public const DESCRIPTION = "All 40x and 50x error responses are structured the
    exact same throughout the entire app with the exception of some 40x errors. Some 
    40x errors are unique and can return an error code as well as some additional  
    information. A good example of this would be when creating or editing a resource.  
    If you are missing a required field in the request body you would see an error code  
    of *validation_error* along with an errors object containing the error messages for  
    each field that had any validation errors. All 20x success responses are structured 
    the same as each other. You will never get any data back that you didn't either request, 
    create, edit, or delete. This is helpful as you will not have to be guessing which JSON  
    keys to look for or parse. For example. If you request a list of 10 custom objects, you  
    know that you are getting back an array of 10 custom objects. If you create a custom  
    object, you know you are getting back the custom object you created. Same with Edit  
    and Delete. Also don\'t expect a message in any of the 20x responses. If you see a  
    20x response, your request was successful. You will NOT get some type of message  
    back like this: 'Your custom object was successfully created'. 
    These is what status codes are for.";

    /**
     * You could create separate description properties for each
     * Section if you wanted as well if you don't want to be so repetitive
     * @var string
     */
    public const PERMISSION_DESCRIPTION = "Returns all the avialable permissions in the platform.
    NOTE: This example response is NOT all inclusive, sp please call this endpoint to get a full list of templates.
    (*) - refers to all resources of that class. (:resourceId) - refers to a specific resource of that class. 
    Classes are denoted by 2 underscores. One prepended and one appended onto the class name itself. 
    The response is sectioned off into groups to help the end user mentally categorize the templates. 
    Templates under the portal/custom_object,etc keys are templates that can be used for portal/custom_object,etc resources only. 
    Templates under object_specific are templates that can be used for objects only. 
    Templates under class_specific are templates that can only be used for classes. Templates under hybrid are 
    almost a hybrid of object_specific and class specific and make use of both * and :resourceId. Templates
    under attribute_specific are not connected to resources at all and are used as more of a boolean permission. 
    can_login, can_manage_permissions, etc.";

    public const SECURITY_CONTROLLER_SIGN_UP = "Sign up a user. This adds them to a given portal and the default 
    auth0 connection for the application. If the portalInternalIdentifier is excluded from the call, 
    the user will be granted access to all portals in the application.";

    public const PERMISSION_CONTROLLER_ROLE_NEW = "Creates a new role in the platform.";

    public const PERMISSION_CONTROLLER_ROLE_EDIT = "Edits a role in the platform.";

    public const PERMISSION_CONTROLLER_ROLE_PERMISSIONS_ADD = "Add permissions to a role in the platform.";

    public const PERMISSION_CONTROLLER_ROLE_PERMISSIONS_REMOVE = "Remove permissions from a role in the platform.";

    public const USER_CONTROLLER_USER_ROLES_ADD = "Add roles to a user in the platform.";

    public const USER_CONTROLLER_USER_ROLES_REMOVE = "Remove roles from a user in the platform.";

    public const USER_CONTROLLER_USER_ROLES_VIEW = "View roles from a user in the platform.";


    public const VERSION_1 = 'v1';
    public const VERSION_2 = 'v2';

    public const SCOPE_PRIVATE = 'private';
    public const SCOPE_PUBLIC = 'public';
    public const SCOPE_MARKETING = 'marketing';

    public const LINK_VIEW = 'view';
    public const LINK_EDIT = 'edit';
    public const LINK_NEW = 'new';
    public const LINK_DELETE = 'delete';

    public static $versions = [
      self::VERSION_1,
      self::VERSION_2
    ];

    public static $scopes = [
        self::SCOPE_PRIVATE,
        self::SCOPE_PUBLIC,
        self::SCOPE_MARKETING
    ];
}