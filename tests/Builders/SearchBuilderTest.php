<?php

namespace Novius\ScoutElastic\Test\Builders;

use Novius\ScoutElastic\SearchRule;
use Novius\ScoutElastic\Builders\SearchBuilder;
use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Test\Dependencies\Model;

class SearchBuilderTest extends AbstractTestCase
{
    use Model;

    public function testRule()
    {
        $builder = new SearchBuilder($this->mockModel(), 'qwerty');

        $ruleFunc = function (SearchBuilder $builder) {
            return [
                'must' => [
                    'match' => [
                        'foo' => $builder->query,
                    ],
                ],
            ];
        };

        $builder->rule(SearchRule::class)->rule($ruleFunc);

        $this->assertEquals([
            SearchRule::class,
            $ruleFunc,
        ], $builder->rules);
    }
}
