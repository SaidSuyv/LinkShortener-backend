<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Link extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "user_id",
        "original_url",
        "code",
        "expiration_at",
    ];

    protected static function booted() {
        static::created(function($shorturl){
            $shorturl->updateQuietly([
                'code' => self::encodeBase62($shorturl->id)
            ]);
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
