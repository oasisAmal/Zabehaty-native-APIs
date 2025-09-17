<?php

namespace Modules\Users\App\Models;

use Illuminate\Support\Facades\DB;

trait UserAttributes
{
    function getFullnameAttribute()
    {
        return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    }

    function fullmobile($number = null)
    {
        $number = ($number) ?? $this->mobile;
        if ($number == '') return '';
        if (substr($number, 0, 2) == '05')
            $number = '971' . substr($number, 1);

        elseif (substr($number, 0, 5) == '00971')
            $number = substr($number, 2);

        elseif (strlen($number) == 9 && substr($number, 0, 1) == '5')
            $number = '971' . $number;

        elseif (substr($number, 0, 3) == '010')
            $number = '2' . $number;
        elseif (substr($number, 0, 1) == '0')
            $number = $this->country_code . substr($number, 1);
        else
            $number = $this->country_code . $number;

        return $number;
    }

    function getMainMobileAttribute()
    {
        $mobile = $this->mobile;
        $mobile = explode(',', $mobile)[0];
        if (substr($mobile, 0, 2) == '05')
            $mobile = ltrim($mobile, '0');
        return $mobile;
    }


    function getExtraMobilesAttribute()
    {
        if ($this->extra_mobiles == '') return [];
        $mobiles = explode(',', $this->extra_mobiles);
        if (empty($mobiles))
            return $mobiles;
        $return = [];
        foreach ($mobiles as $mobile) {
            $return[] = $this->fullmobile($mobile);
        }
        return $return;
    }

    public function scopeSearchMobile($query, $mobile)
    {
        $query->where(function ($q) use ($mobile) {
            $q->where('mobile', $mobile)
                ->orWhere('mobile', '0' . $mobile)
                ->orWhere('mobile', substr($mobile, 1));
        });
    }

    public function scopeName($query, $name)
    {
        $query->where(function ($q) use ($name) {
            $q->where(DB::raw('concat(first_name," ",last_name)'), 'like', '%' . $name . '%');
            $q->orWhere(DB::raw('concat(first_name," ",middle_name," ",last_name)'), 'like', '%' . $name . '%');
        });
    }

    /**
     * Get image.
     *
     * @return url
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // return config('integrations-credentials.s3.url') . '/uploads/' . $this->image;
            return $this->image;
        }
        return url('images/avatar_user.png');
    }
}
