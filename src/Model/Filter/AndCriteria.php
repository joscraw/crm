<?php

namespace App\Model\Filter;

class AndCriteria extends AbstractCriteria
{
    /**
     * @var string
     */
    protected $uid;

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function generateFilterCriteria(FilterData $filterData, &$parts) {

        $parts[] = $this->getQuery();

        if($this->or->count() > 0) {
            $parts[] = ' OR ';
            $parts[] = ' ( ';
        }

        $i = 1;
        foreach($this->or as $orCriteria) {
            $orCriteria->generateFilterCriteria($filterData, $parts);

            if($i !== $this->or->count()) {
                $parts[] = ' OR ';
            }
            $i++;
        }

        if($this->or->count() > 0) {
            $parts[] = ' ) ';
        }

        if($this->and->count() > 0) {
            $parts[] = ' AND ';
            $parts[] = ' ( ';
        }

        $i = 1;
        foreach($this->and as $andCriteria) {
            $andCriteria->generateFilterCriteria($filterData, $parts);

            if($i !== $this->and->count()) {
                $parts[] = ' AND ';
            }
            $i++;
        }

        if($this->and->count() > 0) {
            $parts[] = ' ) ';
        }

    }

    /**
     * @return string
     */
    public function getQuery() {

        return $this->getUid();

    }
}