<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventHost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

use Auth;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user is the host of an event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @param  \App\Models\EventHost  $eventHost
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function isHost(User $user, Event $event, EventHost $eventHost)
    {
        return $user->userid === $eventHost->userid || $user->admin;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return Auth::check();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Event $event, Invited $invited)
    {
        return ($user->userid == $invited->userid && $event->eventid == $invited->eventid && $invited->status == TRUE) || ($event->isPrivate == FALSE);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return Auth::check() && (!$user->isblocked);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Event $event, EventHost $eventHost)
    {
        return ($user->userid === $eventHost->userid || $user->admin) && (!$user->isblocked);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Event $event, EventHost $eventHost)
    {
        return $user->userid === $eventHost->userid && $event->eventid === $eventHost->eventid || $user->admin;
    }
    

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Event $event, EventHost $eventHost)
    {
        return ($user->userid == $eventHost->userid && $event->eventid == $eventHost->eventid || $user->admin)  && (!$user->isblocked);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Event $event, EventHost $eventHost)
    {
        return $user->userid == $eventHost->userid && $event->eventid == $eventHost->eventid || $user->admin;
    }






}

