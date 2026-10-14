<?php
interface AiProviderInterface {
    /**
     * Analyze user input and return a formatted string
     * @param string $prompt
     * @return string
     */
    public function analyzeSymptoms(string $prompt): string;
}
