<?php

namespace Modules\Products\App\Services;

class ProductSizeTransformerService
{
    /**
     * Get product size name with exceptions handling
     *
     * @param mixed $size
     * @return string
     */
    public function getName($size): string
    {
        $name = $size->name ?? '';
        
        if ($size->data && $size->data['age'] != '' && strstr($size->data['age'], 'سنوات')) {
            $age = '';
        } else {
            $age = (in_array($size->product && $size->product->category_id, [1, 2, 3, 4, 160, 168])) ? __('messages.age') : '';
        }
        
        $weight = __('messages.weight');

        if ($size->data && is_array($size->data) && isset($size->data['weight']) && isset($size->data['age'])) {
            return $size->data['weight'] . ' ' . $weight . ' ' . $size->data['age'] . ' ' . $age;
        }
        
        return $name;
    }

    /**
     * Get product size age label
     *
     * @param mixed $size
     * @return string
     */
    public function getAge($size): string
    {
        if ($size->data && $size->data['age'] != '' && strstr($size->data['age'], 'سنوات')) {
            $age = '';
        } else {
            $age = ($size->product && in_array($size->product->category_id, [1, 2, 3, 4, 160, 168])) ? __('messages.age') : '';
        }

        return (is_array($size->data) && isset($size->data['age'])) ? $size->data['age'] . ' ' . $age : '';
    }

    /**
     * Get product size weight label
     *
     * @param mixed $size
     * @return string
     */
    public function getWeight($size): string
    {
        return (is_array($size->data) && isset($size->data['weight'])) ? $size->data['weight'] . ' ' . __('messages.weight') : '';
    }
}

