<?php


namespace Digisource\Users\Contracts;


interface UsersServiceFactory
{
    public function list($query, $filterBy, $sortBy, $page, $pageSize);
    public function getDetail($id);
}
