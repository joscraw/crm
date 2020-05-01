<?php

namespace App\Twig;

use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

    public function getFilters(): array {
        return [
            new TwigFilter('permission_key', [$this, 'getPermissionKey'])
        ];
    }

    // authorize(user, someBuildingBlock, ['new'])
    // authorize(user, someBuildingBlock, ['edit', 'delete'])
    // authorize(user, someRecord, ['edit', 'delete'])
    // authorize(user, constant('App\\Entity\\PersonGroupPermission::YOUR_STATUS_NAME'))
    // authorize(user, constant('App\\Entity\\PersonGroupPermission::YOUR_ACTION_NAME'))
    public function authorize(User $user, $value, ?array $grants = null) {
        true;
    }

    public function getPermissionKey($subject): ?string {
        //return PersonGroupPermission::getKey($subject);
    }

}
