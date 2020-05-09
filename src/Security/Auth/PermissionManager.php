<?php

namespace App\Security\Auth;

use App\Entity\AclEntry;
use App\Entity\Portal;
use App\Entity\Role;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class PermissionManager
{
    const MASK_CREATE = 1;
    const MASK_READ = 2;
    const MASK_UPDATE = 4;
    const MASK_DELETE = 8;
    const MASK_DENY_ALL = -1;
    const MASK_ENABLED = 16;

    /**
     * Resolves the grants for a set of aclEntries
     *
     * @param AclEntry $aclEntry
     * @param bool $stringify
     * @return array
     */
    public function resolveGrants(AclEntry $aclEntry, $stringify = false) {

        $result = [];
        if (($aclEntry->getMask() & self::MASK_CREATE) == self::MASK_CREATE) $result[] = 'create';
        if (($aclEntry->getMask() & self::MASK_READ) == self::MASK_READ) $result[] = 'read';
        if (($aclEntry->getMask() & self::MASK_UPDATE) == self::MASK_UPDATE) $result[] = 'update';
        if (($aclEntry->getMask() & self::MASK_DELETE) == self::MASK_DELETE) $result[] = 'delete';
        if (($aclEntry->getMask() & self::MASK_DENY_ALL) == self::MASK_DENY_ALL) $result[] = 'deny all';
        if (($aclEntry->getMask() & self::MASK_ENABLED) == self::MASK_ENABLED) $result[] = 'enabled';
        return $stringify ? implode(", ", $result) : $result;
    }

    /**
     * Returns the mask for a set of grants
     *
     * @param array|null $grants
     * @return int
     *
     */
    public function resolveMasks(?array $grants) {
        $bit = 0;
        if ($grants != null && count($grants)) {
            if (in_array('create', $grants)) $bit |= self::MASK_CREATE;
            if (in_array('read', $grants)) $bit |= self::MASK_READ;
            if (in_array('update', $grants)) $bit |= self::MASK_UPDATE;
            if (in_array('delete', $grants)) $bit |= self::MASK_DELETE;
            if (in_array('deny all', $grants)) $bit |= self::MASK_DENY_ALL;
            if (in_array('enabled', $grants)) $bit |= self::MASK_ENABLED;
        }

        return $bit;
    }

    /**
     * Determines whether or not a set of aclEntries are valid for a given bit
     * @param int $bit
     * @param array $aclEntries
     * @return bool
     */
    public function isAuthorized(int $bit, array $aclEntries): bool {

        /** @var AclEntry $aclEntry */
        foreach($aclEntries as $aclEntry) {

            if(($bit & $aclEntry->getMask()) == $bit) {
                return true;
            }
        }

        return false;
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

       /* $permissions = $this->permissionRepository->findAll();
        foreach($permissions as $permission) {
            $role->addPermission($permission);
        }*/

        return $role;
    }

    /**
     * @retu«rn mixed
     * @throws \Symfony\Component\Config\Exception\LoaderLoadException
     */
    public static function load() {

        $dir = __DIR__.'/permissions';
        $finder = new Finder();
        $finder->depth("< 3")->in($dir)->files()->name('*.yaml');
        $configValues = [];
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            // make sure the file has yaml inside it
            if(is_array(Yaml::parse($file->getContents()))) {
                $configValues = array_merge($configValues, Yaml::parse($file->getContents()));
            }
        }
        $yaml = Yaml::dump($configValues);
        file_put_contents($dir . '/permissions.yaml', $yaml);

        $configDirectories = [$dir];
        $fileLocator = new FileLocator($configDirectories);
        // Add additional Loaders here to pull in permissions from other sources instead of just .yaml files
        $loaderResolver = new LoaderResolver([new YamlPermissionLoader($fileLocator)]);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        $configValues = $delegatingLoader->load($dir.'/permissions.yaml');

        return $configValues;
    }

}