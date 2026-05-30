<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public representation of an authenticated user. Sensitive attributes
 * (password hash, remember token) are never exposed.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'role' => $this->whenLoaded('role', fn (): array => [
                'slug' => $this->role->slug,
                'name' => $this->role->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

