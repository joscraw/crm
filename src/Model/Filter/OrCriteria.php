<?php

namespace App\Model\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OrCriteria extends AbstractCriteria
{
    public function __construct($uid = null, ArrayCollection $andCriteria = null, ArrayCollection $orCriteria = null)
    {
        $this->uid = $uid;
        parent::__construct($andCriteria, $orCriteria);
    }
}