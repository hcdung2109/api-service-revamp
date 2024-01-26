<?php


namespace Digisource\Core\Entities;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Add this line to import the 'Str' class

class BaseModel extends Model
{

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'write_date';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
            $model->write_date = now(); // Set default value for updated_at
            $model->create_date = now(); // Set default value for created_at
        });

        static::updating(function ($model) {
            $model->write_date = now(); // Set default value for updated_at
        });
    }
}
