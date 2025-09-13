<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TugasDetail extends Model
{
    protected $table = 'tugas_detail';
    protected $fillable = ['sub_tugas_id','nama'];

    public function subTugas()
    {
        return $this->belongsTo(SubTugas::class, 'sub_tugas_id');
    }
}
