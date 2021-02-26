<?php


namespace OpenDialogAi\GraphQLClient;


interface GraphQLClientInterface
{

    /**
     * Clear all data
     */
    public function dropData();

    /**
     * Clear data and schema
     * @return mixed
     */
    public function dropAll();

    /**
     * Set a new schema
     * @param  string  $schema
     */
    public function setSchema(string $schema);

    /**
     * Make a graphQL query
     *
     * @param  string       $query
     * @param  array        $variables
     *
     * @return array
     */
    public function query(string $query, array $variables): array;
}
