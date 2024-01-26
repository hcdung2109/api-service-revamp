<?php


namespace Digisource\Core\Constant;


final class Status
{
    const INACTIVE = 0;
    const ACTIVE = 1;
    const UN_PUBLISHED = 0;
    const PUBLISHED = 1;
    const DRAFT = 2;

    public static function all()
    {
        return [
            self::PUBLISHED => 'Published',
            self::UN_PUBLISHED => 'Unpublished'
        ];
    }

    public static function map()
    {
        return [
            'Published' => self::PUBLISHED,
            'Unpublished' => self::UN_PUBLISHED
        ];
    }

    public static function label($status)
    {
        return $status == 1 ? 'Published' : 'Unpublished';
    }
}
