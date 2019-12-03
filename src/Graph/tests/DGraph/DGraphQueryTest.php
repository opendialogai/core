<?php

namespace OpenDialogAi\Core\Graph\tests\DGraph;

use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\ModelFacets;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryFilter;
use OpenDialogAi\Core\Tests\TestCase;

class DGraphQueryTest extends TestCase
{
    public function testBasicQuery() {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test')
        ->setQueryGraph([
            Model::UID,
            Model::ID
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals('{ dGraphQuery( func:eq(id,"test")){uid id }}', $preparedQuery);
    }

    public function testQueryWithBasicFilter() {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test')
        ->filter(function (DGraphQueryFilter $filter) { $filter->eq(Model::EI_TYPE, 'conversation_template'); })
        ->setQueryGraph([
            Model::UID,
            Model::ID
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test"))@filter( eq(ei_type,"conversation_template")){uid id }}',
            $preparedQuery
        );
    }

    public function testQueryWithComplexFilter() {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test')
        ->filter(function (DGraphQueryFilter $filter) { $filter->eq(Model::EI_TYPE, 'conversation_template'); })
        ->andFilter(function (DGraphQueryFilter $filter) {
            $filter->notHas(Model::HAS_OPENING_SCENE);
        })
        ->setQueryGraph([
            Model::UID,
            Model::ID
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test"))@filter( eq(ei_type,"conversation_template") and not has(has_opening_scene)){uid id }}',
            $preparedQuery
        );
    }

    public function testQueryWithRecurseNoDepth() {
        $query = new DGraphQuery();
        $query->uid("0xABCD")->recurse()->setQueryGraph([
            Model::UID,
            Model::UPDATE_OF
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:uid(0xABCD))@recurse(loop:false){uid update_of }}',
            $preparedQuery
        );
    }

    public function testQueryWithRecurseWithDepth() {
        $query = new DGraphQuery();
        $query->uid("0xABCD")->recurse(true, 5)->setQueryGraph([
            Model::UID,
            Model::UPDATE_OF
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:uid(0xABCD))@recurse(loop:true,depth:5){uid update_of }}',
            $preparedQuery
        );
    }

    public function testQueryWithSortingAndPagination() {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')->sort(Model::ORDER)->first()->setQueryGraph([
            Model::UID,
            Model::ID
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id"),orderasc:core.attribute.order,first:1){uid id }}',
            $preparedQuery
        );

        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')->sort(Model::ORDER, DGraphQuery::SORT_ASC)->first(1)->setQueryGraph([
            Model::UID,
            Model::ID
        ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id"),orderasc:core.attribute.order,first:1){uid id }}',
            $preparedQuery
        );

        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')
            ->sort(Model::ORDER, DGraphQuery::SORT_DESC)
            ->first(5)
            ->offset(5)
            ->setQueryGraph([
                Model::UID,
                Model::ID
            ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id"),orderdesc:core.attribute.order,first:5,offset:5){uid id }}',
            $preparedQuery
        );
    }

    public function testQueryImplicitFacetsOnScalarPredicate()
    {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')
            ->setQueryGraph([
                Model::UID,
                Model::ID,
                Model::FOLLOWED_BY => DGraphQuery::WITH_FACETS
            ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id")){uid id followed_by @facets}}',
            $preparedQuery
        );
    }

    public function testQueryExplicitFacetsOnScalarPredicate()
    {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')
            ->setQueryGraph([
                Model::UID,
                Model::ID,
                Model::FOLLOWED_BY => [
                    DGraphQuery::WITH_FACETS => [
                        ModelFacets::CREATED_AT,
                        ModelFacets::COUNT
                    ]
                ]
            ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id")){uid id followed_by @facets(created_at count )}}',
            $preparedQuery
        );
    }

    public function testQueryImplicitFacetsOnUidPredicate()
    {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')
            ->setQueryGraph([
                Model::UID,
                Model::ID,
                Model::FOLLOWED_BY => [
                    DGraphQuery::WITH_FACETS,
                    Model::UID,
                    Model::ID
                ]
            ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id")){uid id followed_by @facets {uid id }}}',
            $preparedQuery
        );
    }

    public function testQueryExplicitFacetsOnUidPredicate()
    {
        $query = new DGraphQuery();
        $query->eq(Model::ID, 'test_id')
            ->setQueryGraph([
                Model::UID,
                Model::ID,
                Model::FOLLOWED_BY => [
                    DGraphQuery::WITH_FACETS => [
                        ModelFacets::CREATED_AT,
                        ModelFacets::COUNT
                    ],
                    Model::UID,
                    Model::ID
                ]
            ]);

        $preparedQuery = $query->prepare();
        $this->assertEquals(
            '{ dGraphQuery( func:eq(id,"test_id")){uid id followed_by @facets(created_at count ){uid id }}}',
            $preparedQuery
        );
    }
}
