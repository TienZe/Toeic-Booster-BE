# Authentication Form Request Refactor

## Task Overview
Refactor the Authentication module to use Form Request validation for all endpoints. This will improve code organization, reusability, and maintainability by separating validation logic from controllers.

## Task Status
Done

## Requirements
1. Create Form Request classes for each authentication endpoint in `app/Http/Requests/Auth`:
   - RegisterRequest
   - LoginRequest
2. Move validation rules from controllers to Form Requests
3. Update AuthController to use Form Requests
4. Ensure consistent error response format
5. Follow Laravel 11 standards
6. Use type hints and return types
7. Follow PSR-12 standards
8. Handle camelCase field names from frontend using middleware
9. Create form request by using php artisan command line.
    
## Implementation Plan

### 1. Middleware Layer
- [X] Create CamelCaseToSnakeCaseMiddleware
  - [X] Transform request input from camelCase to snake_case
  - [X] Handle nested arrays and objects
  - [X] Register as global middleware
  - [X] Add to middleware stack before validation

### 2. Form Request Layer
- [X] Create RegisterRequest using `php artisan make:request Auth/RegisterRequest`
  - [X] Validate fields (now in snake_case):
    - name: required, string, max:255
    - email: required, email, unique:users
    - password: required, min:8, confirmed
    - password_confirmation: required
  - [X] Custom validation messages
- [X] Create LoginRequest using `php artisan make:request Auth/LoginRequest`
  - [X] Validate fields (now in snake_case):
    - email: required, email
    - password: required
  - [X] Custom validation messages

### 3. Controller Layer Updates
- [X] Update AuthController
  - [X] Remove validation logic
  - [X] Use Form Requests
  - [X] Update method signatures
  - [X] Ensure consistent response format

### 4. Testing
- [X] Test middleware transformation
  - [X] Test simple fields
  - [X] Test nested arrays
  - [X] Test nested objects
- [X] Test validation rules
- [X] Test error responses
- [X] Test successful scenarios

## Scratchpad
- [X] Need to create CamelCaseToSnakeCaseMiddleware:
  - [X] Transform request input
  - [X] Handle nested data
  - [X] Register in bootstrap/app.php
- [X] Need to create RegisterRequest using `php artisan make:request Auth/RegisterRequest`:
  - [X] name: required, string, max:255
  - [X] email: required, email, unique:users
  - [X] password: required, min:8, confirmed
  - [X] password_confirmation: required
- [X] Need to create LoginRequest using `php artisan make:request Auth/LoginRequest`:
  - [X] email: required, email
  - [X] password: required
- [X] Need to update AuthController to use Form Requests
- [X] Need to test all validation scenarios
- [X] Need to test middleware transformation

## Task Lessons
- Use Form Requests for better code organization
- Separate validation logic from controllers
- Use consistent validation messages
- Follow Laravel's validation best practices
- Use type hints and return types
- Follow PSR-12 standards
- Organize requests by controller namespace
- Use middleware for input transformation
- Keep transformation logic separate from validation
- Always create form requests using Laravel commands for consistency 