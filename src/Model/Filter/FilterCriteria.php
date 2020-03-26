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
     * @return void
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
                $filterData->filterCriteriaParts[] = " OR \n";
            }
            $i++;
        }

        if($this->or->count() > 0) {
            $filterData->filterCriteriaParts[] = ' ) ';
        }

        if($this->and->count() > 0) {
            $filterData->filterCriteriaParts[] = " AND \n";
        }

        if($this->and->count() > 0) {
            $filterData->filterCriteriaParts[] = ' ( ';
        }

        /** @var AndCriteria $andCriteria */
        $i = 1;
        foreach($this->and as $andCriteria) {
            $filterData->filterCriteriaParts[] = ' ( ';
            $andCriteria->generateFilterCriteria($filterData);
            $filterData->filterCriteriaParts[] = ' ) ';

            if($i !== $this->and->count()) {
                $filterData->filterCriteriaParts[] = " AND \n";
            }
            $i++;
        }

        if($this->and->count() > 0) {
            $filterData->filterCriteriaParts[] = ' ) ';
        }
    }
}