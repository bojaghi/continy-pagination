<?php

namespace Bojaghi\Pagination\Tests;

use ReflectionClass;
use WP_Query;
use WP_UnitTestCase;
use Bojaghi\Pagination\Pagination;

class TestPagination extends WP_UnitTestCase
{
    public function test_pagination()
    {
        $query              = new WP_Query();
        $query->found_posts = 320;
        $query->set('posts_per_page', 25);
        $query->set('paged', 7);

        $pagination = new Pagination($query);
        $pagination
            ->setQuery($query)
            ->setSize(5)
            ->create()
        ;

        /*
         * 320 / 25 = 12.8 => total 13 pages
         * 6 [7] 8 9 10
         */
        $begin = $pagination->getBegin();
        $this->assertEquals(6, $begin);
        $end = $pagination->getEnd();
        $this->assertEquals(10, $end);

        $pagination->setType(Pagination::TYPE_CENTER);
        $pagination->create();

        /**
         * 5 6 [7] 8 9
         */
        $begin = $pagination->getBegin();
        $this->assertEquals(5, $begin);
        $end = $pagination->getEnd();
        $this->assertEquals(9, $end);
    }

    /**
     * @throws \ReflectionException
     * @dataProvider _calcSectionProvider
     */
    public function test_calcSection(array $input, array $expected): void
    {
        $method = getAccessibleMethod(Pagination::class, '_calcSection');
        $result = $method->invoke(null, ...$input);
        $this->assertEquals($expected, $result);
    }

