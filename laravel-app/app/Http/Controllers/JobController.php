<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreJobPostingRequest;
use App\Models\JobPosting;
use App\Services\AddressService;

class JobController extends Controller
{
    /**
     * Sortable columns mapping
     */
    private const SORT_OPTIONS = [

        'wage_desc' => ['column' => 'wage', 'direction' => 'desc'],
        'latest' => ['column' => 'created_at', 'direction' => 'desc'],
    ];

    public function index(Request $request)
    {
        $query = JobPosting::with(['tags', 'momentum', 'address'])->where('is_published', 1);

        // Keyword Search (multiple keywords - AND logic between keywords, OR logic between columns)
        if ($request->filled('keyword')) {
            $keywords = $request->input('keyword');
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->where('title', 'like', "%{$keyword}%")
                             ->orWhere('company_name', 'like', "%{$keyword}%")
                             ->orWhereHas('address', function ($addressQ) use ($keyword) {
                                 $addressQ->where('prefecture', 'like', "%{$keyword}%")
                                          ->orWhere('city', 'like', "%{$keyword}%")
                                          ->orWhere('town', 'like', "%{$keyword}%")
                                          ->orWhere('nearest_station', 'like', "%{$keyword}%")
                                          ->orWhere('line_name', 'like', "%{$keyword}%");
                             });
                    });
                }
            });
        }

        // IDs
        if ($request->filled('jobIds')) {
            $query->whereIn('id', $request->input('jobIds'));
        }

        // Wage / Salary Filters
        if ($request->filled('min_wage')) {
            $query->where('wage', '>=', $request->input('min_wage'));
        }
        if ($request->filled('max_wage')) {
            $query->where('wage', '<=', $request->input('max_wage'));
        }


        // Momentum Filters
        if ($request->anyFilled(['min_calorie', 'max_calorie', 'min_steps', 'max_steps', 'exercise_levels'])) {
            $query->whereHas('momentum', function ($q) use ($request) {
                if ($request->filled('min_calorie')) {
                    $q->where('calorie', '>=', $request->input('min_calorie'));
                }
                if ($request->filled('max_calorie')) {
                    $q->where('calorie', '<=', $request->input('max_calorie'));
                }
                if ($request->filled('min_steps')) {
                    $q->where('steps', '>=', $request->input('min_steps'));
                }
                if ($request->filled('max_steps')) {
                    $q->where('steps', '<=', $request->input('max_steps'));
                }
                if ($request->filled('exercise_levels')) {
                    $q->whereIn('exercise_level', $request->input('exercise_levels'));
                }
            });
        }

        // Sort
        $sortKey = $request->input('sort', 'latest');
        $sortConfig = self::SORT_OPTIONS[$sortKey] ?? self::SORT_OPTIONS['latest'];
        $query->orderBy($sortConfig['column'], $sortConfig['direction']);

        // Pagination
        $perPage = min($request->input('per_page', 20), 100); // Max 100 per page
        $jobs = $query->paginate($perPage);

        return response()->json($jobs);
    }

    public function store(StoreJobPostingRequest $request)
    {
        $validated = $request->validated();

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('images', 'public')
            : null;

        // 住所情報がある場合はAddressを作成
        $addressId = null;
        if (!empty($validated['access'])) {
            $postalCode = $validated['postalCode'] ?? '';
            $address = AddressService::createFromFullAddress($postalCode, $validated['access']);
            $addressId = $address->id;
        }

        $job = JobPosting::create([
            'title' => $validated['title'],
            'company_name' => $validated['companyName'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'salary_type' => $validated['salaryType'],
            'wage' => $validated['wage'],
            'employment_type' => $validated['employmentType'],
            'external_link_url' => $validated['externalLinkUrl'],
            'image' => $imagePath,
            'address_id' => $addressId,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
