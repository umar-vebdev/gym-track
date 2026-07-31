<?php

namespace App\Modules\Clients\Services;

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Repositories\ClientRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Сервис бизнес-логики модуля клиентов.
 *
 * Содержит бизнес-логику работы с клиентами.
 * Не знает про Eloquent — обращается только к интерфейсу репозитория.
 */
class ClientService
{
    /**
     * @param ClientRepositoryInterface $repository
     */
    public function __construct(
        private readonly ClientRepositoryInterface $repository
    ) {}

    /**
     * Получить список клиентов с пагинацией и опциональным поиском по ФИО.
     *
     * @param string|null $search  Строка поиска
     * @param int         $perPage Кол-во на страницу
     *
     * @return LengthAwarePaginator
     */
    public function list(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $perPage);
    }

    /**
     * Найти клиента по ID.
     *
     * @param int $id ID клиента
     *
     * @return Client|null
     */
    public function findById(int $id): ?Client
    {
        return $this->repository->findById($id);
    }

    /**
     * Создать нового клиента.
     *
     * @param array<string, mixed> $data Валидированные данные
     *
     * @return Client
     */
    public function create(array $data): Client
    {
        return $this->repository->create($data);
    }

    /**
     * Обновить данные клиента.
     *
     * @param Client               $client Модель клиента
     * @param array<string, mixed> $data   Новые данные
     *
     * @return Client
     */
    public function update(Client $client, array $data): Client
    {
        return $this->repository->update($client, $data);
    }

    /**
     * Удалить клиента.
     *
     * @param Client $client Модель клиента
     *
     * @return bool
     */
    public function delete(Client $client): bool
    {
        return $this->repository->delete($client);
    }
}
