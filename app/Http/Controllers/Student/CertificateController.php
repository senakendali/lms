<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class CertificateController extends Controller
{
    public function index()
    {
        return view('student.certificates.index');
    }
}
