<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ProductVariationTypes extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variation Types';

    protected static string|\BackedEnum|null $navigationIcon = 'ionicon-options-outline';

    public static function getNavigationLabel(): string
    {
        return 'Variation Types';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Repeater::make('variationTypes')
                ->relationship()
                ->collapsible()
                ->defaultItems(1)
                ->label(false)
                ->addActionLabel('Add new Variation Type')
                ->columnSpan(2)
                ->schema([
                    TextInput::make('name')
                        ->required(),
                    Select::make('type')
                        ->options(ProductVariationTypeEnum::labels())
                        ->required(),

                    Repeater::make('options')
                        ->relationship()
                        ->collapsible()
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->columnSpan(2),

                            SpatieMediaLibraryFileUpload::make('images')
                                ->multiple()
                                ->openable()
                                ->panelLayout('grid')
                                ->collection('images')
                                ->reorderable()
                                ->appendFiles()
                                ->preserveFilenames()
                                ->columnSpan(3)

                        ])
                        ->columnSpan(2)
                ])
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
