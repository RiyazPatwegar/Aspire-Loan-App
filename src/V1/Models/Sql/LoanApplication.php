<?php

namespace AspireRESTAPI\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for Customer Table
 *
 * @author          Riyaz Patwegar <riyaz_patwegar@yahoo.com>
 * @since           Aug 27, 2022
 * @version         1.0
 */
class LoanApplication extends Model
{
    /** @var string Table Name */
    protected $table = 'loan_application';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = false;

    protected $hidden=['created_at','updated_at'];    

    /**
     * Get the loan customer payment schedule details.
     */
    public function Schedule()
    {
        return $this->hasMany(PaymentSchedule::class, 'application_id', 'id');
    }
}