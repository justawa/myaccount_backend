<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $guarded = [];

    public function profileEdits()
    {
        $this->hasMany('App/UserProfileEdits', 'row_id');
    }

    public function latestByColumn($column)
    {
        return $this->profileEdits()->where('columns', $column)->orderBy('applicable_date', 'desc')->where('applicable_date', '<', Carbon::now()->format('Y-m-d'))->first();
    }
}
