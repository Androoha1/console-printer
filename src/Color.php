<?php

declare(strict_types=1);

namespace Posternak\ConsolePrinter;

enum Color: int {
    case SOFT_BLUE = 67;
    case CYAN = 51;
    case YELLOW = 227;
    case GREEN = 2;
    case RED = 1;
    case GRAY = 8;
    case ORANGE = 208;
    case PURPLE = 141;
    case WHITE = 15;
    case BLACK = 0;

    public function foreground(): string {
        return "\033[38;5;{$this->value}m";
    }

    public function background(): string {
        return "\033[48;5;{$this->value}m";
    }

    public static function resetForeground(): string {
        return "\033[39m";
    }

    public static function resetBackground(): string {
        return "\033[49m";
    }
}