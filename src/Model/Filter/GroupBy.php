<?php

namespace App\Model\Filter;

class GroupBy
{
    use Uid;

    public function getQuery(Column $column) {

        $groupByQuery = <<<HERE
`%s`.properties->>'$."%s"'
HERE;
        return sprintf($groupByQuery, $column->getAlias(), $column->getProperty()->getInternalName());
    }
}