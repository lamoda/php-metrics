<?php

namespace Lamoda\MetricBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricInterface;

abstract class MetricGroup implements \IteratorAggregate, MetricGroupInterface
{
    /** @var string */
    private $name;
    /** @var MetricInterface[]|Collection */
    private $metrics;
    /** @var string[] */
    private $tags;

    /**
     * MetricGroup constructor.
     *
     * @param string                       $name
     * @param Collection|MetricInterface[] $metrics
     * @param string[]                     $tags
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, $metrics = null, array $tags = [])
    {
        $this->name = $name;
        if (null === $metrics) {
            $metrics = [];
        }
        if (is_array($metrics)) {
            $metrics = new ArrayCollection($metrics);
        }
        if (!$metrics instanceof Collection) {
            throw new \InvalidArgumentException(
                'Second argument shuold be collection or array of ' . MetricInterface::class
            );
        }
        $this->metrics = $metrics;
        $this->tags = $tags;
    }

    public function addMetric(MetricInterface $metric)
    {
        if (!$this->metrics->contains($metric)) {
            $this->metrics->add($metric);
        }
    }

    public function removeMetric(MetricInterface $metric)
    {
        if ($this->metrics->contains($metric)) {
            $this->metrics->removeElement($metric);
        }
    }

    public function getMetrics(): \Traversable
    {
        return $this->metrics->getIterator();
    }

    public function setTag($tag, $value)
    {
        $this->tags[$tag] = $value;
    }

    public function removeTag($tag)
    {
        unset($this->tags[$tag]);
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        return $this->tags;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }
}
