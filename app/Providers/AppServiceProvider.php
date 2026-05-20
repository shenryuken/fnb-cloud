<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
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
        $this->configureBladeDirectives();
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

    protected function configureBladeDirectives(): void
    {
        Blade::directive('curr', function () {
            return "<?php echo e(auth()->user()?->tenant?->currency_symbol ?? 'RM'); ?>";
        });

        Blade::directive('money', function ($expression) {
            return "<?php \$__sym = (string) (auth()->user()?->tenant?->currency_symbol ?? 'RM'); \$__amt = (float) ($expression); echo e(trim(\$__sym) . ' ' . number_format(\$__amt, 2)); ?>";
        });
    }
}
