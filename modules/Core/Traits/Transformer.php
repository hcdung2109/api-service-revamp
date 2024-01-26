<?php


namespace Digisource\Core\Traits;


use Digisource\Core\Contracts\TransformerContract;

trait Transformer
{
    private function doTransform($data, $transformer){
        if (is_callable($transformer)) {
            return collect($data)->map($transformer);
        } else if ($transformer instanceof TransformerContract){
            return collect($data)->map(function ($item) use($transformer){
                return $transformer->transform($item);
            });
        }else{
            return $data;
        }
    }
}
