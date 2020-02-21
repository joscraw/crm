<?php

namespace App\Model\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class FilterCriteria extends AbstractCriteria
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param FilterData $filterData
     * @return array
     */
    public function generateFilterCriteria(FilterData $filterData) {

        $parts = [];

        if($this->or->count() > 0) {
            $parts[] = ' ( ';
        }

        /** @var OrCriteria $orCriteria */
        $i = 1;
        foreach($this->or as $orCriteria) {
            $parts[] = ' ( ';
            $orCriteria->generateFilterCriteria($filterData, $parts);
            $parts[] = ' ) ';

            if($i !== $this->or->count()) {
                $parts[] = ' OR ';
            }
            $i++;
        }

        if($this->or->count() > 0) {
            $parts[] = ' ) ';
        }

        return $parts;
    }
}