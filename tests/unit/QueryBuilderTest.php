<?php

namespace KairosDB\Test;

use KairosDB\QueryBuilder;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  QueryBuilder $queryBuilder */
    private $queryBuilder;

    public function setUp()
    {
        $this->queryBuilder = new QueryBuilder();
    }

/*"aggregators": [
                    {
                        "name": "max",
                        "align_sampling": true,
                        "sampling": {
                        "value": "2",
                            "unit": "days",
                            "time_zone": ""
                        }
                    }
                ]
            }
        ],

    }*/
    public function testAggregatorMax()
    {

        $metricName = "network_in";
        $query = $this->queryBuilder
            ->addMetric($metricName)
            ->max(['unit' => 'days', 'value' => 1])
            ->build();

        $metric = array_pop($query['metrics']);

        $this->assertArrayHasKey('aggregators', $metric);
        $this->assertCount(1, $metric['aggregators']);
        $this->assertArrayHasKey('name', $metric['aggregators'][0]);
        $this->assertArrayHasKey('sampling', $metric['aggregators'][0]);
    }


    public function testAddMetricGroupingByValue()
    {
        $metricName = "network_in";
        $range_size = 1000;
        $query = $this->queryBuilder
            ->addMetric($metricName)
            ->groupByValue($range_size)
            ->build();

        $metric = array_pop($query['metrics']);

        $this->assertArrayHasKey('group_by', $metric);
        $this->assertEquals('value', $metric['group_by'][0]['name']);
        $this->assertEquals($range_size, $metric['group_by'][0]['range_size']);
    }

    public function testAddMetricGroupingByTags()
    {
        $metricName = "network_in";
        $tags = ["host"];
        $query = $this->queryBuilder
            ->addMetric($metricName)
            ->groupByTags($tags)
            ->build();

        $metric = array_pop($query['metrics']);

        $this->assertArrayHasKey('group_by', $metric);
        $this->assertEquals('tag', $metric['group_by'][0]['name']);
        $this->assertEquals($tags, $metric['group_by'][0]['tags']);
    }

    public function testAddSeveralMetric()
    {
        $metricName = "network_in";
        $metricName2 = "network_out";
        $query = $this->queryBuilder->addMetric($metricName)->addMetric($metricName2)->build();
        $this->assertArrayHasKey('metrics', $query);

        $this->assertCount(2, $query['metrics']);
    }

    public function testAddTagsToMetric()
    {
        $metricName = "network_in";
        $tags = ['host' => 'precise64'];
        $query = $this->queryBuilder
            ->addMetric($metricName)
            ->tags($tags)
            ->build();

        $metric = array_pop($query['metrics']);

        $this->assertArrayHasKey('tags', $metric);

    }

    public function testAddLimiToMetric()
    {
        $metricName = "network_in";
        $query = $this->queryBuilder
            ->addMetric($metricName)
            ->limit(700)
            ->build();

        $metric = array_pop($query['metrics']);

        $this->assertArrayHasKey('limit', $metric);

    }

    public function testAddMetric()
    {
        $metricName = "network_in";
        $query = $this->queryBuilder->addMetric($metricName)->build();
        $this->assertArrayHasKey('metrics', $query);
        $metric = array_pop($query['metrics']);

        $this->assertEquals($metricName, $metric['name']);
    }

    public function testStartFromRelative()
    {
        $query = $this->queryBuilder->start(['value' => 1, 'unit' => 'days'])->build();
        $this->assertArrayHasKey('start_relative', $query);
    }

    public function testStartFromAbsolute()
    {
        $start = round(microtime(true) * 1000);
        $query = $this->queryBuilder->start($start)->build();
        $this->assertArrayHasKey('start_absolute', $query);
    }

    public function testEndRelative()
    {
        $query = $this->queryBuilder->end(['value' => 1, 'unit' => 'days'])->build();
        $this->assertArrayHasKey('end_relative', $query);
    }

    public function testEndAbsolute()
    {
        $start = round(microtime(true) * 1000);
        $query = $this->queryBuilder->end($start)->build();
        $this->assertArrayHasKey('end_absolute', $query);
    }

    public function testQueryCacheTime()
    {
        $seconds = 60*60;
        $query = $this->queryBuilder->cache($seconds)->build();
        $this->assertArrayHasKey('cache_time', $query);
    }

} 