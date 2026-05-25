<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventOptionItem extends Model
{
    protected $fillable = ['event_option_id', 'recipe_id'];

    public function eventOption() { return $this->belongsTo(EventOption::class); }
    public function recipe()      { return $this->belongsTo(Recipe::class); }
}
