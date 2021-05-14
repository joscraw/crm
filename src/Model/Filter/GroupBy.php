<?php

namespace App\Model\Filter;

class GroupBy
{
    use Uid;

    public function getQuery(Column $column) {
        $internalName = sprintf('$."%s"', $column->getProperty()->getInternalName());
        return [
            'sql' => sprintf("`%s`.properties->>?", $column->getAlias()),
            'bindings' => [$internalName]
        ];
    }
}