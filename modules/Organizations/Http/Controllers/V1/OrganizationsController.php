<?php

namespace Digisource\Organizations\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('organizations::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organizations::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('organizations::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('organizations::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
