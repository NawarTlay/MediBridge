<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Medicin;
use App\Models\Category;
use Auth;
class CategoryResourcePhar extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = Medicin::where('user_id', $request['store_id'])->where('category_id', $this->id)->get();

        if(count($data) > 0){
            return[
                'category' => $this->name,
                'medicins' => MedicinResource::collection($data)
            ];
        }
        else{
            return[
                'error' => true,
                'message' => $this->name." لا يوجد أدوية من صنف",
            ];    
        }
    }
}
