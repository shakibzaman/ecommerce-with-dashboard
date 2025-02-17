<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'blogs';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
                  'title',
                  'blog_category_id',
                  'image',
                  'description',
                  'source',
                  'blog_seo',
                  'meta_tag',
                  'meta_description'
              ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
    
    /**
     * Get the blogCategory for this model.
     *
     * @return App\Models\BlogCategory
     */
    public function blogCategory()
    {
        return $this->belongsTo('App\Models\BlogCategory','blog_category_id');
    }



}
