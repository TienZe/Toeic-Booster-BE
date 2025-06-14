<?php

namespace App\Helpers;

final class MarkdownHelper
{
    /**
     * Removes the JSON markdown wrapper (```json ... ```) from a string.
     *
     * @param string $rawText The raw string from the AI, which may contain a markdown wrapper.
     * @return string The cleaned string, ready for JSON decoding.
     */
    public static function cleanJsonMarkdown(string $rawText): string
    {
        // This pattern looks for a string that starts with ```json,
        // followed by any characters (including newlines), and ends with ```.
        // The [\s\S]*? part captures everything in between, non-greedily.
        $pattern = '/^```json\s*([\s\S]*?)\s*```$/';

        // Trim the input string to remove leading/trailing whitespace
        $trimmedText = trim($rawText);

        if (preg_match($pattern, $trimmedText, $matches)) {
            // If a match is found, return the captured group (the JSON content).
            return $matches[1];
        }

        // If no markdown wrapper is found, return the original trimmed string.
        return $trimmedText;
    }
}
