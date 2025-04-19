# User Profile Update Implementation

## Task Overview
Implement user profile update functionality, focusing on updating test date and target score fields.

## Task Status
In Progress

## Requirements
1. Create UserController for handling profile updates
2. Create UpdateProfileRequest for validation
3. Implement update profile endpoint
4. Follow Laravel 11 standards
5. Use type hints and return types
6. Follow PSR-12 standards
7. Handle camelCase field names from frontend using middleware

## Implementation Plan

### 1. Form Request Layer
- [X] Create UpdateProfileRequest using `php artisan make:request User/UpdateProfileRequest`
  - [X] Validate fields:
    - test_date: required, date
    - target_score: required, integer, min:0, max:990

### 2. Controller Layer
- [ ] Create UserController using `php artisan make:controller User/UserController`
  - Implement updateProfile method
  - Use UpdateProfileRequest for validation
  - Return updated user directly
  - Handle errors appropriately

### 3. Routes
- [ ] Add route for profile update
  - PUT /api/users/profile
  - Protected by auth middleware

## Scratchpad
- [X] Need to create UpdateProfileRequest:
  - [X] test_date: required, date
  - [X] target_score: required, integer, min:0, max:990
- [ ] Need to create UserController:
  - Implement updateProfile method
  - Use form request for validation
  - Return updated user directly
- [ ] Need to add route:
  - PUT /api/users/profile
  - Add auth middleware

## Task Lessons
- Use form requests for validation
- Follow RESTful API standards
- Use proper HTTP methods and status codes
- Handle errors gracefully
- Use type hints and return types
- Follow PSR-12 standards 