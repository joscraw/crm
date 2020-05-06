<?php

namespace App\Security\Auth;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Entity\Role;
use App\Repository\PermissionRepository;
use App\Repository\PortalRepository;

class PermissionManager
{
    const MASK_CREATE = 1;
    const MASK_VIEW = 2;
    const MASK_EDIT = 4;
    const MASK_DELETE = 8;
    const MASK_ALL = 1073741823;

    // doesn't really matter what you choose here. Using for attribute permissions only
    const MASK_ENABLED = 16;

    public static $attributePermissions = [
        'Can Login',
        'Can Configure Roles And Permissions'
    ];

    /**
     * Dynamic template placeholders in which permissions can be calculated from
     *
     * @var array
     */
    public static $templates = [

        'portal' => [
            'portal_*', // grants applied to all portals
            'portal_:portalid', // grants applied to a specific portal
        ],

        'custom_object' => [
            'customobject_*', // grants applied to all custom objects
            'customobject_:customobjectid', // grants applied to a specific custom object
            'portal_:portalid_customobject_*', // grants applied to all custom objects in a specific portal
        ],

        'property_group' => [
            'propertygroup_*', // grants applied to all property groups
            'propertygroup_:propertygroupid', // grants applied to a specific property group
            'portal_:portalid_propertygroup_*', // grants applied to all property groups in a specific portal
            'customobject_:customobjectid_propertygroup_*', // grants applied to all property groups in a specific custom object
        ],

        'property' => [
            'property_*', // grants applied to all properties
            'property_:propertyid', // grants applied to a specific property
            'portal_:portalid_property_*', // grants applied to all properties in a specific portal
            'customobject_:customobjectid_property_*', // grants applied to all properties in a custom object
            'propertygroup_:propertygroupid_property_*', // grants applied to all properties in a specific property group
        ],

        'record' => [
            'record_*', // grants applied to all records
            'record_:recordid', // grants applied to a specific record
            'portal_:portalid_record_*', // grants applied to all records in a specific portal
            'customobject_:customobjectid_record_*', // grants applied to all records created from a specific custom object
        ],

        'class_specific' => [
            'portal_*', // grants applied to all portals
            'customobject_*', // grants applied to all custom objects
            'propertygroup_*', // grants applied to all property groups
            'property_*', // grants applied to all properties
            'record_*', // grants applied to all records
        ],

        'object_specific' => [
            'portal_:portalid', // grants applied to a specific portal
            'customobject_:customobjectid', // grants applied to a specific custom object
            'propertygroup_:propertygroupid', // grants applied to a specific property group
            'property_:propertyid', // grants applied to a specific property
            'record_:recordid', // grants applied to a specific record
        ],

        'hybrid' => [
            'portal_:portalid_customobject_*', // grants applied to all custom objects in a specific portal
            'portal_:portalid_propertygroup_*', // grants applied to all property groups in a specific portal
            'customobject_:customobjectid_propertygroup_*', // grants applied to all property groups in a specific custom object
            'portal_:portalid_property_*', // grants applied to all properties in a specific portal
            'customobject_:customobjectid_property_*', // grants applied to all properties in a custom object
            'propertygroup_:propertygroupid_property_*', // grants applied to all properties in a specific property group
            'portal_:portalid_record_*', // grants applied to all records in a specific portal
            'customobject_:customobjectid_record_*', // grants applied to all records created from a specific custom object
        ],

        'attribute_specific' => [
            'can_login',
            'can_configure_roles_and_permissions'
        ]

    ];

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * PermissionManager constructor.
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Return the key used in the permission mapping. The key itself
     * is representative of what the user is trying to authorize against
     * and follows a strict pattern. Examples:
     *
     * @param null $object
     * @return mixed|string
     */
    public function getKeys($object = null) {

        $context = [
            'portalId' => null,
            'customObjectId' => null,
            'propertyGroupId' => null,
            'propertyId' => null,
            'recordId' => null
        ];

        if($object instanceof Portal) {
            $context['portalId'] = $object->getId();
            $templates = self::$templates['portal'];
        } else if($object instanceof CustomObject) {
            $context['portalId'] = $object->getPortal()->getId();
            $context['customObjectId'] = $object->getId();
            $templates = self::$templates['custom_object'];
        } else if($object instanceof PropertyGroup) {
            $context['portalId'] = $object->getCustomObject()->getPortal()->getId();
            $context['customObjectId'] = $object->getCustomObject()->getId();
            $context['propertyGroupId'] = $object->getId();
            $templates = self::$templates['property_group'];
        } else if($object instanceof Property) {
            $context['portalId'] = $object->getPropertyGroup()->getCustomObject()->getPortal()->getId();
            $context['customObjectId'] = $object->getPropertyGroup()->getCustomObject()->getId();
            $context['propertyGroupId'] = $object->getPropertyGroup()->getId();
            $context['propertyId'] = $object->getId();
            $templates = self::$templates['property'];
        } else if($object instanceof Record) {
            $context['portalId'] = $object->getCustomObject()->getPortal()->getId();
            $context['customObjectId'] = $object->getCustomObject()->getId();
            $context['recordId'] = $object->getId();
            $templates = self::$templates['record'];
        } else if(is_string($object)) {
            return str_replace(" ", "_", $object);
        } else {
            return [];
        }

        $keys = [];
        foreach($templates as $template) {
            $keys[] = preg_replace_callback('/(\:[a-zA-Z]+)/', function($matches) use($context, $keys) {
                $slug = $matches[0];
                switch($slug) {
                    case ':portalid':
                        return $context['portalId'];
                    case ':customobjectid':
                        return $context['customObjectId'];
                    case ':propertygroupid':
                        return $context['propertyGroupId'];
                    case ':propertyid':
                        return $context['propertyId'];
                    case ':recordid':
                        return $context['recordId'];
                    default:
                        return $slug;
                }
            }, $template);
        }

        return $keys;
    }

