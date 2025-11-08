<?php

namespace App\Enum;

enum ImplementationStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case PARTIALLY_DONE = 'partially_done';
    case COMPLETED = 'completed';
    case ABANDONED = 'abandoned';
    case DELAYED = 'delayed';

    public function getLabel(): string
    {
        return match($this) {
            self::NOT_STARTED => 'Pas commencé',
            self::IN_PROGRESS => 'En cours',
            self::PARTIALLY_DONE => 'Partiellement réalisé',
            self::COMPLETED => 'Terminé',
            self::ABANDONED => 'Abandonné',
            self::DELAYED => 'Reporté',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::NOT_STARTED => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::PARTIALLY_DONE => 'warning',
            self::COMPLETED => 'success',
            self::ABANDONED => 'danger',
            self::DELAYED => 'info',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::NOT_STARTED => 'fas fa-clock',
            self::IN_PROGRESS => 'fas fa-spinner',
            self::PARTIALLY_DONE => 'fas fa-tasks',
            self::COMPLETED => 'fas fa-check-circle',
            self::ABANDONED => 'fas fa-times-circle',
            self::DELAYED => 'fas fa-pause-circle',
        };
    }

    public function getProgressPercentage(): int
    {
        return match($this) {
            self::NOT_STARTED => 0,
            self::IN_PROGRESS => 50,
            self::PARTIALLY_DONE => 75,
            self::COMPLETED => 100,
            self::ABANDONED => 0,
            self::DELAYED => 25,
        };
    }

    public static function getChoices(): array
    {
        return [
            'Pas commencé' => self::NOT_STARTED->value,
            'En cours' => self::IN_PROGRESS->value,
            'Partiellement réalisé' => self::PARTIALLY_DONE->value,
            'Terminé' => self::COMPLETED->value,
            'Abandonné' => self::ABANDONED->value,
            'Reporté' => self::DELAYED->value,
        ];
    }

    public static function fromValue(string $value): ?self
    {
        return match($value) {
            'not_started' => self::NOT_STARTED,
            'in_progress' => self::IN_PROGRESS,
            'partially_done' => self::PARTIALLY_DONE,
            'completed' => self::COMPLETED,
            'abandoned' => self::ABANDONED,
            'delayed' => self::DELAYED,
            default => null,
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [self::IN_PROGRESS, self::PARTIALLY_DONE, self::COMPLETED]);
    }

    public function isNegative(): bool
    {
        return in_array($this, [self::ABANDONED]);
    }

    public function isNeutral(): bool
    {
        return in_array($this, [self::NOT_STARTED, self::DELAYED]);
    }
}
