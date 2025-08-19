<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venues extends Model
{
    use HasFactory;
    protected $table = 'venues';
    protected $primaryKey = 'id';
    protected $appends = ['image_url'];
    protected $guarded = [];
    // Tambahkan semua field yang bisa diisi
    protected $fillable = [
    'name',
    'category_id',
    'address',
    'capacity',
    'price',
    'city_id',
    'status',
];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function images()
    {
        return $this->hasMany(VenueImage::class, 'venue_id');
    }
{
    return $this->hasMany(VenueImage::class, 'venue_id'); // pastikan 'venue_id' sesuai kolom
}

public function getImageUrlAttribute() {
    $image = $this->images->first();
    return $image ? asset('storage/' . $image->image_url) : null;
}
    public function primaryImage()
    {
        return $this->hasOne(VenueImage::class, 'venue_id')->where('is_primary', true);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_venue', 'venue_id', 'facility_id');
    }

    public function fnbMenus()
    {
        return $this->hasMany(Fnb_menu::class);
    }


}
