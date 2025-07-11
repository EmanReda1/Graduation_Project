<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';
    protected $primaryKey = 'chat_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'librarian_id',
        'message',
        'date_time', // Laravel سيتعامل مع created_at و updated_at تلقائياً
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_time' => 'datetime',
    ];

    /**
     * Get the student associated with the chat message.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the librarian (user) associated with the chat message.
     */
    public function librarian()
    {

        return $this->belongsTo(User::class, 'librarian_id', 'id');
    }
}
