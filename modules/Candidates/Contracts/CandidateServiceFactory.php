<?php


namespace Digisource\Candidates\Contracts;


interface CandidateServiceFactory
{
    public function list($query, $filterBy, $sortBy, $page, $pageSize);
    public function getDetail($id);
}