    public function _calcSectionProvider(): array
    {
        return [
            'Page 1/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 1, 'size' => 5], // Input
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 2/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 2, 'size' => 5], // Input
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 3/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 3, 'size' => 5], // Input
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 4/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 4, 'size' => 5], // Input
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 5/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 5, 'size' => 5], // Input
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 6/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 6, 'size' => 5], // Input
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Page 7/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 7, 'size' => 5], // Input
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Page 8/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 8, 'size' => 5], // Input
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Page 9/10'            => [
                ['total' => 100, 'numItems' => 10, 'current' => 9, 'size' => 5], // Input
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Page 10/10'           => [
                ['total' => 100, 'numItems' => 10, 'current' => 10, 'size' => 5], // Input
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Edge: Page 0/10'      => [
                ['total' => 100, 'numItems' => 10, 'current' => 1, 'size' => 5], // Input
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Edge: Page 11/10'     => [
                ['total' => 100, 'numItems' => 10, 'current' => 11, 'size' => 5], // Input
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Fraction: Page 11/12' => [
                ['total' => 115, 'numItems' => 10, 'current' => 11, 'size' => 5], // Input
                ['begin' => 11, 'end' => 12], // Expected
            ],
            'Fraction: Page 12/12' => [
                ['total' => 115, 'numItems' => 10, 'current' => 12, 'size' => 5], // Input
                ['begin' => 11, 'end' => 12], // Expected
            ],
            // ----
            'Page 1/1/5'           => [
                ['total' => 5, 'numItems' => 10, 'current' => 1, 'size' => 5], // Input
                // [1]
                ['begin' => 1, 'end' => 1], // Expected
            ],
            // ----
            'Page 2/3/5'           => [
                ['total' => 30, 'numItems' => 10, 'current' => 2, 'size' => 5], // Input
                // 1 [2] 3
                ['begin' => 1, 'end' => 3], // Expected
            ],
        ];
    }

    /**
     * @throws \ReflectionException
     * @dataProvider _calcCenterProvider
     */
    public function test_calcCenter(array $input, array $expected): void
    {
        $method = getAccessibleMethod(Pagination::class, '_calcCenter');
        $result = $method->invoke(null, ...$input);
        $this->assertEquals($expected, $result);
    }

    public function _calcCenterProvider(): array
    {
        return [
            'Page 1/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 1, 'size' => 5], // Input
                // [1] 2 3 4 5
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 2/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 2, 'size' => 5], // Input
                // 1 [2] 3 4 5
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 3/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 3, 'size' => 5], // Input
                // 1 2 [3] 4 5
                ['begin' => 1, 'end' => 5], // Expected
            ],
            'Page 4/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 4, 'size' => 5], // Input
                // 2 3 [4] 5 6
                ['begin' => 2, 'end' => 6], // Expected
            ],
            'Page 5/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 5, 'size' => 5], // Input
                // 3 4 [5] 6 7
                ['begin' => 3, 'end' => 7], // Expected
            ],
            'Page 6/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 6, 'size' => 5], // Input
                // 4 5 [6] 7 8
                ['begin' => 4, 'end' => 8], // Expected
            ],
            'Page 7/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 7, 'size' => 5], // Input
                // 5 6 [7] 8 9
                ['begin' => 5, 'end' => 9], // Expected
            ],
            'Page 8/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 8, 'size' => 5], // Input
                // 6 7 [8] 9 101
                ['begin' => 6, 'end' => 10], // Expected
            ],
            'Page 9/13/5'  => [
                ['total' => 128, 'numItems' => 10, 'current' => 9, 'size' => 5], // Input
                // 7 8 [9] 10 11
                ['begin' => 7, 'end' => 11], // Expected
            ],
            'Page 10/13/5' => [
                ['total' => 128, 'numItems' => 10, 'current' => 10, 'size' => 5], // Input
                // 8 9 [10] 11 12
                ['begin' => 8, 'end' => 12], // Expected
            ],
            'Page 11/13/5' => [
                ['total' => 128, 'numItems' => 10, 'current' => 11, 'size' => 5], // Input
                // 9 10 [11] 12 13
                ['begin' => 9, 'end' => 13], // Expected
            ],
            'Page 12/13/5' => [
                ['total' => 128, 'numItems' => 10, 'current' => 12, 'size' => 5], // Input
                // 9 10 11 [12] 13
                ['begin' => 9, 'end' => 13], // Expected
            ],
            'Page 13/13/5' => [
                ['total' => 128, 'numItems' => 10, 'current' => 13, 'size' => 5], // Input
                // 9 10 11 12 [13]
                ['begin' => 9, 'end' => 13], // Expected
            ],
            // ----
            'Page 1/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 1, 'size' => 6], // Input
                // [1] 2 3 4 5 6
                ['begin' => 1, 'end' => 6], // Expected
            ],
            'Page 2/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 2, 'size' => 6], // Input
                // 1 [2] 3 4 5 6
                ['begin' => 1, 'end' => 6], // Expected
            ],
            'Page 3/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 3, 'size' => 6], // Input
                // 1 2 [3] 4 5 6
                ['begin' => 1, 'end' => 6], // Expected
            ],
            'Page 4/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 4, 'size' => 6], // Input
                // 2 3 [4] 5 6 7
                ['begin' => 2, 'end' => 7], // Expected
            ],
            'Page 5/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 5, 'size' => 6], // Input
                // 3 4 [5] 6 7 8
                ['begin' => 3, 'end' => 8], // Expected
            ],
            'Page 6/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 6, 'size' => 6], // Input
                //  3 4 5 [6] 7 8
                ['begin' => 3, 'end' => 8], // Expected
            ],
            'Page 7/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 7, 'size' => 6], // Input
                // 3 4 5 6 [7] 8
                ['begin' => 3, 'end' => 8], // Expected
            ],
            'Page 8/8/6'   => [
                ['total' => 78, 'numItems' => 10, 'current' => 8, 'size' => 6], // Input
                // 3 4 5 6 7 [8]
                ['begin' => 3, 'end' => 8], // Expected
            ],
            // ----
            'Page 1/1/5'   => [
                ['total' => 5, 'numItems' => 10, 'current' => 1, 'size' => 5], // Input
                // [1]
                ['begin' => 1, 'end' => 1], // Expected
            ],
            // ----
            'Page 2/3/5'   => [
                ['total' => 30, 'numItems' => 10, 'current' => 2, 'size' => 5], // Input
                // 1 [2] 3
                ['begin' => 1, 'end' => 3], // Expected
            ],
        ];
    }
}
