<?php
namespace App\Http\Controllers;
use App\Models\JournalEntry;
class JournalController extends Controller
{
    public function index(){return view('accounting.journals',['entries'=>JournalEntry::with('lines.account')->latest('entry_date')->paginate(20)]);}
}
