<?php

namespace Hyperwallet\Util;

/**
 * A simple implementation of https://www.rfc-editor.org/rfc/rfc6570 for expanding Hyperwallet
 * Uniform Resource Identifier (URI) Template.
 *
 * This class was created to allow the Hyperwallet SDK to support by PHP 5.6 and latest version
 * @package Hyperwallet\Util
 */
final class HyperwalletUriTemplate
{

    /**
     * Regex pattern to find variable identifier defined by pair of braces ('{', '}').
     * e.g. {var}
     */
    const PATTERN = '/\{([^\}]+)\}/';

    /**
     * Processes URI Template
     *
     * This implementation will replace simple key defined in pair of braces ('{', '}') and replaced for the associate
     * variable value.
     *
     * E.g. $uriTemplate = `test/{var-a}/{var-b}`, $variables = array('var-a' => 'testA', 'var-b' => 'testB') will return
     * test/testA/testB
     *
     * @param string $uriTemplate the URI Template is a string that
     * contains zero or more embedded variable expressions delimited by pair of braces ('{', '}').
     * @param array $variables the variable identifiers for associating values within a template
     * processor
     * @return string
     */
    public function expand($uriTemplate, array $variables)
    {
        if (!$variables || strpos($uriTemplate, '{') === false) {
            // skip processing
            return $uriTemplate;
        }

        return \preg_replace_callback(
            self::PATTERN,
            self::buildProcessMatchResult($variables),
            $uriTemplate
        );
    }

    /**
     * Evaluates the match result and find the associated value from the defined variables
     * @param array $matches the match results
     * @param array $variables the variable identifiers for associating values within a template
     * processor
     * @return mixed
     */
    private static function processMatchResult(array $matches, array $variables)
    {
        if (!isset($variables[$matches[1]])) {
            // missing variable definition, return the match key
            return $matches[0];
        }

        return $variables[$matches[1]];
    }

    /**
     * Builds an anonymous functions to process the match result
     * @param array $variables
     * @return \Closure
     */
    private static function buildProcessMatchResult(array $variables)
    {
        return static function (array $matches) use ($variables) {
            return self::processMatchResult($matches, $variables);
        };
    }
}