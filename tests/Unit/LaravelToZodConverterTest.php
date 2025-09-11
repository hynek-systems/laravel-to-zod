<?php

use Hynek\LaravelToZod\LaravelToZodConverter;

it('converts basic string validation rules', function () {
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['name'])->toBe([
        'type' => 'string',
        'validations' => ['max(255)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['email'])->toBe([
        'type' => 'string',
        'validations' => ['email()'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts numeric validation rules', function () {
    $rules = [
        'age' => 'required|integer|min:18|max:120',
        'price' => 'required|numeric|gt:0',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['age'])->toBe([
        'type' => 'number',
        'validations' => ['min(18)', 'max(120)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['price'])->toBe([
        'type' => 'number',
        'validations' => ['gt(0)'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts boolean validation rules', function () {
    $rules = [
        'is_active' => 'boolean',
        'terms_accepted' => 'required|boolean',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['is_active'])->toBe([
        'type' => 'boolean',
        'validations' => [],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['terms_accepted'])->toBe([
        'type' => 'boolean',
        'validations' => [],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts array validation rules', function () {
    $rules = [
        'tags' => 'array|min:1|max:10',
        'categories' => 'required|array',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['tags'])->toBe([
        'type' => 'array',
        'validations' => ['min(1)', 'max(10)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['categories'])->toBe([
        'type' => 'array',
        'validations' => [],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts enum validation rules', function () {
    $rules = [
        'status' => 'required|in:active,inactive,pending',
        'priority' => 'in:1,2,3,4,5',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['status'])->toBe([
        'type' => 'enum',
        'validations' => ["enum(['active', 'inactive', 'pending'])"],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['priority'])->toBe([
        'type' => 'enum',
        'validations' => ['enum([1, 2, 3, 4, 5])'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('handles optional and nullable rules', function () {
    $rules = [
        'optional_field' => 'sometimes|string',
        'nullable_field' => 'nullable|string',
        'optional_nullable' => 'sometimes|nullable|string',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['optional_field'])->toBe([
        'type' => 'string',
        'validations' => [],
        'optional' => true,
        'nullable' => false,
    ]);

    expect($schema['nullable_field'])->toBe([
        'type' => 'string',
        'validations' => [],
        'optional' => false,
        'nullable' => true,
    ]);

    expect($schema['optional_nullable'])->toBe([
        'type' => 'string',
        'validations' => [],
        'optional' => true,
        'nullable' => true,
    ]);
});

it('converts date validation rules', function () {
    $rules = [
        'birth_date' => 'required|date',
        'start_date' => 'date|after:2023-01-01',
        'end_date' => 'date|before:2024-12-31',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['birth_date'])->toBe([
        'type' => 'date',
        'validations' => [],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['start_date'])->toBe([
        'type' => 'date',
        'validations' => ["min(new Date('2023-01-01'))"],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['end_date'])->toBe([
        'type' => 'date',
        'validations' => ["max(new Date('2024-12-31'))"],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts specialized string validation rules', function () {
    $rules = [
        'email' => 'required|email',
        'website' => 'url',
        'user_id' => 'uuid',
        'phone' => 'regex:/^\+?[1-9]\d{1,14}$/',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['email'])->toBe([
        'type' => 'string',
        'validations' => ['email()'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['website'])->toBe([
        'type' => 'string',
        'validations' => ['url()'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['user_id'])->toBe([
        'type' => 'string',
        'validations' => ['uuid()'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['phone'])->toBe([
        'type' => 'string',
        'validations' => ['regex(/^\+?[1-9]\d{1,14}$/)'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts size and length validation rules', function () {
    $rules = [
        'username' => 'string|size:10',
        'description' => 'string|between:10,500',
        'code' => 'digits:6',
        'pin' => 'digits_between:4,8',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['username'])->toBe([
        'type' => 'string',
        'validations' => ['length(10)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['description'])->toBe([
        'type' => 'string',
        'validations' => ['min(10).max(500)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['code'])->toBe([
        'type' => 'string',
        'validations' => ['regex(/^\d{6}$/)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['pin'])->toBe([
        'type' => 'string',
        'validations' => ['regex(/^\d{4,8}$/)'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('converts number comparison validation rules', function () {
    $rules = [
        'score' => 'numeric|gte:0|lte:100',
        'quantity' => 'integer|gt:0|lt:1000',
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['score'])->toBe([
        'type' => 'number',
        'validations' => ['gte(0)', 'lte(100)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['quantity'])->toBe([
        'type' => 'number',
        'validations' => ['gt(0)', 'lt(1000)'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('handles array format validation rules', function () {
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'age' => ['integer', 'min:18'],
    ];

    $converter = new LaravelToZodConverter($rules);
    $schema = $converter->getSchema();

    expect($schema['name'])->toBe([
        'type' => 'string',
        'validations' => ['max(255)'],
        'optional' => false,
        'nullable' => false,
    ]);

    expect($schema['age'])->toBe([
        'type' => 'number',
        'validations' => ['min(18)'],
        'optional' => false,
        'nullable' => false,
    ]);
});

it('generates valid JSON output', function () {
    $rules = [
        'name' => 'required|string|max:255',
        'age' => 'integer|min:18',
    ];

    $converter = new LaravelToZodConverter($rules);
    $json = $converter->toJSON();

    expect($json)->toBeString();
    expect(json_decode($json, true))->toBeArray();
});

it('generates Zod schema JavaScript code', function () {
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'age' => 'integer|min:18|max:120',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toBeString();
    expect($zodSchema)->toContain("import { z } from 'zod'");
    expect($zodSchema)->toContain('const schema = z.object({');
    expect($zodSchema)->toContain('name: z.string().max(255)');
    expect($zodSchema)->toContain('email: z.string().email()');
    expect($zodSchema)->toContain('age: z.number().min(18).max(120)');
    expect($zodSchema)->toContain('});');
});

it('handles optional and nullable in Zod schema output', function () {
    $rules = [
        'required_field' => 'required|string',
        'optional_field' => 'sometimes|string',
        'nullable_field' => 'nullable|string',
        'optional_nullable' => 'sometimes|nullable|string',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toContain('required_field: z.string()');
    expect($zodSchema)->toContain('optional_field: z.string().optional()');
    expect($zodSchema)->toContain('nullable_field: z.string().nullable()');
    expect($zodSchema)->toContain('optional_nullable: z.string().nullish()');
});

it('handles enum types in Zod schema output', function () {
    $rules = [
        'status' => 'required|in:active,inactive,pending',
        'priority' => 'nullable|in:low,medium,high',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toContain("status: z.enum(['active', 'inactive', 'pending'])");
    expect($zodSchema)->toContain("priority: z.enum(['low', 'medium', 'high']).nullable()");
});

it('converts complex validation scenarios', function () {
    $rules = [
        'username' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
        'password' => 'required|string|min:8|max:255',
        'age' => 'sometimes|integer|min:13|max:120',
        'role' => 'required|in:admin,user,moderator',
        'tags' => 'array|min:1|max:5',
        'is_verified' => 'boolean',
        'created_at' => 'date',
        'website' => 'nullable|url',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toContain("username: z.string().min(3).max(20).regex(/^[a-zA-Z0-9_]+$/)");
    expect($zodSchema)->toContain("password: z.string().min(8).max(255)");
    expect($zodSchema)->toContain("age: z.number().min(13).max(120).optional()");
    expect($zodSchema)->toContain("role: z.enum(['admin', 'user', 'moderator'])");
    expect($zodSchema)->toContain("tags: z.array().min(1).max(5)");
    expect($zodSchema)->toContain("is_verified: z.boolean()");
    expect($zodSchema)->toContain("created_at: z.date()");
    expect($zodSchema)->toContain("website: z.string().url().nullable()");
});
