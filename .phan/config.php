<?php

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 *
 * Checked key by key against the phan 6.0.11 template in
 * vendor/phan/phan/.phan/config.php.
 */
return [

    // The PHP version phan checks this code against.
    // Supported values in phan 6: '8.1', '8.2', '8.3', '8.4', '8.5', null.
    // An older value is accepted in silence and resolved to 8.1, so the '8.0'
    // this file used to carry never meant what it looked like. It must be a
    // string; a float raises
    // TypeError: computeClosestTargetPHPVersionId(): Argument #1 ($version) must be of type string.
    //
    // The library declares PHP >= 7.0, which phan 6 cannot express at all. The
    // floor belongs to PHPCompatibility, run at `testVersion 7.0-` from
    // composer.json; what phan guards here is the ceiling, the newest PHP the
    // library has to survive.
    'target_php_version' => '8.5',

    // minimum_target_php_version is deliberately left unset. Phan reads the
    // `php` requirement from composer.json and clamps the result to 8.1, the
    // oldest version it knows, so writing '8.1' here would claim the library
    // needs 8.1 when it needs 7.0.

    // A list of directories that should be parsed for class and
    // method information. After excluding the directories
    // defined in exclude_analysis_directory_list, the remaining
    // files will be statically analyzed for errors.
    'directory_list' => [
        'src',
    ],

    // Analyzed as well. autoload.php is a file, not a directory, so it belongs
    // here: listing it under directory_list makes phan report
    // "Failed to open directory" and carry on. It was outside phan's reach
    // until now - an undeclared function planted in it went unreported - while
    // it is the one file that rewrites the library on a merchant's server.
    'file_list' => [
        'autoload.php',
    ],

    // tests/ is left out on purpose. Phan does not parse PHPUnit, since vendor/
    // is not in directory_list, so every assertion reads as a call to an
    // undeclared method: 102 of them, all noise. phpstan covers tests/ at
    // level 5 instead.

    // A directory list that defines files that will be excluded
    // from static analysis, but whose class and method
    // information should be included.
    'exclude_analysis_directory_list' => [
        'vendor/',
    ],

    // Issue types intentionally ignored.
    //
    // PhanPluginDuplicateExpressionAssignmentOperation suggests the `??=` operator,
    // which was only added in PHP 7.4. This library declares support for PHP >= 7.0,
    // so applying those suggestions would break PHP 7.0 - 7.3.
    'suppress_issue_types' => [
        'PhanPluginDuplicateExpressionAssignmentOperation',
    ],

    // Plugins bundled with phan, given by name rather than by path.
    // All eleven are present in phan 6.0.11.
    //
    // Documentation about available bundled plugins can be found
    // at https://github.com/phan/phan/tree/v5/.phan/plugins
    'plugins' => [
        // checks if a function, closure or method unconditionally returns.
        'AlwaysReturnPlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'DuplicateExpressionPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
        'SleepCheckerPlugin',
        // Checks for syntactically unreachable statements in
        // the global scope or function bodies.
        'UnreachableCodePlugin',
        'UseReturnValuePlugin',
        'EmptyStatementListPlugin',
        'LoopVariableReusePlugin',
    ],
];
