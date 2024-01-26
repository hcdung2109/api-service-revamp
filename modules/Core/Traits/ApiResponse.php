<?php


namespace Digisource\Core\Traits;

use Digisource\Core\Contracts\TransformerContract;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Traits\BaseApiResponse;
use \Illuminate\Contracts\Pagination\LengthAwarePaginator;
/**
* Class ApiResponse
 *
 * @OA\Schema(
 *   description="ApiResponse",
 *   title="ApiResponse Schema",
 *   schema="ApiResponse",
 *    @OA\Property(
 *      property="success",
 *      type="boolean",
 *      description="success status",
 *      example="true"
    *    ),
 *    @OA\Property(
 *      property="status",
 *      type="integer",
 *      description="status",
 *      example="200"
    *    ),
 *    @OA\Property(
 *      property="message",
 *      type="string",
 *      description="error message",
 *      example=""
    *    ),
 *    @OA\Property(
 *      property="error",
 *      type="integer",
 *      description="error code",
 *      example="0"
    *    ),
 * )
 */
trait ApiResponse
{
    use BaseApiResponse;
    use Transformer;

    /**
     * @param $data
     * @param  null  $transformer
     */
    public function addData($data, $transformer = null)
    {
        if ($data instanceof LengthAwarePaginator) {
            $transformedData = $this->doTransform($data->items(), $transformer);
            $current = $data->currentPage();
            $this->data = [
                "items" => $transformedData,
                "total" => $data->total(),
                "total_page" => $data->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $data->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $data->perPage(),
                "from" => $data->firstItem(),
                "to" => $data->lastItem(),
            ];
        } else {
            if (is_countable($data)) {
                $this->data = $this->doTransform($data, $transformer);
            } else if ($data instanceof TransformerItem && $transformer instanceof TransformerContract) {
                $this->data = $transformer->transform($data);
            }else{
                $this->data = $data;
            }
        }
    }

    /**
     * append new response data
     * @param $data
     */
    public function appendData($data){
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }
}
