<?php

namespace AspireRESTAPI\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for payment_schedule Table
 *
 * @author          Riyaz Patwegar <riyaz_patwegar@yahoo.com>
 * @since           AUg 27, 2022
 * @version         1.0
 */
class PaymentSchedule extends Model
{
    /** @var string Table Name */
    protected $table = 'payment_schedule';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = false;

    protected $hidden=['created_at','updated_at'];    
}