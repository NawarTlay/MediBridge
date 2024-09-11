<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Favourit;
use Auth;
class MedicinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $f = 1;
        $fav=Favourit::where('medicin_id',$this->id)->where('user_id',Auth::user()->id)->first();
        if($fav == null) $f = 0;

        return [
            'id' => $this->id,
            'sc_name' => $this->sc_name,
            'trad_name' => $this->trad_name,
            'quantity' => $this->quantity,
            'category' => $this->category->name,
            'price' => $this->price,
            'manufacturer' => $this->manufacturer,
            'finish_date' => $this->finish_date,
            'isFavourit' => $f,
        ]; 
    }
}
