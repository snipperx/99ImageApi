<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImagesResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
                'name' =>$this->firstName,
                'lastName' =>$this->lastName,
                'story' =>$this->story,
                'images' => [
                    'imageUrl' => $this->path,
                ], 200
        ];
    }
}
