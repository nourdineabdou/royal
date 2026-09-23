<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = ['name', 'image', 'price', 'category_id', 'is_catering'];

    protected $casts = ['is_catering' => 'boolean'];

    protected $appends = ['image_url'];

    /**
     * Retourne l'URL complète de l'image :
     * - si le champ contient déjà une URL https://, on l'utilise tel quel
     * - sinon on construit l'URL storage locale
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function accompaniments()
    {
        return $this->belongsToMany(Accompaniment::class, 'meal_accompaniments');
    }
}
