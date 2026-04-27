<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Team;
use App\Models\User;
use App\Policies\ClientPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TaskComment::class, TaskCommentPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
