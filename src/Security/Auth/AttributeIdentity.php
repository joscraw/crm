<?php

namespace App\Security\Auth;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

class AttributeIdentity implements ObjectIdentityInterface
{
    private $identifier;
    private $type;

    /**
     * Constructor.
     *
     * @param $attribute
     */
    public function __construct($attribute)
    {
        if (!$attribute) {
            throw new \InvalidArgumentException('$attribute cannot be empty.');
        }

        $this->identifier = $attribute;
        $this->type = $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    public function equals(ObjectIdentityInterface $identity)
    {
        return $this->identifier == $identity->getIdentifier()
            && $this->type === $identity->getType();
    }

    /**
     * Returns a textual representation of this object identity.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('ObjectIdentity(%s, %s)', $this->identifier, $this->type);
    }
}