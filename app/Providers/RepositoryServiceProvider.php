<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Провайдер привязки интерфейсов репозиториев к их Eloquent-реализациям.
 *
 * Обеспечивает инверсию зависимостей (Dependency Inversion Principle):
 * Service-слой зависит от абстракции (Interface), а не от конкретного репозитория.
 * Это позволяет подменять реализацию (например, для тестов) без изменения бизнес-логики.
 *
 * Биндинги раскомментируются по мере создания модулей.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Регистрация биндингов Interface → Repository.
     *
     * @return void
     */
    public function register(): void
    {
        // Clients
        $this->app->bind(
            \App\Modules\Clients\Repositories\ClientRepositoryInterface::class,
            \App\Modules\Clients\Repositories\EloquentClientRepository::class
        );

        // MembershipTypes
        $this->app->bind(
            \App\Modules\Memberships\Repositories\MembershipTypeRepositoryInterface::class,
            \App\Modules\Memberships\Repositories\EloquentMembershipTypeRepository::class
        );

        // MembershipPurchases
        $this->app->bind(
            \App\Modules\Memberships\Repositories\MembershipPurchaseRepositoryInterface::class,
            \App\Modules\Memberships\Repositories\EloquentMembershipPurchaseRepository::class
        );

        // Visits
        // $this->app->bind(
        //     \App\Modules\Visits\Repositories\VisitRepositoryInterface::class,
        //     \App\Modules\Visits\Repositories\EloquentVisitRepository::class
        // );
    }
}
