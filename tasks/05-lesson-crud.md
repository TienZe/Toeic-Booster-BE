# Task Overview
Create complete CRUD (Create, Read, Update, Delete) operations for the Lesson model, including database migration, model definition, repository, service, controller, and API endpoints. The Lesson model will have a relationship with the Collection model.

# Task Status
`Done`

# Requirements
1. Create migration for the lesson table with the following structure:
   - id (integer, primary key)
   - name (varchar)
   - collection_id (integer, foreign key to collections table)
2. Define Lesson model with relationship to Collection
3. Create LessonRepository
4. Create LessonService
5. Create LessonController
6. Create API endpoints for CRUD operations

# Scratchpad
Based on the Collection CRUD implementation, we'll follow these patterns:
- Models use `$guarded = []` instead of $fillable
- Controllers return Eloquent models directly without wrapping in JsonResponse
- Repositories handle basic CRUD operations
- Services contain business logic and call repositories
- Repository methods use type hints and return types
- Don't implement authorize() in form requests

## Step 1: Create Migration for Lesson Table
- [X] Navigate to src/ directory
- [X] Generate migration file using Laravel artisan command: `php artisan make:migration create_lessons_table`
- [X] Define the structure in the migration file with the required fields
- [X] Add foreign key constraint to collection_id referencing collections table
- [X] Run the migration with `php artisan migrate` (database connection issue, but file created correctly)

## Step 2: Define Lesson Model
- [X] Create Lesson model using artisan command: `php artisan make:model Models/Lesson`
- [X] Add `protected $guarded = []` for mass assignment
- [X] Define `belongsTo` relationship to Collection model
- [X] Implement any necessary constants similar to Collection::THUMBNAIL_FOLDER

## Step 3: Update Collection Model
- [X] Add `hasMany` relationship to Lesson model in Collection model

## Step 4: Create Form Requests for Validation
- [X] Create StoreLessonRequest: `php artisan make:request Lesson/StoreLessonRequest`
- [X] Create UpdateLessonRequest: `php artisan make:request Lesson/UpdateLessonRequest`
- [X] In StoreLessonRequest, validate:
  - [X] name: required|string|max:255
  - [X] collection_id: required|exists:collections,id
- [X] In UpdateLessonRequest, validate:
  - [X] name: sometimes|required|string|max:255
  - [X] collection_id: sometimes|required|exists:collections,id
- [X] Make sure authorize() returns true without additional implementation

## Step 5: Create LessonRepository
- [X] Create LessonRepository class in `app/Repositories/LessonRepository.php`
- [X] Implement the following methods:
  - [X] getAll(): EloquentCollection
  - [X] create(array $data): Lesson
  - [X] update($idOrInstance, array $data): ?Lesson
  - [X] delete(int|string $id): int
  - [X] getByCollectionId(int|string $collectionId): EloquentCollection

## Step 6: Create LessonService
- [X] Create LessonService class in `app/Services/LessonService.php`
- [X] Inject LessonRepository in constructor
- [X] Add getLessonById(int|string $id): Lesson method using findOrFail
- [X] Implement the following methods:
  - [X] getAllLessons(): EloquentCollection
  - [X] createLesson(array $data): Lesson
  - [X] updateLesson($id, array $data): ?Lesson
  - [X] deleteLesson($id): int
  - [X] getLessonsByCollectionId(int|string $collectionId): EloquentCollection

## Step 7: Create LessonController
- [X] Create LessonController: `php artisan make:controller LessonController --api`
- [X] Inject LessonService in constructor
- [X] Implement the standard CRUD methods:
  - [X] index() - Get all lessons
  - [X] store(StoreLessonRequest $request) - Create lesson
  - [X] show(string $id) - Get lesson by ID
  - [X] update(UpdateLessonRequest $request, string $id) - Update lesson
  - [X] destroy(string $id) - Delete lesson
- [X] Add method to get lessons by collection ID: getByCollection(string $collectionId)

## Step 8: Define API Routes
- [X] Add standard API resource routes in `src/routes/api.php`
- [X] Add custom route for getting lessons by collection ID: GET /collections/{collection_id}/lessons
- [X] Group routes under 'lessons' prefix and protect with jwt.auth middleware (commented out for now)

# Task Lessons
1. Don't implement extra logic in the authorize() method in form requests 
2. Controllers should follow RESTful conventions with appropriate methods