<?php

namespace App\Enum;

enum CommitmentStatus: string
{
    case ACCEPTED = 'accepted';
    case REFUSED = 'refused';

    public function getLabel(): string
    {
        return match($this) {
            self::ACCEPTED => 'Accepté',
            self::REFUSED => 'Refusé',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ACCEPTED => 'success',
            self::REFUSED => 'danger',
        };
    }

    public static function getChoices(): array
    {
        return [
            'Accepté' => self::ACCEPTED->value,
            'Refusé' => self::REFUSED->value,
        ];
    }

    public static function fromValue(string $value): ?self
    {
        return match($value) {
            'accepted' => self::ACCEPTED,
            'refused' => self::REFUSED,
            default => null,
        };
    }
}
