<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 */
class Category extends Model
{
    public function parent() : BelongsTo {
        return $this->belongsTo(Category::class , 'parent_id');
    }
}
