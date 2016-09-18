<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactModel extends Model
{
    protected $table = 'contacts';

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'custom1',
        'custom2',
        'custom3',
        'custom4',
        'custom5',
        'external_id'
    ];
}
