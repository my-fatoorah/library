<?php

namespace MyFatoorah\Test;

/**
 * Base class for the suites that call the MyFatoorah API.
 *
 * It loads the shared API key sets once, so every suite can exercise both the
 * success path and the three authentication failure paths.
 */
abstract class MyFatoorahTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * The API key sets keyed by scenario: valid, noContent, expired, notAuthorized.
     *
     * @var array
     */
    protected $keys;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->keys = include __DIR__ . '/apiKeys.php';
    }
}
