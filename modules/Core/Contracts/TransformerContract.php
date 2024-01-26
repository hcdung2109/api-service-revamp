<?php


namespace Digisource\Core\Contracts;


interface TransformerContract
{
    /**
     * @param $item
     * @return mixed
     */
    public function transform(TransformerItem $item);
}
