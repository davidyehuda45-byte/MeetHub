<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(?User $user, Meeting $meeting): bool
    {
        return true;
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $user->id === $meeting->host_id;
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $user->id === $meeting->host_id;
    }

    public function manageNotes(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $user->id === $meeting->host_id;
    }

    public function exportAttendance(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $user->id === $meeting->host_id;
    }
}

