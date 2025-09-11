# Laravel to Zod Converter

A PHP package that converts Laravel validation rules to [Zod](https://zod.dev/) schema JavaScript code, enabling seamless validation sharing between your Laravel backend and TypeScript/JavaScript frontend.

## Installation

Install the package via Composer:

```bash
composer require hynek/laravel-to-zod
```

The package will automatically register its service provider in Laravel 5.5+.

## Basic Usage

### Converting Validation Rules

```php
use Hynek\LaravelToZod\LaravelToZodConverter;

// Define your Laravel validation rules
$rules = [
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'age' => 'nullable|integer|min:18|max:120',
    'preferences' => 'array',
    'is_admin' => 'boolean',
];

// Convert to Zod schema
$converter = new LaravelToZodConverter($rules);
$zodSchema = $converter->toZodSchema();

echo $zodSchema;
```

**Output:**
```javascript
import { z } from 'zod';

const schema = z.object({
  name: z.string().max(255),
  email: z.string().email().max(255),
  age: z.number().min(18).max(120).nullable(),
  preferences: z.array(),
  is_admin: z.boolean()
});
```

### Working with Form Requests

```php
use App\Http\Requests\UserRegistrationRequest;
use Hynek\LaravelToZod\LaravelToZodConverter;

class UserController extends Controller
{
    public function getValidationSchema()
    {
        $request = new UserRegistrationRequest();
        $converter = new LaravelToZodConverter($request->rules());
        
        return response()->json([
            'schema' => $converter->toZodSchema()
        ]);
    }
}
```

## Supported Laravel Validation Rules

### Basic Types
- `string` → `z.string()`
- `integer`, `numeric` → `z.number()`
- `boolean` → `z.boolean()`
- `array` → `z.array()`
- `date` → `z.date()`

### String Validations
- `email` → `z.string().email()`
- `url` → `z.string().url()`
- `uuid` → `z.string().uuid()`
- `min:n` → `z.string().min(n)`
- `max:n` → `z.string().max(n)`
- `size:n` → `z.string().length(n)`
- `between:min,max` → `z.string().min(min).max(max)`
- `regex:pattern` → `z.string().regex(/pattern/)`

### Number Validations
- `min:n` → `z.number().min(n)`
- `max:n` → `z.number().max(n)`
- `gt:n` → `z.number().gt(n)`
- `gte:n` → `z.number().gte(n)`
- `lt:n` → `z.number().lt(n)`
- `lte:n` → `z.number().lte(n)`

### Array Validations
- `array` → `z.array()`
- `min:n` → `z.array().min(n)`
- `max:n` → `z.array().max(n)`
- `size:n` → `z.array().length(n)`

### Enum Validations
- `in:value1,value2,value3` → `z.enum(['value1', 'value2', 'value3'])`

### Modifiers
- `required` → Field is required (default behavior)
- `nullable` → `.nullable()`
- `optional`, `sometimes` → `.optional()`

## Advanced Usage

### Nested Field Names

The package properly handles nested validation rules with dot notation:

```php
$rules = [
    'user.name' => 'required|string|max:50',
    'user.email' => 'required|email',
    'data.preferences' => 'array',
];

$converter = new LaravelToZodConverter($rules);
echo $converter->toZodSchema();
```

**Output:**
```javascript
import { z } from 'zod';

const schema = z.object({
  'user.name': z.string().max(50),
  'user.email': z.string().email(),
  'data.preferences': z.array()
});
```

### Complex Validation Scenarios

```php
$rules = [
    'username' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
    'password' => 'required|string|min:8|max:255',
    'confirm_password' => 'required|string|same:password',
    'role' => 'required|in:admin,user,moderator',
    'metadata' => 'sometimes|array',
    'profile.bio' => 'nullable|string|max:1000',
];
```

### API Integration

You can easily expose Zod schemas via API endpoints for frontend consumption:

```php
Route::get('/validation-schemas/{form}', function ($form) {
    $rules = match($form) {
        'user-registration' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ],
        'product-creation' => [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,id',
        ],
        default => []
    };
    
    if (empty($rules)) {
        abort(404);
    }
    
    $converter = new LaravelToZodConverter($rules);
    
    return response($converter->toZodSchema())
        ->header('Content-Type', 'application/javascript');
});
```

### Frontend Usage

Once you have the Zod schema, you can use it in your TypeScript/JavaScript frontend:

```typescript
// Fetch the schema from your Laravel API
const response = await fetch('/validation-schemas/user-registration');
const schemaCode = await response.text();

// The schema code can be evaluated or saved to a file
// For example, save it as userRegistrationSchema.js and import it:

import { schema } from './userRegistrationSchema.js';

// Use the schema for validation
const result = schema.safeParse(formData);

if (!result.success) {
    console.log('Validation errors:', result.error.issues);
} else {
    console.log('Valid data:', result.data);
}
```

## Output Formats

The package provides two output formats:

### 1. Zod Schema JavaScript (default)
```php
$converter->toZodSchema(); // Returns complete JavaScript code
```

### 2. JSON Structure
```php
$converter->toJSON(); // Returns JSON representation of the schema structure
```

## Testing

The package includes comprehensive tests using Pest PHP:

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suites
./vendor/bin/pest tests/Unit
./vendor/bin/pest tests/Feature

# Run with coverage
./vendor/bin/pest --coverage
```

## Requirements

- PHP 8.1+
- Laravel 9.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Changelog

### 1.0.0
- Initial release
- Support for basic Laravel validation rules
- Zod schema generation
- Comprehensive test suite

---

For more information about Zod, visit the [official Zod documentation](https://zod.dev/).
