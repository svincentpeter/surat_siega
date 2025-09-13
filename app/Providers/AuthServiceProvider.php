<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\TugasHeader;
use App\Policies\TugasHeaderPolicy;
use App\Models\KeputusanHeader;
use App\Policies\KeputusanHeaderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TugasHeader::class     => TugasHeaderPolicy::class,
        KeputusanHeader::class => KeputusanHeaderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

// Gate: approver (user_id 2/3) boleh koreksi bila dia yang dituju & status masih draft/pending
Gate::define('edit-surat', function ($user, $tugas) {
    // Approver (2: dekan, 3: wakil dekan), status draft/pending, dan user adalah next_approver
    return in_array((int) $user->peran_id, [2,3], true)
        && in_array((string) $tugas->status_surat, ['draft','pending'], true)
        && (int) $tugas->next_approver === (int) $user->id;
});

Gate::define('manage-kop-surat', function ($user) {
        // izinkan Admin TU
        return in_array((int)$user->peran_id, [1], true);
    });
        //
    }
}
