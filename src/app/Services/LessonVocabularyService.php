<?php

namespace App\Services;

use App\Entities\GeneratedWord;
use App\Models\Lesson;
use App\Models\LessonVocabulary;
use App\Models\Vocabulary;
use App\Repositories\LessonVocabularyRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LessonVocabularyService
{
    protected $lessonVocabularyRepository;

    public function __construct(LessonVocabularyRepository $lessonVocabularyRepository)
    {
        $this->lessonVocabularyRepository = $lessonVocabularyRepository;
    }

    /**
     * Bulk store lesson vocabularies.
     */
    public function bulkStore($lessonId, array $data)
    {
        $created = [];

        DB::transaction(function () use ($lessonId, $data, &$created) {
            foreach ($data as $item) {
                // Upload media files
                // ...

                if (isset($item['vocabulary_id'])) {
                    // Attach system word
                    $created[] = $this->attachSystemVocabulary($lessonId, $item['vocabulary_id']);
                } else if (isset($item['lesson_vocabulary_id'])) {
                    // Currently, the pin word feature only apply for system word, just retrieve the vocabulary_id of the post lesson vocabulary
                    // In the future, we can expand this feat by replicate the post lesson vocabulary to the current lesson
                    $vocabulary = LessonVocabulary::with('vocabulary')->find($item['lesson_vocabulary_id'])?->vocabulary;

                    if (!$vocabulary) {
                        throw new \Exception('Vocabulary not found');
                    }

                    $created[] = $this->attachSystemVocabulary($lessonId, $vocabulary->id);
                } else {
                    // Create own vocabulary of lesson
                    $basedWord = new GeneratedWord();
                    $basedWord->fromArray($item);

                    $generatedWord = GeminiChatBotService::generateWord($basedWord);

                    $created[] = LessonVocabulary::create([
                        'lesson_id' => $lessonId,
                        'word' => $generatedWord->word,
                        'definition' => $generatedWord->definition,
                        'meaning' => $generatedWord->meaning,
                        'pronunciation' => $generatedWord->pronunciation,
                        'example' => $generatedWord->example,
                        'example_meaning' => $generatedWord->exampleMeaning,
                        'part_of_speech' => $generatedWord->partOfSpeech,

                        'pronunciation_audio' => $item['pronunciation_audio'] ?? null,
                    ]);
                }
            }
        });

        return $created;
    }

    public function attachSystemVocabulary($lessonId, $vocabularyId)
    {
        $lessonVoca = LessonVocabulary::where('lesson_id', $lessonId)
            ->where('vocabulary_id', $vocabularyId)
            ->first();

        if ($lessonVoca) {
            throw ValidationException::withMessages(['vocabulary_id' => ["The word has already been attached to this folder"]]);
        }

        return LessonVocabulary::create([
            'lesson_id' => $lessonId,
            'vocabulary_id' => $vocabularyId
        ]);
    }

    public function getLessonVocabularies($lessonId, $options = [])
    {
        $lessonVocabularies = LessonVocabulary::with('vocabulary')->where('lesson_id', $lessonId);

        $withUserLessonLearning = $options['with_user_lesson_learning'] ?? false;
        if ($withUserLessonLearning) {
            $userId = Auth::user()->id;

            if (!$userId) {
                throw new \Exception('User not found');
            }

            // Load related user learning in the lesson
            $lessonVocabularies = $lessonVocabularies->with('lessonLearnings', function ($query) use ($userId, $lessonId) {
                $query->where('user_id', $userId);
                $query->where('lesson_id', $lessonId);
            });
        }

        $lessonVocabularies = $lessonVocabularies->get();

        $lessonVocabulariesDTO = $lessonVocabularies->map(function ($lessonVocabulary) use ($withUserLessonLearning) {
            $lessonVocabularyDTO = $this->getLessonVocabularyDTO($lessonVocabulary);

            if ($withUserLessonLearning) {
                $lessonVocabularyDTO['user_lesson_learning'] = $lessonVocabulary->lessonLearnings->first(); // only has 1 related user lesson learning for each lesson vocabulary
            }

            return $lessonVocabularyDTO;
        });

        return $lessonVocabulariesDTO;
    }

    public function deleteLessonVocabulary($lessonId, $vocabularyId)
    {
        $lessonVocabulary = $this->lessonVocabularyRepository->get($lessonId, $vocabularyId);

        if (!$lessonVocabulary) {
            throw new \Exception('Lesson vocabulary not found');
        }

        // Delete media files
        if ($lessonVocabulary->thumbnail_public_id) {
            Cloudinary::uploadApi()->destroy($lessonVocabulary->thumbnail_public_id);
        }

        if ($lessonVocabulary->pronunciation_audio_public_id) {
            Cloudinary::uploadApi()->destroy($lessonVocabulary->pronunciation_audio_public_id);
        }

        if ($lessonVocabulary->example_audio_public_id) {
            Cloudinary::uploadApi()->destroy($lessonVocabulary->example_audio_public_id);
        }

        return $lessonVocabulary->delete();
    }

    public function updateLessonVocabulary($lessonVocabularyId, $data)
    {
        /**
         * @var LessonVocabulary
         */
        $lessonVocabulary = LessonVocabulary::findOrFail($lessonVocabularyId);

        // Handle thumbnail upload if provided
        if (!empty($data['thumbnail'])) {
            $thumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Vocabulary::THUMBNAIL_FOLDER,
            ]);

            $data['thumbnail'] = $thumbnail['secure_url'];
            $data['thumbnail_public_id'] = $thumbnail['public_id'];

            if ($lessonVocabulary->thumbnail_public_id) {
                Cloudinary::uploadApi()->destroy($lessonVocabulary->thumbnail_public_id);
            }
        }

        // Handle pronunciation audio upload if provided
        if (!empty($data['pronunciation_audio'])) {
            $pronunciationAudio = Cloudinary::uploadApi()->upload($data['pronunciation_audio'], [
                "folder" => Vocabulary::PRONUNCIATION_AUDIO_FOLDER,
                "resource_type" => "auto"
            ]);

            $data['pronunciation_audio'] = $pronunciationAudio['secure_url'];
            $data['pronunciation_audio_public_id'] = $pronunciationAudio['public_id'];

            if ($lessonVocabulary->pronunciation_audio_public_id) {
                Cloudinary::uploadApi()->destroy($lessonVocabulary->pronunciation_audio_public_id);
            }
        }

        // Handle example audio upload if provided
        if (!empty($data['example_audio'])) {
            $exampleAudio = Cloudinary::uploadApi()->upload($data['example_audio'], [
                "folder" => Vocabulary::EXAMPLE_AUDIO_FOLDER,
                "resource_type" => "auto"
            ]);

            $data['example_audio'] = $exampleAudio['secure_url'];
            $data['example_audio_public_id'] = $exampleAudio['public_id'];

            if ($lessonVocabulary->example_audio_public_id) {
                Cloudinary::uploadApi()->destroy($lessonVocabulary->example_audio_public_id);
            }
        }

        $lessonVocabulary->update($data);

        return $lessonVocabulary->refresh();
    }

    /**
     * Map to the lesson vocabulary DTO that has the word information merged with the default vocabulary
     * @param LessonVocabulary $lessonVocabulary
     * @return array
     */
    public static function getLessonVocabularyDTO($lessonVocabulary)
    {
        $defaultVocabulary = $lessonVocabulary->vocabulary;

        return [
            "id" => $lessonVocabulary->id,
            "lesson_id" => $lessonVocabulary->lesson_id,
            "vocabulary_id" => $lessonVocabulary->vocabulary_id,
            "word" => $defaultVocabulary?->word ?? $lessonVocabulary->word,
            "thumbnail" => $lessonVocabulary->thumbnail ?? $defaultVocabulary?->thumbnail,
            "part_of_speech" => $lessonVocabulary->part_of_speech ?? $defaultVocabulary?->part_of_speech,
            "meaning" => $lessonVocabulary->meaning ?? $defaultVocabulary?->meaning,
            "definition" => $lessonVocabulary->definition ?? $defaultVocabulary?->definition,
            "pronunciation" => $lessonVocabulary->pronunciation ?? $defaultVocabulary?->pronunciation,
            "pronunciation_audio" => $lessonVocabulary->pronunciation_audio ?? $defaultVocabulary?->pronunciation_audio,
            "example" => $lessonVocabulary->example ?? $defaultVocabulary?->example,
            "example_meaning" => $lessonVocabulary->example_meaning ?? $defaultVocabulary?->example_meaning,
            "example_audio" => $lessonVocabulary->example_audio ?? $defaultVocabulary?->example_audio,
        ];
    }

    // For AI usage - function calling only
    public function addWordsToFolder($wordFolderId, array $words)
    {
        $loggedInUserId = auth()->id();
        $wordFolder = Lesson::where('user_id', $loggedInUserId)->findOrFail($wordFolderId); // for validation

        $created = [];

        DB::transaction(function () use ($wordFolderId, $words, &$created) {
            // 1. Map to based word array
            $basedWords = array_map(function ($word) {
                $basedWord = new GeneratedWord();
                $basedWord->fromArray($word);

                return $basedWord;
            }, $words);

            // 2. Generate list of words using based words
            $generatedWords = GeminiChatBotService::generateListOfWords($basedWords);

            // 3. Create model lesson vocabularies
            foreach ($generatedWords as $generatedWord) {
                $created[] = LessonVocabulary::create([
                    'lesson_id' => $wordFolderId,
                    'word' => $generatedWord->word,
                    'definition' => $generatedWord->definition,
                    'meaning' => $generatedWord->meaning,
                    'pronunciation' => $generatedWord->pronunciation,
                    'example' => $generatedWord->example,
                    'example_meaning' => $generatedWord->exampleMeaning,
                    'part_of_speech' => $generatedWord->partOfSpeech,
                ]);
            }
        });

        return $created;
    }
}
