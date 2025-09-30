<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;


class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->live(true)
                    ->required()
                    ->afterStateUpdated(
                        function (string $operation, $state, callable $set) {
                            return $set("slug", Str::slug($state));
                        }
                    ),
                TextInput::make('slug')
                    ->required(),

                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label(__('Department'))
                    ->preload()
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('category_id', null);
                    }),

                Select::make('category_id')
                    ->relationship(
                        'category',
                        'name',
                        modifyQueryUsing: function (Builder $query, callable $get) {
                            $departmentId = $get('department_id');
                            if ($departmentId) {
                                $query->where('department_id', $departmentId);
                            }
                        })
                    ->label(__('Category'))
                    ->preload()
                    ->searchable()
                    ->required(),

                RichEditor::make('description')
                    ->label(__('Description'))
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                        'table',
                    ])
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->required()
                    ->numeric(),

                TextInput::make('quantity')
                    ->integer(),

                Select::make('status')
                    ->options(ProductStatusEnum::labels())
                    ->default(ProductStatusEnum::Draft->value)
                    ->required(),
            ]);
    }
}
