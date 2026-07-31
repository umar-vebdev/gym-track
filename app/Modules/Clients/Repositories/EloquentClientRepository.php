<?php

namespace App\Modules\Clients\Repositories;

use App\Modules\Clients\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-реализация репозитория клиентов.
 *
 * Единственное место, где выполняются Eloquent-запросы к таблице `clients`.
 */
class EloquentClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Client::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('client_code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('full_name')->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Client
    {
        return Client::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Client $client): bool
    {
        return $client->delete();
    }
}
