<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\PaginatedList;
use App\Models\Vocabulary;
use App\Repositories\VocabularyRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class VocabularyService
{
    private VocabularyRepository $vocabularyRepository;

    public function __construct(VocabularyRepository $vocabularyRepository)
    {
        $this->vocabularyRepository = $vocabularyRepository;
    }

    public function getVocabularyById(int $id): Vocabulary
    {
        return $this->vocabularyRepository->find($id);
    }

    public function getVocabularies(array $options): PaginatedList
    {
        return $this->vocabularyRepository->get($options);
    }

    /**
     * Create a new vocabulary
     *
     * @param array $data
     * @return Vocabulary
     */
    public function createVocabulary(array $data): Vocabulary
    {
        // Handle thumbnail upload if provided
        if (!empty($data['thumbnail'])) {
            $thumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Vocabulary::THUMBNAIL_FOLDER,
            ]);

            $data['thumbnail'] = $thumbnail['secure_url'];
            $data['thumbnail_public_id'] = $thumbnail['public_id'];
        }

        // Handle pronunciation audio upload if provided
        if (!empty($data['pronunciation_audio'])) {
            $pronunciationAudio = Cloudinary::uploadApi()->upload($data['pronunciation_audio'], [
                "folder" => Vocabulary::PRONUNCIATION_AUDIO_FOLDER,
                "resource_type" => "auto"
            ]);

            $data['pronunciation_audio'] = $pronunciationAudio['secure_url'];
            $data['pronunciation_audio_public_id'] = $pronunciationAudio['public_id'];
        }

        // Handle example audio upload if provided
        if (!empty($data['example_audio'])) {
            $exampleAudio = Cloudinary::uploadApi()->upload($data['example_audio'], [
                "folder" => Vocabulary::EXAMPLE_AUDIO_FOLDER,
                "resource_type" => "auto"
            ]);

            $data['example_audio'] = $exampleAudio['secure_url'];
            $data['example_audio_public_id'] = $exampleAudio['public_id'];
        }

        return $this->vocabularyRepository->create($data);
    }

    public function updateVocabulary($id, array $data)
    {
        $vocabulary = Vocabulary::findOrFail($id);

        // Handle thumbnail upload if provided
        if (!empty($data['thumbnail'])) {
            $thumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Vocabulary::THUMBNAIL_FOLDER,
            ]);

            $data['thumbnail'] = $thumbnail['secure_url'];
            $data['thumbnail_public_id'] = $thumbnail['public_id'];

            // Delete the old thumbnail if it exists
            if ($vocabulary->thumbnail_public_id) {
                Cloudinary::uploadApi()->destroy($vocabulary->thumbnail_public_id);
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

            if ($vocabulary->pronunciation_audio_public_id) {
                Cloudinary::uploadApi()->destroy($vocabulary->pronunciation_audio_public_id);
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

            if ($vocabulary->example_audio_public_id) {
                Cloudinary::uploadApi()->destroy($vocabulary->example_audio_public_id);
            }
        }

        return $this->vocabularyRepository->update($id, $data);
    }

    public function deleteVocabulary(int $id): bool
    {
        $vocabulary = $this->vocabularyRepository->find($id);

        if ($vocabulary->thumbnail_public_id) {
            Cloudinary::uploadApi()->destroy($vocabulary->thumbnail_public_id);
        }

        if ($vocabulary->pronunciation_audio_public_id) {
            Cloudinary::uploadApi()->destroy($vocabulary->pronunciation_audio_public_id);
        }

        if ($vocabulary->example_audio_public_id) {
            Cloudinary::uploadApi()->destroy($vocabulary->example_audio_public_id);
        }

        return $vocabulary->delete();
    }
}
