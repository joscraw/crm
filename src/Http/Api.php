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


    public const VERSION_1 = 'v1';
    public const VERSION_2 = 'v2';

    public const LINK_VIEW = 'view';
    public const LINK_EDIT = 'edit';
    public const LINK_NEW = 'new';
    public const LINK_DELETE = 'delete';

}