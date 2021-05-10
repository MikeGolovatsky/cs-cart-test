<?php

declare(strict_types=1);

namespace Shop;

final class Shop
{
    /**
     * @var Item[]
     */
    private $items;
    
    /**
     * Max quality value for all products but Mjolnir
     */
    const QUALITY_VALUE_MAX = 50;
    
    /**
     * Min quality value for all products but Mjolnir
     */
    const QUALITY_VALUE_MIN = 0;
    
    /**
     * Constant quality value for Mjolnir
     */
    const QUALITY_VALUE_MJOLNIR = 80;
    
    /**
     * Value of decrement for default products.  
     */
    const QUALITY_VALUE_DECREMENT_DEFAULT = 1;
    
    /**
     * Value of decrement for magic products.
     */
    const QUALITY_VALUE_DECREMENT_MAGIC = 2;
    
    public function __construct(array $items)
    {
        $this->items = $items;
    }
    
    /**
     * Calculates|Returns the quality value of Mjolnir
     * 
     * @param int $quality
     * @return int Quality value of Mjolnir
     */
    private function calculateQualityForMjolnir(int $quality): int
    {
        return self::QUALITY_VALUE_MJOLNIR;
    }
    
    /**
     * Check if the quality value in the allowed range of values. It can be used 
     * for all products but Mjolnir.
     * 
     * @param int $quality
     * @return bool
     */
    private function isProductQualityCorrect(int $quality): bool
    {
        return $quality >= self::QUALITY_VALUE_MIN && $quality <= self::QUALITY_VALUE_MAX ? true : false;
    }
    
    /**
     * Fixes the quality value if it's out of the allowed range of values. 
     * It can be used for all products but Mjolnir.
     * 
     * @param int $quality
     * @return int
     */
    private function fixProductQuality(int $quality): int
    {
        if ($quality < self::QUALITY_VALUE_MIN) {
            $quality = self::QUALITY_VALUE_MIN;
        } else if ($quality > self::QUALITY_VALUE_MAX) {
            $quality = self::QUALITY_VALUE_MAX;
        }
        
        return $quality;
    }
    
    /**
     * Returns if the product is expired.
     * 
     * @param int $sell_in
     * @return bool
     */
    private function isProductExpired(int $sell_in): bool
    {
        return $sell_in < 0 ? true : false;
    }
    
    /**
     * Calculates|Returns the quality value of Blue cheese.
     * 
     * @param int $quality
     * @param int $sell_in
     * @return int
     */
    private function calculateQualityForBlueCheese(int $quality, int $sell_in): int
    {
        if ($this->isProductExpired($sell_in) && $quality <= self::QUALITY_VALUE_MAX - 2) {
            return $quality + 2;
        } 
        
        if ($this->isProductExpired($sell_in) && $quality >= self::QUALITY_VALUE_MAX - 1) {
            return self::QUALITY_VALUE_MAX;
        } 
        
        if (!$this->isProductExpired($sell_in) && $quality <= self::QUALITY_VALUE_MAX - 1) {
            return $quality + 1;
        } 
        
        if (!$this->isProductExpired($sell_in) && $quality >= self::QUALITY_VALUE_MAX - 1) {
            return self::QUALITY_VALUE_MAX;
        }
    }
    
    /**
     * Calculates|Returns the quality value of Concert tickets.
     * 
     * @param int $quality
     * @param int $sell_in
     * @return int
     */
    private function calculateQualityForConcertTickets(int $quality, int $sell_in):int
    {
        if ($this->isProductExpired($sell_in)) {
            return self::QUALITY_VALUE_MIN;
        } 
        
        $is_10_days_left = $sell_in <= 10 && $sell_in > 5;
        if ( $is_10_days_left && $quality < self::QUALITY_VALUE_MAX - 2){
            return $quality + 2;
        } 
        
        if ($is_10_days_left && $quality >= self::QUALITY_VALUE_MAX - 2){
            return self::QUALITY_VALUE_MAX;
        }
        
        $is_5_days_left = $sell_in <= 5;
        if ($is_5_days_left && $quality < self::QUALITY_VALUE_MAX - 3) {
            return $quality + 3;
        } 
        
        if ($is_5_days_left && $quality >= self::QUALITY_VALUE_MAX - 3) {
            return self::QUALITY_VALUE_MAX;
        }
        
        if ($quality < self::QUALITY_VALUE_MAX - 1) {
            return $quality + 1;
        } else {
            return self::QUALITY_VALUE_MAX;
        }
    }
    
    /**
     * Calculates|Returns the quality value of default or magic products.  
     * It cann't be used for Mjolnir, Concert tickets and Blue cheese.
     * 
     * @param int $quality
     * @param int $sell_in
     * @param int $decrement_value
     * @return int
     */
    private function calculateQualityBaseLogic(int $quality, int $sell_in, int $decrement_value):int
    {
        if ($this->isProductExpired($sell_in) && $quality > self::QUALITY_VALUE_MIN + 2*$decrement_value) {
            return $quality - 2*$decrement_value;
        } 
        
        if ($this->isProductExpired($sell_in) && $quality <= self::QUALITY_VALUE_MIN + 2*$decrement_value) {
            return self::QUALITY_VALUE_MIN;
        }
        
        if ($quality > self::QUALITY_VALUE_MIN + $decrement_value) {
            return $quality - $decrement_value;
        } 
        
        if ($quality <= self::QUALITY_VALUE_MIN + $decrement_value) {
            return self::QUALITY_VALUE_MIN;
        }
    }
    
    /**
     * Calculates|Returns the quality value of default product.
     * 
     * @param int $quality
     * @param int $sell_in
     * @return int
     */
    private function calculateQualityForDefaultProduct(int $quality, int $sell_in): int
    {
        return $this->calculateQualityBaseLogic($quality, $sell_in, self::QUALITY_VALUE_DECREMENT_DEFAULT);
    }
    
    /**
     * Checks if it's a magic product.
     * 
     * @param string $name
     * @return bool
     */
    private function isMagicProduct(string $name): bool
    {
        /**
         * I've taken the simplest test for magic products...
         */
        return stripos($name, 'Magic') !== false ? true : false;
    }
    
    /**
     * Calculates|Returns the quality value of magic product.
     * 
     * @param int $quality
     * @param int $sell_in
     * @return int
     */
    private function calculateQualityForMagicProduct(int $quality, int $sell_in): int
    {
        return $this->calculateQualityBaseLogic($quality, $sell_in, self::QUALITY_VALUE_DECREMENT_MAGIC);
    }    

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            if ($item->name !== 'Mjolnir' && !$this->isProductQualityCorrect($item->quality)) {
                $item->sell_in--;
                $item->quality = $this->fixProductQuality($item->quality);
                continue;
            }
            
            if ($item->name === 'Mjolnir') {
                $item->quality = $this->calculateQualityForMjolnir($item->quality);
                continue;
            }
            
            $item->sell_in--;
            
            switch($item->name){
                case 'Blue cheese' : {
                    $item->quality = $this->calculateQualityForBlueCheese($item->quality, $item->sell_in);
                    break;
                }
                case 'Concert tickets' : {
                    $item->quality = $this->calculateQualityForConcertTickets($item->quality, $item->sell_in);
                    break;
                }
                default:{
                    if ($this->isMagicProduct($item->name)) {
                        $item->quality = $this->calculateQualityForMagicProduct($item->quality, $item->sell_in);
                    } else {
                        $item->quality = $this->calculateQualityForDefaultProduct($item->quality, $item->sell_in);
                    }
                }
            }
        }
    }
}