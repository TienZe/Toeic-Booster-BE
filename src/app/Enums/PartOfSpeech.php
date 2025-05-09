<?php

namespace App\Enums;

enum PartOfSpeech: string
{
    case NOUN = 'noun';
    case VERB = 'verb';
    case ADJECTIVE = 'adjective';
    case ADVERB = 'adverb';
    case PRONOUN = 'pronoun';
    case PREPOSITION = 'preposition';
    case CONJUNCTION = 'conjunction';
    case INTERJECTION = 'interjection';
    case DETERMINER = 'determiner';
    case PROPER = 'proper';

    /**
     * Get all enum values as array
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum names as array
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