    /**
     * Returns a simple mapping of all grants and their associated masks
     * @return array
     * @throws \ReflectionException
     */
    public function grantMaskMapping()
    {
        $masks = [];
        $reflection = new \ReflectionClass(get_called_class());
        foreach ($reflection->getConstants() as $name => $mask) {
            if (0 !== strpos($name, 'MASK_')) {
                continue;
            }

            $masks[substr($name, 5)] = $mask;
        }
        return $masks;
    }

    /**
     * Resolves the grants for a set of permissions
     *
     * @param array $permissions
     * @return array
     */
    public function resolveGrants(array $permissions) {
        $result = [];
        foreach($permissions as $key => $value) {
            if(!in_array(str_replace("_", " ", $key), self::$attributePermissions)) {
                $row = [];
                if (($value & self::MASK_CREATE) == self::MASK_CREATE) $row[] = 'create';
                if (($value & self::MASK_VIEW) == self::MASK_VIEW) $row[] = 'view';
                if (($value & self::MASK_EDIT) == self::MASK_EDIT) $row[] = 'edit';
                if (($value & self::MASK_DELETE) == self::MASK_DELETE) $row[] = 'delete';
                if (($value & self::MASK_ALL) == self::MASK_ALL) $row[] = 'all';
                $result[$key] = implode(", ", $row);
            } else {
                $result[$key] = 'enabled';
            }
        }
        return $result;
    }

    /**
     * Returns the mask a set of grants
     *
     * @param array|null $grants
     * @return int
     *
     */
    public function resolveMasks(?array $grants) {
        $bit = 0;
        if ($grants != null && count($grants)) {
            if (in_array('create', $grants)) $bit |= self::MASK_CREATE;
            if (in_array('view', $grants)) $bit |= self::MASK_VIEW;
            if (in_array('edit', $grants)) $bit |= self::MASK_EDIT;
            if (in_array('delete', $grants)) $bit |= self::MASK_DELETE;
            if (in_array('all', $grants)) $bit |= self::MASK_ALL;
            if (in_array('enabled', $grants)) $bit |= self::MASK_ENABLED;
        }

        return $bit;
    }

    /**
     * Determines whether or not a permission set allows authorization for a given bit
     * @param $keys
     * @param int $bit
     * @param array $permissions
     * @param bool $matchAll
     * @return bool
     */
    public function isAuthorized($keys, int $bit, array $permissions, $matchAll = true): bool {

        $keys = is_array($keys) ? $keys : [$keys];

        if($matchAll) {

            foreach($keys as $key) {
                if (!array_key_exists($key, $permissions)) {
                    return false;
                }

                if (($permissions[$key] & $bit) != $bit) {
                    return false;
                }
            }

            return true;
        } else {

            foreach($keys as $key) {
                if (array_key_exists($key, $permissions)) {
                    if (($permissions[$key] & $bit) == $bit) {
                        return true;
                    }
                }
            }
            return false;
        }

    }

    /**
     * @param Portal $portal
     * @return Role
     */
    public function configureSuperAdminRole(?Portal $portal = null): Role {

        $role = new Role();
        $role->setName('ROLE_SUPER_ADMIN')
            ->setDescription('Super Admin Role');

        if($portal) {
            $role->setPortal($portal);
        }

        $permissions = $this->permissionRepository->findAll();
        foreach($permissions as $permission) {
            $role->addPermission($permission);
        }

        return $role;
    }

}