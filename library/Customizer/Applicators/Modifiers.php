<?php

namespace Municipio\Customizer\Applicators;

class Modifiers extends AbstractApplicator
{
    public function __construct()
    {
        add_action('wp', array($this, 'applyModifiers'));
    }

    /**
     * Apply modifiers
     *
     * @return void
     */
    public function applyModifiers()
    {

        //Get field definition
        $fields = \Kirki::$all_fields;

        
        //Determine what's a controller var, fetch it
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $key => $field) {
                if (!$this->isFieldType($field, 'modifier')) {
                    continue;
                }

                if (isset($field['output']) && is_array($field['output']) &&  !empty($field['output']) && !$this->callBackHandler($field)) {
                    
                    foreach ($field['output'] as $output) {
                        if (isset($output['context'])) {
                            $value = \Kirki::get_option($key);
                            if (isset($output['value_map']) && is_array($output['value_map'])) {
                                if (array_key_exists((string)$value, $output['value_map'])) {
                                    $value = $output['value_map'][$value];
                                }
                            }
                            $filter = [
                                'contexts'  => $output['context'],
                                'value'     => $value,
                            ];
                        }
                    }
                }

                add_filter('ComponentLibrary/Component/Modifier', function ($modifiers, $contexts) use ($filter) {
                    if (!is_array($contexts)) {
                        $contexts = [$contexts];
                    }

                    if (!is_array($modifiers)) {
                        $modifiers = [$modifiers];
                    }

                    if (is_array($contexts) && !empty($contexts)) {
                        foreach ($contexts as $context) {
                            if (in_array($context, $filter['contexts'])) {
                                $modifiers[] = $filter['value'];
                                break;
                            }
                        }
                        // Check if contexts filter is multidimensional (new format)
                        if (count($filter['contexts']) !== count($filter['contexts'], COUNT_RECURSIVE)) {
                            if ($this->hasFilterContexts($contexts, $filter['contexts'])) {
                                $modifiers[] = $filter['value'];
                            }
                        }
                    }

                    return $modifiers;
                }, 10, 2);
            }
        }
    }

    private function callBackHandler($field) {
        $conditional = [];
        if (!empty($field['active_callback'])) {
            foreach ($field['active_callback'] as $callback) {
                $operator = $callback['operator'];
                if ($this->isValidOperator($operator)) {
                    if (!preg_match('/^[a-z\d_-]+$/i', $callback['setting']) || !preg_match('/^[a-z\d_-]+$/i', $callback['value'])) {
                        return;
                    }
                    $expression =  "\Kirki::get_option(\$callback['setting']) $operator \$callback['value'];";
                    $result = eval("return $expression;");
                    $conditional[] = $result;
                } else {
                    $conditional[] = true;
                }
            }
        } else { $conditional[] = true;}
        return in_array(false, $conditional);
    }
}
