<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    private function isAdmin(User $user) {
        return $user->admin;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, User $model)
    {
        return ($user->userid === $model->userid || $this->isAdmin($user));  //só pode ver o model User se for o próprio ou se for admin
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $this->isAdmin($user);  //só pode criar um novo User (sem ser por register) se for admin  //mudar isto como no event?
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        return $user->userid === $model->userid || $this->isAdmin($user);  //só pode atualizar o model User se for o próprio ou se for admin
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        return $user->userid === $model->userid || $this->isAdmin($user);  //só pode apagar o model User se for o próprio ou se for admin
    }

    public function changeBlock(User $user){
        return $this->isAdmin($user); //so admins podem alterar block status e nao podem bloquear a si mesmos
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }


    
    public function attend(User $user, Event $event)
    {
        //check if user is already attending and event is not private
        $ticket = Ticket::where('userid', $user->userid)->where('eventid', $event->eventid)->first();

        return is_null($ticket) && !($event->isprivate);
    }


    public function leave(User $user, User $model)
    {
        //check if user is attending event and event is not private
        $ticket = Ticket::where('userid', $user->userid)->where('eventid', $event->eventid)->first();

        return !is_null($ticket) && !($event->isprivate);
    }
}
