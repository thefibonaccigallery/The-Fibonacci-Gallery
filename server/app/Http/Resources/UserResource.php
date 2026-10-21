<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'uuid'    => $this->uuid,
            'name'    => $this->name,
            'email'   => $this->email,
            'country'       => $this->country,
            'country_code'  => $this->country_code,
            'phone_number'  => $this->phone_number,
            'role'          => $this->getRoleNames()->first(),
            'referral_code' => $this->referral_code,
        ];
    }
}
