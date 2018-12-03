<?php

namespace App\Policies;

use App\Models\User\User;
use App\App\Models\Organisation;

use Illuminate\Auth\Access\HandlesAuthorization;

class OrganisationPolicy
{
    use HandlesAuthorization;

    public function owner()
    {
    	$user = auth()->user();

        return $user->id === $user->organisation->user_id;
    }

    public function status()
    {
        $user = auth()->user();

    	return $user->organisation->status == true;
    }
}
