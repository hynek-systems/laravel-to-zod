<?php

use Hynek\LaravelToZod\LaravelToZodConverter;

it('converts user registration form validation rules', function () {
    $rules = [
        'first_name' => 'required|string|min:2|max:50',
        'last_name' => 'required|string|min:2|max:50',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|max:255',
        'password_confirmation' => 'required|string|same:password',
        'birth_date' => 'required|date|before:today',
        'phone' => 'nullable|string|regex:/^[\+]?[1-9][\d]{0,15}$/',
        'terms_accepted' => 'required|boolean|accepted',
        'newsletter_subscription' => 'sometimes|boolean',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    // Verify the generated Zod schema contains expected validations
    expect($zodSchema)->toContain("import { z } from 'zod'");
    expect($zodSchema)->toContain('const schema = z.object({');
    
    // Check specific field conversions
    expect($zodSchema)->toContain('first_name: z.string().min(2).max(50)');
    expect($zodSchema)->toContain('last_name: z.string().min(2).max(50)');
    expect($zodSchema)->toContain('email: z.string().email().max(255)');
    expect($zodSchema)->toContain('password: z.string().min(8).max(255)');
    expect($zodSchema)->toContain('birth_date: z.date()');
    expect($zodSchema)->toContain('phone: z.string().regex(/^[\+]?[1-9][\d]{0,15}$/).nullable()');
    expect($zodSchema)->toContain('terms_accepted: z.boolean()');
    expect($zodSchema)->toContain('newsletter_subscription: z.boolean().optional()');
    
    expect($zodSchema)->toContain('});');
});

it('converts product creation form validation rules', function () {
    $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:products',
        'description' => 'nullable|string|max:5000',
        'price' => 'required|numeric|min:0|max:999999.99',
        'category_id' => 'required|integer|exists:categories,id',
        'status' => 'required|in:draft,published,archived',
        'tags' => 'array|max:10',
        'tags.*' => 'string|max:50',
        'images' => 'array|min:1|max:5',
        'featured' => 'boolean',
        'available_from' => 'nullable|date|after_or_equal:today',
        'stock_quantity' => 'sometimes|integer|min:0',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toContain('name: z.string().max(255)');
    expect($zodSchema)->toContain('slug: z.string().max(255)');
    expect($zodSchema)->toContain('description: z.string().max(5000).nullable()');
    expect($zodSchema)->toContain('price: z.number().min(0).max(999999.99)');
    expect($zodSchema)->toContain('category_id: z.number()');
    expect($zodSchema)->toContain("status: z.enum(['draft', 'published', 'archived'])");
    expect($zodSchema)->toContain('tags: z.array().max(10)');
    expect($zodSchema)->toContain('images: z.array().min(1).max(5)');
    expect($zodSchema)->toContain('featured: z.boolean()');
    expect($zodSchema)->toContain('available_from: z.date().nullable()');
    expect($zodSchema)->toContain('stock_quantity: z.number().min(0).optional()');
});

it('converts API request validation rules', function () {
    $rules = [
        'api_key' => 'required|string|size:32',
        'request_id' => 'required|uuid',
        'timestamp' => 'required|integer|min:1000000000',
        'data' => 'required|array',
        'data.user_id' => 'required|integer|min:1',
        'data.action' => 'required|in:create,update,delete',
        'data.payload' => 'sometimes|array',
        'metadata' => 'nullable|array',
        'signature' => 'required|string|regex:/^[a-f0-9]{64}$/i',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toContain('api_key: z.string().length(32)');
    expect($zodSchema)->toContain('request_id: z.string().uuid()');
    expect($zodSchema)->toContain('timestamp: z.number().min(1000000000)');
    expect($zodSchema)->toContain('data: z.array()');
    expect($zodSchema)->toContain("'data.user_id': z.number().min(1)");
    expect($zodSchema)->toContain("'data.action': z.enum(['create', 'update', 'delete'])");
    expect($zodSchema)->toContain("'data.payload': z.array().optional()");
    expect($zodSchema)->toContain('metadata: z.array().nullable()');
    expect($zodSchema)->toContain('signature: z.string().regex(/^[a-f0-9]{64}$/i)');
});

it('handles form validation with conditional rules', function () {
    $rules = [
        'type' => 'required|in:individual,business',
        'first_name' => 'required_if:type,individual|string|max:50',
        'last_name' => 'required_if:type,individual|string|max:50',
        'company_name' => 'required_if:type,business|string|max:100',
        'tax_id' => 'required_if:type,business|string|max:20',
        'email' => 'required|email',
        'country' => 'required|string|size:2',
        'state' => 'sometimes|string|max:100',
        'postal_code' => 'required|string|between:3,10',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    expect($zodSchema)->toContain("type: z.enum(['individual', 'business'])");
    expect($zodSchema)->toContain('first_name: z.string().max(50)');
    expect($zodSchema)->toContain('last_name: z.string().max(50)');
    expect($zodSchema)->toContain('company_name: z.string().max(100)');
    expect($zodSchema)->toContain('tax_id: z.string().max(20)');
    expect($zodSchema)->toContain('email: z.string().email()');
    expect($zodSchema)->toContain('country: z.string().length(2)');
    expect($zodSchema)->toContain('state: z.string().max(100).optional()');
    expect($zodSchema)->toContain('postal_code: z.string().min(3).max(10)');
});

it('generates complete Zod schema that can be used in TypeScript', function () {
    $rules = [
        'username' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
        'email' => 'required|email',
        'age' => 'nullable|integer|min:13|max:120',
        'preferences' => 'array',
        'is_admin' => 'boolean',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    // Verify it's a complete, well-formed Zod schema
    expect($zodSchema)->toStartWith("import { z } from 'zod';");
    expect($zodSchema)->toContain('const schema = z.object({');
    expect($zodSchema)->toEndWith('});');
    
    // Verify all fields are present and properly formatted
    $lines = explode("\n", $zodSchema);
    $schemaLines = array_filter($lines, fn($line) => str_contains($line, ':'));
    
    expect(count($schemaLines))->toBe(5); // Should have 5 field definitions
    
    // Each line should be properly indented and formatted
    foreach ($schemaLines as $line) {
        expect($line)->toStartWith('  '); // Proper indentation
        expect($line)->toContain(': z.'); // Zod type definition
    }
});

it('preserves field order in the generated schema', function () {
    $rules = [
        'zebra' => 'string',
        'alpha' => 'string',
        'beta' => 'string',
        'gamma' => 'string',
    ];

    $converter = new LaravelToZodConverter($rules);
    $zodSchema = $converter->toZodSchema();

    // Fields should appear in the same order as the input
    $zebraPosition = strpos($zodSchema, 'zebra:');
    $alphaPosition = strpos($zodSchema, 'alpha:');
    $betaPosition = strpos($zodSchema, 'beta:');
    $gammaPosition = strpos($zodSchema, 'gamma:');

    expect($zebraPosition)->toBeLessThan($alphaPosition);
    expect($alphaPosition)->toBeLessThan($betaPosition);
    expect($betaPosition)->toBeLessThan($gammaPosition);
});
