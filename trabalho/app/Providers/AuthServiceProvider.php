<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
use App\Models\User;
use App\Policies\UserPolicy;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
      'App\Models\Card' => 'App\Policies\CardPolicy',
      'App\Models\Item' => 'App\Policies\ItemPolicy',
      'App\Models\Event' => 'App\Policies\EventPolicy',
      'App\Models\Comment' => 'App\Policies\CommentPolicy',

      Event::Class => EventPolicy::Class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
