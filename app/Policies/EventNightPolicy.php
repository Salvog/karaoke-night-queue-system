<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\EventNight;

class EventNightPolicy
{
    public function delete(AdminUser $user, EventNight $eventNight): bool
    {
        return $user->isAdmin();
    }
}
