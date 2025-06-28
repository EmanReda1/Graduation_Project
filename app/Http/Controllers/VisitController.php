<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Student;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Display a listing of the visits (Table View Only for Librarian).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Visit::with(['student:student_id,fullname,university_code']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('university_code', 'like', "%{$search}%");
            });
        }

        // Apply date filter
        if ($request->filled('date')) {
            $query->whereDate('visit_time', $request->date);
        }

        // Sort visits by most recent first
        $visits = $query->orderBy('visit_time', 'desc')->paginate(15);

        return view('visits.index', compact('visits'));
    }

    /**
     * Search for visits.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Get today's visits.
     *
     * @return \Illuminate\Http\Response
     */
    public function getToday()
    {
        $visits = Visit::with(['student:student_id,fullname,university_code'])
            ->whereDate('visit_time', now())
            ->orderBy('visit_time', 'desc')
            ->paginate(15);

        return view('visits.index', compact('visits'));
    }

    /**
     * Show QR Code for library entrance
     *
     * @return \Illuminate\Http\Response
     */
    public function showQrCode()
    {
        // Generate QR code data
        $qrCodeData = 'LIBRARY_001'; // This is what students will scan
        $libraryName = 'مكتبة كلية الحاسبات والمعلومات - جامعة الزقازيق';

        return view('visits.qr-code', compact('qrCodeData', 'libraryName'));
    }

    // Remove all other methods - only table view is needed
}

