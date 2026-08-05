<?php

namespace App\Modules\Clients\Repositories;

use App\Modules\Clients\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория клиентов.
 *
 * Определяет методы доступа к данным клиентов.
 * Service-слой зависит от этого интерфейса, а не от конкретной реализации (DIP).
 */
interface ClientRepositoryInterface
{
    /**
     * Получить список клиентов с пагинацией и поиском.
     *
     * @param string|null $search  Строка поиска по ФИО
     * @param int         $perPage Количество записей на страницу
     * @param int|null    $membershipTypeId Фильтр по типу абонемента
     *
     * @return LengthAwarePaginator
     */
    public function paginate(?string $search = null, int $perPage = 15, ?int $membershipTypeId = null): LengthAwarePaginator;

    /**
     * Найти клиента по ID.
     *
     * @param int $id ID клиента
     *
     * @return Client|null
     */
    public function findById(int $id): ?Client;

    /**
     * Создать нового клиента.
     *
     * @param array<string, mixed> $data Данные клиента
     *
     * @return Client
     */
    public function create(array $data): Client;

    /**
     * Обновить данные клиента.
     *
     * @param Client               $client Модель клиента
     * @param array<string, mixed> $data   Новые данные
     *
     * @return Client
     */
    public function update(Client $client, array $data): Client;

    /**
     * Удалить клиента.
     *
     * @param Client $client Модель клиента
     *
     * @return bool
     */
    public function delete(Client $client): bool;
}
