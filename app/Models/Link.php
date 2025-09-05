<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Link extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "original_url",
        "code"
    ];

    protected static function booted() {
        static::created(function($shorturl){
            $shorturl->code = self::encodeBase62($shorturl->id);
            $shorturl->save();
        });
    }

    protected static function encodeBase62($num): string {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $base = strlen($chars);
        $str = "";

        while($num > 0) {
            $str = $chars[$num % $base] . $str;

            $num = intdiv($num, $base);
        }

        return $str ?: '0';
    }

    public function owner()
    {
        return $this->belongsTo(User::class,"user_id");
    }
}
