<?php

namespace KairosDB;

/**
 * Class QueryBuilder
 * @package KairosDB
 */
class QueryBuilder
{
    private $query = [];

    private $currentMetric = [];
    private $metrics = [];

    /**
     * @param string $metricName
     * @return $this
     */
    public function addMetric($metricName)
    {
        if (!empty($this->currentMetric)) {
            $this->metrics[] = $this->currentMetric;
            $this->currentMetric = [];
        }

        $this->currentMetric['name'] = $metricName;

        return $this;
    }

    /**
    * @param string $name
    * @param array $sampling
    * @return $this
    */
    public function addAggregator($name, $sampling = [])
    {
        if (!isset($this->currentMetric['aggregators']))
            $this->currentMetric['aggregators'] = [];
        $this->currentMetric['aggregators'][] = [
            'name'     => $name,
            'sampling' => $sampling,
        ];

        return $this;
    }

    /**
    * @param array $sampling
    * @return $this
    */
    public function max($value, $unit)
    {
        return $this->addAggregator('max', ['value' => $value, 'unit' => $unit]);
    }

    /**
    * @param array $sampling
    * @return $this
    */
    public function min($value, $unit)
    {
        return $this->addAggregator('min', ['value' => $value, 'unit' => $unit]);
    }

    /**
    * @param array $sampling
    * @return $this
    */
    public function avg($value, $unit)
    {
        return $this->addAggregator('avg', ['value' => $value, 'unit' => $unit]);
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function groupByValue($value)
    {
        $this->currentMetric['group_by'] = [
            'name' => 'value',
            'range_size' => $value,
        ];

        return $this;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function groupByTags(array $tags)
    {
        $this->currentMetric['group_by'] = [
            'name' => 'tag',
            'tags' => $tags,
        ];

        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->currentMetric['limit'] = $limit;

        return $this;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function tags(array $tags)
    {
        $this->currentMetric['tags'] = $tags;

        return $this;
    }

    /**
     * Can be :
     * - absolute: in miliseconds
     * - relative: array ['value'=> 1, 'unit'=>'days']
     *
     * @param mixed $start
     * @return $this
     */
    public function start($start)
    {
        $this->setTimeLimits('start', $start);

        return $this;
    }

    /**
     * Can be :
     * - absolute: in miliseconds
     * - relative: array ['value'=> 1, 'unit'=>'days']
     *
     * @param mixed $start
     * @return $this
     */
    public function end($end)
    {
        $this->setTimeLimits('end', $end);

        return $this;
    }

    /**
     * The amount of time in seconds to cache the output of the query.
     * @param int $seconds
     * @return $this
     */
    public function cache($seconds)
    {
        $this->query['cache_time'] = $seconds;
        return $this;
    }

    /**
     * @return array $query
     */
    public function build()
    {
        $this->metrics[] = $this->currentMetric;
        $this->query['metrics'] = $this->metrics;

        return $this->query;
    }


    /**
     * todo: throw exceptions if unit/value have not been specified
     * @param $type
     * @param $limits
     */
    private function setTimeLimits($type, $limits)
    {
        if (is_array($limits)) {

            $this->query["{$type}_relative"]= [
                'unit'  => $limits['unit'],
                'value' => $limits['value']
            ];

        } elseif(is_numeric($limits)) {
            $this->query["{$type}_absolute"] = $limits;
        }
    }
}
