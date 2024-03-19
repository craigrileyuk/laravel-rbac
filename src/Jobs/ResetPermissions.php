<?php

namespace BinaryCats\Rbac\Jobs;

use BinaryCats\Rbac\DiscoverAbilities;
use BackedEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class ResetPermissions
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $guard;
    protected string $abilityPath;

    /**
     * @param string|null $guard
     */
    public function __construct(string $guard = null)
    {
        $this->abilityPath = config('rbac.path');
        $this->guard = $guard ?? config('auth.defaults.guard');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->permissions()
            ->each(fn (BackedEnum $ability) => Artisan::call('permission:create-permission', [
                'name' => $ability->value,
                'guard' => $this->guard,
            ]));
    }

    /**
     * @return \Illuminate\Support\Collection<int, \BackedEnum>
     */
    protected function permissions(): Collection
    {
        return DiscoverAbilities::within(
            abilitiesPath: $this->abilityPath,
            basePath: app()->basePath()
        );
    }
}