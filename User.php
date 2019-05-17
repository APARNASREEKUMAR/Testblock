<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Kyslik\ColumnSortable\Sortable;
use App\UserAdtnlInfo;
use Response;
use PragmaRX\Recovery;

class User extends Authenticatable
{
    use Notifiable,Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
       protected $fillable = [
        'name', 'email', 'password', 'google2fa_secret','google_auth'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
  protected $hidden = [
        'password', 'remember_token', 'google2fa_secret','google_auth'
    ];
     public function setGoogle2faSecretAttribute($value)
    {
         $this->attributes['google2fa_secret'] = encrypt($value);
    }
     public function getGoogle2faSecretAttribute($value)
    {
        return decrypt($value);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
     protected $sortable = ['id',
        'name',
        'email',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function detail()
    {
        return $this->hasOne(UserAdtnlInfo::class,'id','id');
    }
    public function log()
    {
        return $this->hasOne(AuthLogs::class,'id','id');
    }
    public static function recoverycodes()
    {
        $recovery = new \PragmaRX\Recovery\Recovery();
        $rec=$recovery->toArray();
        return $rec;
        $filename = "recoverycodes.csv";
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('Code'));
        for($i=0;$i<sizeof($rec);$i++){
        fputcsv($handle, array($rec[$i]));
        }
        fclose($handle);
        $headers = array(
        'Content-Type' => 'text/csv',
        );
        Response::download($filename, 'recoverycodes.csv', $headers);
            
    }
}
