<?php

namespace Bojaghi\Pagination;

use WP_Query;

/**
 * Pagination helper class
 *
 * Method #1: using WP_Query
 * -------------------------
 * - with constructor:
 *     $p = new Pagination($wp_query);
 * - without constructor:
 *     $p = new Pagination();
 *     $p->setQuery($wp_query)->create();
 *
 * Method #2: using parameters
 * ---------------------------
 *     $p = new Pagination();
 *     $p
 *      ->setNumItems(10) // posts_per_page
 *      ->setTotal(130)   // total posts
 *      ->setCurrent(2)   // current page
 *      ->create();
 *
 * Results
 * -------
 * $begin = $p->getBegin();
 * $end   = $p->getEnd();
 *
 * Types
 * -----
 * - Type center:
 *   e.g. size=5
 *   * [1] 2 3 4 5
 *   * 1 [2] 3 4 5
 *   * 1 2 [3] 5 5
 *   * 2 3 [4] 5 6
 *   * 3 4 [5] 6 7
 *   * 4 5 [6] 7 8
 *   * 5 6 [7] 8 9
 *   * 6 7 [8] 9 10
 *
 * - Type section:
 *   e.g. size=5
 *   * [1] 2 3 4 5
 *   * 1 [2] 3 4 5
 *   * 1 2 [3] 4 5
 *   * 1 2 3 [4] 5
 *   * 1 2 3 4 [5]
 *   * [6] 7 8 9 10
 *   * 6 [7] 8 9 10
 *   * 6 7 [8] 9 10
 *   * 6 7 8 [9] 10
 *   * 6 7 8 9 [10]
 */
class Pagination
{
    public const DEFAULT_SIZE = 5;
    public const TYPE_CENTER  = 'center';
    public const TYPE_SECTION = 'section';

    private WP_Query|null $query;

    private int $size = self::DEFAULT_SIZE;

    private int $begin;

    private int $end;

    private int $current;

    private int $total;

    private int $numItems;

    private string $type = self::TYPE_SECTION;

    public function __construct(WP_Query|null $query = null)
    {
        if ($query) {
            $this
                ->setQuery($query)
                ->create()
            ;
        }
    }

    public function reset(): void
    {
        $this->query = null;

        $this->begin    = -1;
        $this->end      = -1;
        $this->current  = -1;
        $this->numItems = -1;
        $this->total    = -1;
    }

    public function create(): self
    {
        if ($this->query) {
            $total         = $this->query->found_posts;
            $numItems      = (int)$this->query->get('posts_per_page');
            $this->current = (int)$this->query->get('paged');
        } elseif ($this->total > -1 && $this->numItems > -1 && $this->current > -1) {
            $total    = $this->total;
            $numItems = $this->numItems;
        } else {
            return $this;
        }

        [
            'begin' => $begin,
            'end'   => $end,
        ] = match ($this->type) {
            self::TYPE_CENTER  => self::_calcCenter($total, $numItems, $this->current, $this->size),
            self::TYPE_SECTION => self::_calcSection($total, $numItems, $this->current, $this->size),
        };

        $this->begin = $begin;
        $this->end   = $end;

        return $this;
    }

    private static function _calcCenter(int $total, int $numItems, int $current, int $size): array
    {
        $firstPage = 1;
        $lastPage  = (int)ceil((float)$total / (float)$numItems);
        $current   = self::_clip($current, $firstPage, $lastPage);
        $size      = absint($size);

        $after  = $current;
        $begin  = $current;
        $before = $current;
        $end    = $current;
        $count  = $size - 1;

        do {
            ++$after;
            $isAfterAvail = $count && $after <= $lastPage;
            if ($isAfterAvail) {
                $end = $after;
                --$count;
            }

            --$before;
            $isBeforeAvail = $count && $before >= 1;
            if ($isBeforeAvail) {
                $begin = $before;
                --$count;
            }
        } while ($isBeforeAvail || $isAfterAvail);

        return compact('begin', 'end');
    }

    private static function _clip(int $value, int $min, int $max): int
    {
        if ($value > $max) {
            return $max;
        } elseif ($value < $min) {
            return $min;
        } else {
            return $value;
        }
    }

    private static function _calcSection(int $total, int $numItems, int $current, int $size): array
    {
        $firstPage = 1;
        $lastPage  = (int)ceil((float)$total / (float)$numItems);
        $current   = self::_clip($current, $firstPage, $lastPage);

        $sector = (int)(($current - 1) / $size);
        $begin  = $sector * $size + 1;
        $end    = min($begin + $size - 1, $lastPage);

        return compact('begin', 'end');
    }

    public function getBegin(): int
    {
        return $this->begin;
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function setCurrent(int $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function getNumItems(): int
    {
        return $this->numItems;
    }

    public function setNumItems(int $numItems): self
    {
        $this->numItems = $numItems;

        return $this;
    }

    public function getQuery(): WP_Query|null
    {
        return $this->query;
    }

    public function setQuery(WP_Query $query): self
    {
        $this->reset();
        $this->query = $query;
        $this->create();

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
