<?php

declare(strict_types=1);

namespace Posternak\ConsolePrinter;

class Printer {
    /**
     * @param list<Color> $colors
     */
    public function print(string $text, array $colors = [], ?Color $background = null): void {
        $this->validateInput($text, $colors);

        $output = '';

        if ($background !== null) {
            $output .= $background->background();
        }

        $output .= $this->coloredText($text, $colors);

        if ($background !== null) {
            $output .= "\033[49m";  // Reset background only
        }

        echo $output;
    }

    /**
     * @param list<Color> $colors
     */
    public function println(string $text, array $colors = [], ?Color $background = null): void {
        $this->print($text, $colors, $background);
        $this->newLine();
    }

    public function newLine(): void {
        echo "\n";
    }

    /**
     * @param list<Color> $colors
     */
    private function validateInput(string $text, array $colors): void {
        $groupCount = $this->countCapturingGroups($text);

        if ($groupCount > 0 && empty($colors)) {
            throw new \InvalidArgumentException('Capturing groups are defined but no colors provided');
        }

        if (count($colors) > $groupCount && !($groupCount === 0 && count($colors) === 1)) {
            throw new \InvalidArgumentException(
                sprintf('Too many colors provided: %d colors for %d capturing groups', count($colors), $groupCount)
            );
        }
    }

    /**
     * @param list<Color> $colors
     */
    private function coloredText(string $text, array $colors): string {
        $groupCount = $this->countCapturingGroups($text);

        if ($groupCount === 0) {
            return $colors[0]->foreground() . $text . Color::resetForeground();
        }

        $mapping = $this->symbolsToColorsMapping($text);
        $textWithoutBraces = str_replace(['{', '}'], '', $text);

        return $this->applyColorsToText($textWithoutBraces, $mapping, $colors);
    }

    /**
     * @param list<int> $mapping
     * @param list<Color> $colors
     */
    private function applyColorsToText(string $text, array $mapping, array $colors): string {
        $result = '';
        $currentColorIndex = -1;

        for ($i = 0; $i < strlen($text); $i++) {
            $groupIndex = $mapping[$i];

            if ($groupIndex !== $currentColorIndex) {
                if ($currentColorIndex > 0) {
                    $result .= Color::resetForeground();
                }

                if ($groupIndex > 0) {
                    $colorIndex = min($groupIndex - 1, count($colors) - 1);
                    $result .= $colors[$colorIndex]->foreground();
                }

                $currentColorIndex = $groupIndex;
            }

            $result .= $text[$i];
        }

        if ($currentColorIndex > 0) {
            $result .= Color::resetForeground();
        }

        return $result;
    }

    private function countCapturingGroups(string $text): int {
        return substr_count($text, '{');
    }

    /**
     * @return list<int>
     */
    private function symbolsToColorsMapping(string $text): array {
        $map = [];
        $groupCounter = 0;
        $groupStack = [0];

        for ($i = 0; $i < strlen($text); $i++) {
            if ($text[$i] === '{') {
                $groupCounter++;
                $groupStack[] = $groupCounter;
            } elseif ($text[$i] === '}') {
                array_pop($groupStack);
            } else {
                $map[] = $groupStack[count($groupStack) - 1];
            }
        }

        return $map;
    }
}