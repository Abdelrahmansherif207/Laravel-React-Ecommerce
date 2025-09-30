<?php

namespace App\Enums;

enum ProductVariationTypeEnum: string
{
    case Select = 'select';
    case Radio = 'radio';
    case Image = 'image';

    public static function labels(): array
    {
        return [
            self::Select->value => __('Select'),
            self::Radio->value => __('Radio'),
            self::Image->value => __('Image'),
        ];
    }
}
