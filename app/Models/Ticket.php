<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    //
    protected $filalble = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'completedAt',
    ];

    public function user(){
        return $this->belongsTo(User::class);

    }

    public function ticketReplies(){
        return $this->hasMany(TicketReply::class);
    }
}
