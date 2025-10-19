<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
{
    return view('auth/login'); // Carga directamente la vista sin redireccionar
}

}
