<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Str;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->live(onBlur: true)
                ->required()
                ->afterStateUpdated(function (string $operation, $state, callable $set) {
                    $set('slug', Str::slug($state));
                }),

            TextInput::make('slug')
                ->label('Slug')
                ->required(),

            Checkbox::make('active')
                ->label('Active'),
        ]);
    }
}
