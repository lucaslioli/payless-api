<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'price', 'sold_out', 'user_id'];

    /**
     * Returns the user that owns the product.
     * 
     * @return App\User
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
}
