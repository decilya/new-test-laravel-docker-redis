<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Application\User\UserView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс для сериализации DTO UserView в JSON.
 *
 * @mixin UserView
 */
final class UserResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'avatar_url' => $this->avatarUrl ? asset('storage/'.$this->avatarUrl) : null,
            'created_at' => $this->createdAt,
        ];
    }
}
