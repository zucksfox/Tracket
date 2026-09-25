<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\ServiceProvider;

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
        Builder::defaultStringLength(191);

        // Alias pendek untuk relasi polymorphic. Dengan morph map, kolom
        // activity_logs.subject_type menyimpan "customer" alih-alih
        // "App\Models\Customer", sehingga catatan audit tetap terbaca walau
        // model dipindahkan ke namespace lain nanti.
        Relation::enforceMorphMap([
            'customer' => Customer::class,
            'sparepart' => Sparepart::class,
            'service_order' => ServiceOrder::class,
        ]);
    }
}
