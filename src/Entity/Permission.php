<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
 */
class Permission
{

    const CREATE = 1;
    const VIEW = 2;
    const EDIT = 4;
    const DELETE = 8;
    const ALL = 16;

    public static $attributePermissions = [
        'Can Login'
    ];

    public static $permissionKeyMap = [
        [
            'key' => 'custom_object',
            'description' => 'Custom object permissions.',
            'bits' => [
                  ['grant' => 'CREATE', 'bit' => self::CREATE],
                  ['grant' => 'VIEW', 'bit' => self::VIEW],
                  ['grant' => 'EDIT', 'bit' => self::EDIT],
                  ['grant' => 'DELETE', 'bit' => self::DELETE],
                  ['grant' => 'ALL', 'bit' => self::ALL],
          ]
      ],
        [
            'key' => 'property',
            'description' => 'Property permissions.',
            'bits' => [
                ['grant' => 'CREATE', 'bit' => self::CREATE],
                ['grant' => 'VIEW', 'bit' => self::VIEW],
                ['grant' => 'EDIT', 'bit' => self::EDIT],
                ['grant' => 'DELETE', 'bit' => self::DELETE],
                ['grant' => 'ALL', 'bit' => self::ALL],
            ]
        ]
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getBit(string $key, int $bit): bool {
        if (is_array($this->data) && array_key_exists($key, $this->data)) {
            return ($this->data[$key] & $bit) == $bit;
        }
        return false;
    }

}
