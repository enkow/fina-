<?php

namespace App\Searchers\ReservationSlot;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class ReservationClubNoteSearcher implements Searcher
{
    public static function handle(Builder $query, string $tableName): Builder
    {
        return $query->whereHas('reservation', function ($query) use($tableName) {
            $query->where('club_note', 'like', '%'.request()->get('searcher')[$tableName].'%');
        });
    }
}
