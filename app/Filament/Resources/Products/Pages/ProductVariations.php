<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Model;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variations';

    protected static string|\BackedEnum|null $navigationIcon = 'css-list';

    public static function getNavigationLabel(): string
    {
        return 'Variations';
    }

    public function form(Schema $schema): Schema
    {
        $types = $this->record->variationTypes;
        $fields = [];

        foreach ($types as $type) {
            $fields[] = TextInput::make('variation_type_' . $type->id . '.name')
                ->label($type->name)
                ->disabled()
                ->columnSpan(1);
        }

        return $schema->components([
            Repeater::make('variations')
                ->label('Product Variations')
                ->addable(false)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Section::make('Variation Options')
                        ->schema($fields)
                        ->columns(count($types)) // put options side by side
                        ->compact(),             // tighter layout

                    Section::make('Stock & Pricing')
                        ->schema([
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->placeholder('Enter stock')
                                ->columnSpan(1),

                            TextInput::make('price')
                                ->label('Price')
                                ->numeric()
                                ->placeholder('Enter price')
                                ->prefix('$')
                                ->columnSpan(1),
                        ])
                        ->columns(2),
                ])
                ->grid(1) // each variation in its own row
                ->columnSpanFull(),
        ]);
    }


    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting(
            $this->record->variationTypes,
            $variations
        );
        return $data;
    }

    public function mergeCartesianWithExisting($variationTypes, $existingData): array
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;
        $cartesianProduct = $this->cartesianProduct(
            $variationTypes,
            $defaultQuantity,
            $defaultPrice
        );
        $mergedResult = [];

        foreach ($cartesianProduct as $product) {
            $optionIds = collect($product)
                ->filter(fn($value, $key) => str_starts_with($key, 'variation_type_'))
                ->map(fn($option) => $option['id'])
                ->values()
                ->toArray();

            $optionLabels = collect($product)
                ->filter(fn($value, $key) => str_starts_with($key, 'variation_type_'))
                ->map(fn($option) => $option['label'])
                ->implode(' / ');

            $match = array_filter($existingData, function ($existingOption) use ($optionIds) {
                $existingIds = json_decode($existingOption['variation_type_option_ids'], true);
                return $existingIds === $optionIds;
            });


            if (!empty($match)) {
                $existingEntry = reset($match);
                $product['id'] = $existingEntry['id'];
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            } else {
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;
            }
            $product['name'] = $optionLabels;
            $product['variation_type_option_ids'] = $optionIds;
            $mergedResult[] = $product;
        }
        return $mergedResult;
    }

    public function cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice): array
    {
        $result = [[]];

        foreach ($variationTypes as $index => $variationType) {
            $temp = [];

            foreach ($variationType->options as $option) {
                foreach ($result as $combination) {
                    $newCombination = $combination + [
                            'variation_type_' . ($variationType->id) => [
                                'id' => $option->id,
                                'name' => $option->name,
                                'label' => $option->name,
                            ]
                        ];
                    $temp[] = $newCombination;
                }
            }
            $result = $temp;

            foreach ($result as &$combination) {
                if (count($combination) === count($variationTypes)) {
                    $combination['quantity'] = $defaultQuantity;
                    $combination['price'] = $defaultPrice;
                }
            }
        }
        return $result;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        $formattedData = [];

        foreach ($data['variations'] as $option) {
            // collect option IDs for this variation
            $variationTypeOptionsIds = collect($this->record->variationTypes)
                ->map(fn($variationType) => $option['variation_type_' . $variationType->id]['id'])
                ->toArray();

            $quantity = $option['quantity'];
            $price = $option['price'];

            $name = collect($this->record->variationTypes)
                ->map(fn($variationType) => $option['variation_type_' . $variationType->id]['label'])
                ->implode(' / ');

            $row = [
                'name' => $name,
                'variation_type_option_ids' => json_encode($variationTypeOptionsIds),
                'quantity' => $quantity,
                'price' => $price,
            ];

            // only set id if exists (for updates)
            if (isset($option['id'])) {
                $row['id'] = $option['id'];
            }

            $formattedData[] = $row;
        }

        $data['variations'] = $formattedData;
        return $data;
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variations = $data['variations'];
        unset($data['variations']);

        // Map variations: include id only if it exists
        $variations = collect($variations)->map(function ($variation) {
            $row = [
                'name' => $variation['name'],
                'variation_type_option_ids' => json_encode($variation['variation_type_option_ids']),
                'quantity' => $variation['quantity'],
                'price' => $variation['price'],
            ];

            if (isset($variation['id'])) {
                $row['id'] = $variation['id']; // existing variation
            }

            return $row;
        })->toArray();

        // Delete old variations
        $record->variations()->delete();

        // Upsert variations with id (existing) and without id (new)
        $record->variations()->upsert(
            $variations,
            ['id'], // key: id for existing variations
            ['variation_type_option_ids', 'quantity', 'price']
        );

        return $record;
    }
}
