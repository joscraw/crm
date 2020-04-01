<?php

namespace App\Model\Filter;

class GroupBy
{
    use Uid;

    public function getQuery(Column $column) {
        return [
            'sql' => sprintf("`%s`.properties->>'$.\"%s\"'", $column->getAlias(), $column->getProperty()->getInternalName()),
            'bindings' => []
        ];
    }

    public function getQueryWithBindings(Column $column) {
        $internalName = sprintf('$."%s"', $column->getProperty()->getInternalName());
        return [
            'sql' => sprintf("`%s`.properties->>?", $column->getAlias()),
            'bindings' => [$internalName]
        ];
    }
}