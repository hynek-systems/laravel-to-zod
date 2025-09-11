<?php

namespace Hynek\LaravelToZod;

/**
 * Laravel to Zod Validation Rule Converter
 *
 * Converts Laravel validation rules to Zod schema JavaScript code
 */
class LaravelToZodConverter
{
    private array $rules;

    private array $zodSchema = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
        $this->convertRules();
    }

    /**
     * Convert Laravel rules to Zod schema structure
     */
    private function convertRules(): void
    {
        foreach ($this->rules as $field => $rules) {
            $this->zodSchema[$field] = $this->parseFieldRules($field, $rules);
        }
    }

    /**
     * Parse rules for a specific field
     */
    private function parseFieldRules(string $field, $rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        $zodRules = [
            'type' => 'string', // default type
            'validations' => [],
            'optional' => false,
            'nullable' => false,
        ];

        foreach ($rules as $rule) {
            $this->parseRule($rule, $zodRules);
        }

        return $zodRules;
    }

    /**
     * Parse individual validation rule
     */
    private function parseRule(string $rule, array &$zodRules): void
    {
        // Handle rules with parameters
        if (strpos($rule, ':') !== false) {
            [$ruleName, $parameters] = explode(':', $rule, 2);
            
            // Special handling for regex patterns that might contain commas
            if (strtolower($ruleName) === 'regex') {
                $params = [$parameters]; // Keep the entire regex pattern as one parameter
            } else {
                $params = explode(',', $parameters);
            }
        } else {
            $ruleName = $rule;
            $params = [];
        }

        switch (strtolower($ruleName)) {
            // Type rules
            case 'string':
                $zodRules['type'] = 'string';
                break;

            case 'integer':
            case 'numeric':
                $zodRules['type'] = 'number';
                break;

            case 'boolean':
                $zodRules['type'] = 'boolean';
                break;

            case 'array':
                $zodRules['type'] = 'array';
                break;

            case 'email':
                $zodRules['type'] = 'string';
                $zodRules['validations'][] = 'email()';
                break;

            case 'url':
                $zodRules['type'] = 'string';
                $zodRules['validations'][] = 'url()';
                break;

            case 'uuid':
                $zodRules['type'] = 'string';
                $zodRules['validations'][] = 'uuid()';
                break;

            case 'date':
                $zodRules['type'] = 'date';
                break;

                // String validations
            case 'min':
                if ($zodRules['type'] === 'string') {
                    $zodRules['validations'][] = "min({$params[0]})";
                } elseif ($zodRules['type'] === 'number') {
                    $zodRules['validations'][] = "min({$params[0]})";
                } elseif ($zodRules['type'] === 'array') {
                    $zodRules['validations'][] = "min({$params[0]})";
                }
                break;

            case 'max':
                if ($zodRules['type'] === 'string') {
                    $zodRules['validations'][] = "max({$params[0]})";
                } elseif ($zodRules['type'] === 'number') {
                    $zodRules['validations'][] = "max({$params[0]})";
                } elseif ($zodRules['type'] === 'array') {
                    $zodRules['validations'][] = "max({$params[0]})";
                }
                break;

            case 'size':
                if ($zodRules['type'] === 'string') {
                    $zodRules['validations'][] = "length({$params[0]})";
                } elseif ($zodRules['type'] === 'array') {
                    $zodRules['validations'][] = "length({$params[0]})";
                }
                break;

            case 'regex':
                // Keep the original pattern including delimiters and modifiers
                $pattern = $params[0];
                $zodRules['validations'][] = "regex({$pattern})";
                break;

            case 'in':
                $values = array_map(function ($val) {
                    return is_numeric($val) ? $val : "'{$val}'";
                }, $params);
                $enumValues = implode(', ', $values);
                $zodRules['type'] = 'enum';
                $zodRules['validations'][] = "enum([{$enumValues}])";
                break;

            case 'between':
                if ($zodRules['type'] === 'string') {
                    $zodRules['validations'][] = "min({$params[0]}).max({$params[1]})";
                } elseif ($zodRules['type'] === 'number') {
                    $zodRules['validations'][] = "min({$params[0]}).max({$params[1]})";
                }
                break;

            case 'digits':
                $zodRules['validations'][] = "regex(/^\\d{{$params[0]}}$/)";
                break;

            case 'digits_between':
                $zodRules['validations'][] = "regex(/^\\d{{$params[0]},{$params[1]}}$/)";
                break;

                // Modifiers
            case 'required':
                // Required is handled by not making it optional
                break;

            case 'nullable':
                $zodRules['nullable'] = true;
                break;

            case 'sometimes':
            case 'optional':
                $zodRules['optional'] = true;
                break;

                // Date validations
            case 'after':
                $zodRules['validations'][] = "min(new Date('{$params[0]}'))";
                break;

            case 'before':
                $zodRules['validations'][] = "max(new Date('{$params[0]}'))";
                break;

            case 'date_format':
                // Zod doesn't have direct date format validation, but we can add a custom regex
                break;

                // Number validations
            case 'gt':
                $zodRules['validations'][] = "gt({$params[0]})";
                break;

            case 'gte':
                $zodRules['validations'][] = "gte({$params[0]})";
                break;

            case 'lt':
                $zodRules['validations'][] = "lt({$params[0]})";
                break;

            case 'lte':
                $zodRules['validations'][] = "lte({$params[0]})";
                break;
        }
    }

    /**
     * Generate Zod schema field definition
     */
    private function generateZodField(array $fieldRules): string
    {
        $zodType = $this->getZodType($fieldRules['type']);
        $validations = ! empty($fieldRules['validations']) ? '.'.implode('.', $fieldRules['validations']) : '';

        $field = "z.{$zodType}(){$validations}";

        // Handle optional and nullable
        if ($fieldRules['nullable'] && $fieldRules['optional']) {
            $field .= '.nullish()';
        } elseif ($fieldRules['nullable']) {
            $field .= '.nullable()';
        } elseif ($fieldRules['optional']) {
            $field .= '.optional()';
        }

        return $field;
    }

    /**
     * Get Zod type from internal type
     */
    private function getZodType(string $type): string
    {
        return match ($type) {
            'string' => 'string',
            'number' => 'number',
            'boolean' => 'boolean',
            'array' => 'array',
            'date' => 'date',
            'enum' => '',  // enum is handled differently
            default => 'string'
        };
    }

    /**
     * Convert to JSON representation
     */
    public function toJSON(): string
    {
        return json_encode($this->zodSchema, JSON_PRETTY_PRINT);
    }

    /**
     * Convert to Zod schema JavaScript string
     */
    public function toZodSchema(): string
    {
        $schemaLines = [];

        foreach ($this->zodSchema as $field => $rules) {
            if ($rules['type'] === 'enum') {
                $enumValidation = $rules['validations'][0];
                $fieldDef = "z.{$enumValidation}";
            } else {
                $fieldDef = $this->generateZodField($rules);
            }

            // Handle optional and nullable for enum
            if ($rules['type'] === 'enum') {
                if ($rules['nullable'] && $rules['optional']) {
                    $fieldDef .= '.nullish()';
                } elseif ($rules['nullable']) {
                    $fieldDef .= '.nullable()';
                } elseif ($rules['optional']) {
                    $fieldDef .= '.optional()';
                }
            }

            // Quote field names that contain dots or special characters
            $fieldName = strpos($field, '.') !== false || preg_match('/[^a-zA-Z0-9_$]/', $field) 
                ? "'{$field}'" 
                : $field;
            
            $schemaLines[] = "  {$fieldName}: {$fieldDef}";
        }

        $schemaBody = implode(",\n", $schemaLines);

        return "import { z } from 'zod';\n\n".
            "const schema = z.object({\n".
            $schemaBody."\n".
            '});';
    }

    /**
     * Get the internal schema array (useful for debugging)
     */
    public function getSchema(): array
    {
        return $this->zodSchema;
    }
}
