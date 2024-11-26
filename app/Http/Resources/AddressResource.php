<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'street' => $this->street,
            'address_type' => $this->address_type,
            'is_default' => $this->is_default,
            'province' => $this->province ? $this->province->name : null,  // Kiểm tra nếu province tồn tại
            'district' => $this->district ? $this->district->name : null,  // Kiểm tra nếu district tồn tại
            'ward' => $this->ward ? $this->ward->name : null,  // Kiểm tra nếu ward tồn tại
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
}
