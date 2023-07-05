<?php

namespace Caimari\FManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Esto dependerá de donde esté ubicada tu clase User

class FManagerFile extends Model
{
    use HasFactory;

    protected $table = 'fmanager_files';

    protected $fillable = ['name', 'ext', 'size', 'user_id', 'url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function get_size()
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($this->size) - 1) / 3);
        return number_format($this->size / pow(1024, $factor), 2) . @$sz[$factor];
    }
}
