<?php

namespace App\Entities;

class GeneratedWord extends Entity
{
    public $word;
    public $definition;
    public $meaning;

    public $pronunciation;
    public $example;
    public $exampleMeaning;
    public $partOfSpeech;

    public function fromArray(array $data)
    {
        $this->word = $data['word'];
        $this->definition = $data['definition'] ?? null;
        $this->meaning = $data['meaning'] ?? null;
        $this->pronunciation = $data['pronunciation'] ?? null;
        $this->example = $data['example'] ?? null;
        $this->exampleMeaning = $data['exampleMeaning'] ?? ($data['example_meaning'] ?? null);
        $this->partOfSpeech = $data['partOfSpeech'] ?? ($data['part_of_speech'] ?? null);
    }
}