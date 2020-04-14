<?php

namespace App\Model\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class FilterCriteria extends AbstractCriteria
{

    /**
     * @param FilterData $filterData
     * @return void
     */
    public function generateFilterCriteria(FilterData $filterData) {

        if($this->or->count() > 0) {
            /**
             * This must start with an AND as you can't have situations like this happening:
             * WHERE `otqeV.contacts`.custom_object_id  = ? OR (  LOWER(`epnIw.contacts`.properties->>?) = ?  )
             * That statement would make it so the custom_object_id was completely optional
             */
            // todo should we add AND to the getQuery part
            $filterData->filterCriteriaParts[] = " AND \n";
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