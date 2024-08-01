<?php

namespace Municipio\Customizer\Applicators;

use Kirki\Compatibility\Kirki as KirkiCompatibility;
use Kirki\Util\Helper as KirkiHelper;

abstract class AbstractApplicator
{
    private static $contextCache = [];

    /**
     * Get fields.
     * 
     * @return array
     */
    protected function getFields(): array 
    {
        $fields = [];
		if (class_exists('\Kirki\Compatibility\Kirki') ) {
			$fields = array_merge(
                KirkiCompatibility::$fields ?? [], 
                KirkiCompatibility::$all_fields ?? [],
                $fields
            );
		}
        return $fields;
    }

    /**
     * Compare values using KirkiHelper.
     * 
     * @param string $settingKey The setting key to compare.
     * @param mixed $value The value to compare.
     * @param string $operator The operator to use.
     * 
     * @return boolean  True if the values match, false otherwise.
     */
    protected function compareValues($settingKey, $value, $operator): bool
    {
        $settingKeyStoredValue = \Kirki::get_option($settingKey);
        return KirkiHelper::compare_values(
            $settingKeyStoredValue,
            $value,
            $operator
        ); 
    }

    /** 
     * Check if a callback is valid, and if it should append data.
     * 
     * @param array $activeCallback The active_callback part of the field.
     * 
     * @return boolean
     */
    protected function isValidActiveCallback($activeCallback): bool
    {
        if (is_string($activeCallback) && $activeCallback === '__return_true') {
            return true; 
        }
        
        if (is_string($activeCallback) && $activeCallback === '__return_false') {
            return false;
        } 
        
        if (is_array($activeCallback) && !empty($activeCallback)) {
            $shouldReturn = false;
            foreach ($activeCallback as $cb) {
                $cb = (object) $cb;
                if($this->compareValues($cb->setting, $cb->value,  $cb->operator)) {
                    $shouldReturn = true;
                    break;
                }
            }
            return $shouldReturn; 
        }

        return true;
    }

    /**
     * Determines if should be handled as a $type.
     *
     * @param array $field          The field to check.
     * @param string $lookForType   The type to look for.
     * @return boolean              True if the field should be handled as $type, false otherwise.
     */
    protected function isFieldType($field, $lookForType): bool
    {
        if (!$this->fieldHasOutput($field)) {
            return false;
        }

        foreach ($field['output'] as $output) {
            if ($this->fieldOutputHasMatchingType($output, $lookForType)) {
                return true;
            }
        }

        return false;
    }

    /** 
     * Determine if field has output.
     * 
     * @param array $field  The field to check.
     * 
     * @return boolean
     */
    private function fieldHasOutput(array $field): bool
    {
        if (!isset($field['output']) || !is_array($field['output'])) {
            return false;
        }

        return !empty($field['output']);
    }

    /**
     * Determine if field output has matching type.
     * 
     * @param array $output The output definition of a field. 
     * @param string $type  The type to look for.
     * 
     * @return boolean      True if the output has the matching type, false otherwise.
     */
    private function fieldOutputHasMatchingType(array $output, string $type): bool
    {
        if (!isset($output['type'])) {
            return false;
        }

        return $output['type'] === $type;
    }


    /** TODO: Refactor, move. */
    protected function hasFilterContexts(array $contexts, array $filterContexts): bool
    {

        $cacheKey = md5(serialize($contexts) . serialize($filterContexts));

        if (isset(self::$contextCache[$cacheKey])) {
            return self::$contextCache[$cacheKey];
        }

        $hasContext = [];
        foreach ($filterContexts as $context) {

            // Handle malformed configuration
            if(is_string($context)) {
                $context = [
                    'context' => $context,
                    'operator' => '=='
                ];
                error_log('Malformed context configuration detected. Please update your configuration to match the new format.');
            }

            // Verify that context is a string and format it for eval
            if (is_string($context['context'])) {
                $context['context'] = '"' . $context['context'] . '"';
            } else {
                trigger_error("Provided context value in context for modifier is not a string.", E_USER_ERROR);
            }

            // Check if provided context exists in context array and compare using operator
            $hasContext[] = (bool) eval('return in_array(' . $context['context'] . ', $contexts) ' . $context['operator'] . ' true;');
        }

        return (bool) self::$contextCache[$cacheKey] = array_product($hasContext);
    }
}
