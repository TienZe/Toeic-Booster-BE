# Task Overview
Create API endpoint for adding a new vocabulary entry, including the necessary database migration, model definition, repository, service, controller, and API endpoint.

# Task Status
`Done`

# Requirements
1. Create migration for the vocabulary table with the following structure:
   - id (integer, primary key)
   - word (varchar, unique)
   - thumbnail (varchar)
   - thumbnail_public_id (varchar) - Needed for Cloudinary integration
   - part_of_speech (varchar) - Will use an enum
   - meaning (varchar)
   - definition (varchar)
   - pronunciation (varchar)
   - pronunciation_audio (varchar)
   - pronunciation_audio_public_id (varchar) - Needed for Cloudinary integration
   - example (varchar)
   - example_meaning (varchar)
   - example_audio (varchar)
   - example_audio_public_id (varchar) - Needed for Cloudinary integration
2. Create PartOfSpeech enum with values: NOUN, VERB, ADJECTIVE, ADVERB, PRONOUN, PREPOSITION, CONJUNCTION, INTERJECTION, DETERMINER
3. Define Vocabulary model
4. Create minimal VocabularyRepository with create method
5. Create minimal VocabularyService with createVocabulary method
6. Create minimal VocabularyController with store method
7. Create API endpoint for adding a vocabulary

# Scratchpad
Based on previous implementations, I'll follow these patterns:
- Models use `$guarded = []` instead of $fillable
- Controllers return Eloquent models directly
- Repositories handle basic CRUD operations
- Services contain business logic and call repositories
- Repository methods use type hints and return types
- Don't implement authorize() in form requests
- Use Cloudinary for file uploads (thumbnail and audio files)
- Use PHP 8.1 enum feature for part_of_speech field

## Step 1: Create Migration for Vocabulary Table
- [X] Navigate to src/ directory
- [X] Generate migration file using Laravel artisan command: `php artisan make:migration create_vocabularies_table`
- [X] Define the structure in the migration file with all required fields
- [X] Add unique constraint on word field
- [X] Add nullable fields for public_id fields (Cloudinary)
- [X] Run the migration with `php artisan migrate`

## Step 2: Create PartOfSpeech Enum
- [X] Create PartOfSpeech enum class in `app/Enums/PartOfSpeech.php`
- [X] Define enum cases: NOUN, VERB, ADJECTIVE, ADVERB, PRONOUN, PREPOSITION, CONJUNCTION, INTERJECTION, DETERMINER
- [X] Add methods to get all values and names as arrays

## Step 3: Define Vocabulary Model
- [X] Create Vocabulary model using artisan command: `php artisan make:model Vocabulary`
- [X] Add `protected $guarded = []` for mass assignment
- [X] Add cast for part_of_speech to use the enum
- [X] Define constants for Cloudinary folders:
  - [X] THUMBNAIL_FOLDER = 'vocabulary_thumbnails'
  - [X] PRONUNCIATION_AUDIO_FOLDER = 'vocabulary_pronunciation_audios'
  - [X] EXAMPLE_AUDIO_FOLDER = 'vocabulary_example_audios'

## Step 4: Create Form Request for Validation
- [X] Create StoreVocabularyRequest: `php artisan make:request Vocabulary/StoreVocabularyRequest`
- [X] Validate:
  - [X] word: required|string|max:255|unique:vocabularies
  - [X] thumbnail: nullable|string
  - [X] part_of_speech: required|string|in:[enum values]
  - [X] meaning: required|string
  - [X] definition: nullable|string
  - [X] pronunciation: nullable|string
  - [X] pronunciation_audio: nullable|string
  - [X] example: nullable|string
  - [X] example_meaning: nullable|string
  - [X] example_audio: nullable|string

## Step 5: Create VocabularyRepository
- [X] Create VocabularyRepository class in `app/Repositories/VocabularyRepository.php`
- [X] Implement create(array $data): Vocabulary method

## Step 6: Create VocabularyService
- [X] Create VocabularyService class in `app/Services/VocabularyService.php`
- [X] Inject VocabularyRepository in constructor
- [X] Implement createVocabulary(array $data): Vocabulary method
- [X] Add file upload handling for:
  - [X] thumbnail using Cloudinary
  - [X] pronunciation_audio using Cloudinary
  - [X] example_audio using Cloudinary

## Step 7: Create VocabularyController
- [X] Create VocabularyController: `php artisan make:controller VocabularyController`
- [X] Inject VocabularyService in constructor
- [X] Implement store(StoreVocabularyRequest $request) method

## Step 8: Define API Route
- [X] Add route for creating a vocabulary: POST /vocabularies

# Task Lessons
1. PHP 8.1 enums can be used to limit the possible values for fields like part_of_speech
2. Multiple file uploads (thumbnail and audio files) can be handled in a service class using Cloudinary