<?php

namespace App\Utils;

trait Query
{
    /**
     * @param Query $query
     * @return array An array with 3 indexes, sql the SQL statement with parameters as ?, params the ordered parameters, and paramTypes as the types each parameter is.
     * @throws \ReflectionException
     */
    protected function getRunnableQueryAndParametersForQuery(Query $query)
    {
        $sql = $query->getSQL();
        $c = new \ReflectionClass('Doctrine\ORM\Query');
        $parser = $c->getProperty('_parserResult');
        $parser->setAccessible(true);
        /** @var \Doctrine\ORM\Query\ParserResult $parser */
        $parser = $parser->getValue($query);
        $resultSet = $parser->getResultSetMapping();

        // Change the aliases back to what was originally specified in the QueryBuilder.
        $sql = preg_replace_callback(
            '/AS\s([a-zA-Z0-9_]+)/',
            function ($matches) use ($resultSet) {
                $ret = 'AS ';
                if ($resultSet->isScalarResult($matches[1])) {
                    $ret .= $resultSet->getScalarAlias($matches[1]);
                } else {
                    $ret .= $matches[1];
                }

                return $ret;

            },
            $sql
        );

        $m = $c->getMethod('processParameterMappings');
        $m->setAccessible(true);

        list($params, $types) = $m->invoke($query, $parser->getParameterMappings());

        return ['sql' => $sql, 'params' => $params, 'paramTypes' => $types];

    }
}