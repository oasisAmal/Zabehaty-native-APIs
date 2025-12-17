<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSizeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private $name='';

    public function toArray($request)
    {
        $this->name = $this->exceptions();
        if( $this->data && $this->data['age']!='' && strstr($this->data['age'],'سنوات') )
            $age = '';
        else
            $age = ($this->product && in_array($this->product->category_id , [1,2,3,4,160,168])) ? __('messages.age') :''  ;

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'descripion' => $this->descripion,
            'image' => $this->image,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'notes' => $this->notes,
            'weight' => (is_array($this->data) && isset($this->data['weight'])) ? $this->data['weight'].' '.__('messages.weight'):'' ,
            'age' =>  (is_array($this->data) && isset($this->data['age'])) ? $this->data['age'].' '.$age:''  ,
            'enough_for_from' => $this->enough_for_from,
            'enough_for_to' => $this->enough_for_to,
            'stock' => $this->stock,
        ];

		return $data ;
    }

    function exceptions()
    {
        if($this->data && $this->data['age']!='' && strstr($this->data['age'],'سنوات') )
            $age = '';
        else
            $age = (in_array($this->product && $this->product->category_id , [1,2,3,4,160,168])) ? __('messages.age') :''  ;
        $weight = __('messages.weight');

        if ($this->data && is_array($this->data) && isset($this->data['weight']) && isset($this->data['age']))
            return $this->data['weight'] . ' ' . $weight . ' ' . $this->data['age'] . ' ' . $age;
        return $this->name;
    }
}
