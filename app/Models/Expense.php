<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{

    use SoftDeletes;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expenses';

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
        'category_id',
        'description',
        'amount',
        'created_by',
        'received_by',
        'updated_by',
        'payment_date',
        'Invoice'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Get the Expensecategory for this model.
     *
     * @return App\Models\Expensecategory
     */
    public function Expensecategory()
    {
        return $this->belongsTo('App\Models\Expensecategory', 'category_id');
    }

    /**
     * Get the creator for this model.
     *
     * @return App\User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function updator()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the receivedBy for this model.
     *
     * @return App\User
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
