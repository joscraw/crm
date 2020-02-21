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

        if($this->or->count() > 0) {
            $filterData->filterCriteriaParts[] = ' ( ';
        }

        /** @var OrCriteria $orCriteria */
        $i = 1;
        foreach($this->or as $orCriteria) {
            $filterData->filterCriteriaParts[] = ' ( ';
            $orCriteria->generateFilterCriteria($filterData);
            $filterData->filterCriteriaParts[] = ' ) ';

            if($i !== $this->or->count()) {
                $filterData->filterCriteriaParts[] = ' OR ';
            }
            $i++;
        }

        if($this->or->count() > 0) {
            $filterData->filterCriteriaParts[] = ' ) ';
        }
    }
}