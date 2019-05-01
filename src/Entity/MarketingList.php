<?php

namespace App\Entity;

use App\Utils\ArrayHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Validator\Constraints as CustomAssert;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MarketingListRepository")
 */
class MarketingList
{
    use TimestampableEntity;
    use ArrayHelper;

    const DYNAMIC_LIST = 'DYNAMIC_LIST';
    const STATIC_LIST = 'STATIC_LIST';

    public static $LIST_TYPES = [
        [
            'name' => self::DYNAMIC_LIST,
            'label' => 'Dynamic List'
        ],

        [
            'name' => self::STATIC_LIST,
            'label' => 'Static List'
        ]
    ];

    /**
     * @Groups({"LIST"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"LIST"})
     *
     * @ORM\Column(type="text")
     */
    private $query;

    /**
     * @Groups({"LIST"})
     *
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @Groups({"LIST"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="marketingLists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @Groups({"LIST"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="marketingLists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @Groups({"LIST"})
     *
     * @ORM\Column(type="json")
     */
    private $columnOrder = [];

    /**
     * @Groups({"LIST"})
     *
     * @Assert\Choice({"DYNAMIC_LIST", "STATIC_LIST"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $records = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Folder", inversedBy="marketingLists")
     */
    private $folder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(?CustomObject $customObject): self
    {
        $this->customObject = $customObject;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(?Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    public function getColumnOrder(): ?array
    {
        return $this->columnOrder;
    }

    public function setColumnOrder(array $columnOrder): self
    {
        $this->columnOrder = $columnOrder;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function listTypeExists($listType) {

        return array_search($listType, array_column(self::$LIST_TYPES, 'name')) !== false;

    }

    public function getRecords(): ?array
    {
        return $this->records;
    }

    public function setRecords(?array $records): self
    {
        $this->records = $records;

        return $this;
    }

    public function getStaticListQuery() {

        return sprintf('%s and id in (%s)', $this->query, implode(",", $this->getArrayValuesRecursive($this->records)));

    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }
}
