<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function create() 
    {
        return "DENTRO DO FORM PARA CREATE TICKE";
    }
    
    public function createForm(Request $request) 
    {
        
    }
    
    
    
}