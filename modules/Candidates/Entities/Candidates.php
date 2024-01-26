<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;


/**
 * Class Package
 *
 * @package Digisource\Packages\Entities
 *
 *
 * @OA\Schema(
 *     schema="Candidate",
 *     description="Candidate",
 *     title="Candidate Entity"
 * )
 */
class Candidates extends BaseModel implements TransformerItem
{
    protected $table = TableName::CANDIDATE;
    protected $guarded = [];

}
/**
 * @OA\Property(
 *     property="id",
 *     type="integer",
 *     description="package id",
 *     example="1"
 * )
 */
/**
 * @OA\Property(
 *     property="name",
 *     type="string",
 *     description="package name",
 *     example="Dynamic Package GEN"
 * )
 */
/**
 * @OA\Property(
 *     property="duration",
 *     type="string",
 *     description="package duration",
 *     example="P5DT0H0M0S"
 * )
 */

/**
 * @OA\Property(
 *     property="product_types",
 *     description="HOT - Hotel
CAR - Car
TKT - Ticket
TRF - Transfer
PCK – Package
FLH – Flight
INS – Insurance",
 *     type="array",
 *     @OA\Items(type="string"),
 *     example="['HOT', 'CAR', 'TKT', 'TRF', 'PCK', 'FLH','INS']"
 * )
 */

/**
 * @OA\Property(
 *     property="images",
 *     description="package images",
 *     type="array",
 *     @OA\Items(type="string")
 * )
 */

/**
 * @OA\Property(
 *     property="status",
 *     description="package status",
 *     type="integer",
 *     example="1"
 * )
 */

/**
 * @OA\Property(
 *     property="description",
 *     description="package description",
 *     type="string",
 *     example=""
 * )
 */
/**
 * @OA\Property(
 *     property="short_description",
 *     description="package short description",
 *     type="string",
 *     example=""
 * )
 */

/**
 * @OA\Property(
 *     property="general_inclusions",
 *     description="package general inclusions",
 *     type="string",
 *     example=""
 * )
 */

/**
 * @OA\Property(
 *     property="valued_at",
 *     description="Valued at",
 *     type="string",
 *     example="$600"
 * )
 */

/**
 * @OA\Property(
 *     property="member_inclusions",
 *     description="package member inclusions",
 *     type="string",
 *     example=""
 * )
 */
/**
 * @OA\Property(
 *     property="featured_member_inclusions",
 *     description="package featured member inclusions",
 *     type="string",
 *     example=""
 * )
 */
/**
 * @OA\Property(
 *     property="important_notes",
 *     description="package important notes",
 *     type="string",
 *     example=""
 * )
 */
/**
 * @OA\Property(
 *     property="offer_tac",
 *     description="Offer Terms and Conditions",
 *     type="string"
 * )
 */
/**
 * @OA\Property(
 *     property="important_info",
 *     description="Important Information",
 *     type="array",
 *     @OA\Items(
 *      @OA\Property(property="title", type="string", example="Blackout Dates"),
 *      @OA\Property(property="value", type="string", example="String text"),
 *     )
 * )
 */
/**
 * @OA\Property(
 *     property="galleries",
 *     description="package galleries",
 *     type="array",
 *     @OA\Items(
 *      @OA\Property(property="index", type="integer", example=1),
 *      @OA\Property(property="raw", type="string", example="https://cdn-..."),
 *      @OA\Property(property="sort", type="integer", example="1"),
 *     )
 * )
 */
