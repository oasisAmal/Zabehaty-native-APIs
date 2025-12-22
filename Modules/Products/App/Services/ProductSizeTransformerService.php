<?php

namespace Modules\Products\App\Services;

class ProductSizeTransformerService
{
    /**
     * Cached translation labels
     */
    private static ?string $ageLabel = null;
    private static ?string $weightLabel = null;

    /**
     * Get age label for size
     *
     * @param mixed $size
     * @return string
     */
    private function getAgeLabel($size): string
    {
        if ($size->data && $size->data['age'] != '' && strstr($size->data['age'], 'سنوات')) {
            return '';
        }

        if (self::$ageLabel === null) {
            self::$ageLabel = __('messages.age');
        }

        $categoryIds = [1, 2, 3, 4, 160, 168];
        return ($size->product && in_array($size->product->category_id, $categoryIds)) ? self::$ageLabel : '';
    }

    /**
     * Get weight label
     *
     * @return string
     */
    private function getWeightLabel(): string
    {
        if (self::$weightLabel === null) {
            self::$weightLabel = __('messages.weight');
        }

        return self::$weightLabel;
    }

    /**
     * Get product size name with exceptions handling
     *
     * @param mixed $size
     * @return string
     */
    public function getName($size): string
    {
        $name = $size->name ?? '';
        
        if (!is_array($size->data) || !isset($size->data['weight']) || !isset($size->data['age'])) {
            return $name;
        }

        $ageLabel = $this->getAgeLabel($size);
        $weightLabel = $this->getWeightLabel();

        return $size->data['weight'] . ' ' . $weightLabel . ' ' . $size->data['age'] . ' ' . $ageLabel;
    }

    /**
     * Get product size age label
     *
     * @param mixed $size
     * @return string
     */
    public function getAge($size): string
    {
        if (!is_array($size->data) || !isset($size->data['age'])) {
            return '';
        }

        $ageLabel = $this->getAgeLabel($size);
        return $size->data['age'] . ' ' . $ageLabel;
    }

    /**
     * Get product size weight label
     *
     * @param mixed $size
     * @return string
     */
    public function getWeight($size): string
    {
        if (!is_array($size->data) || !isset($size->data['weight'])) {
            return '';
        }

        return $size->data['weight'] . ' ' . $this->getWeightLabel();
    }
}

