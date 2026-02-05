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
        $query = JobPosting::with(['tags', 'momentum', 'address'])->where('status', 'approved');

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

        if ($request->filled('jobIds')) {
            $query->whereIn('id', $request->input('jobIds'));
        }
        if ($request->filled('min_wage')) {
            $query->where('wage', '>=', $request->input('min_wage'));
        }
        if ($request->filled('max_wage')) {
            $query->where('wage', '<=', $request->input('max_wage'));
        }
        if ($request->filled('salary_type')) {
            $query->where('salary_type', $request->input('salary_type'));
        }
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

        if ($request->filled('tags')) {
            $tags = $request->input('tags');
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        // ソートパラメータの解析
        $defaultSort = $request->filled(['latitude', 'longitude']) ? ['distance', 'latest'] : ['latest'];
        $sortParams = [];

        if ($request->boolean('distance')) $sortParams[] = 'distance';
        if ($request->boolean('calorie')) $sortParams[] = 'calorie';
        if ($request->boolean('wage')) $sortParams[] = 'wage_desc';
        if ($request->boolean('exercise')) $sortParams[] = 'exercise_desc';
        if ($request->boolean('latest')) $sortParams[] = 'latest';

        // sort配列パラメータが指定されている場合は、それを優先して使用
        if ($request->filled('sort')) {
            $inputSort = $request->input('sort');
            $sortParams = is_array($inputSort) ? $inputSort : explode(',', $inputSort);
        } 
        // どちらも指定がなければデフォルト
        elseif (empty($sortParams)) {
            $sortParams = $defaultSort;
        }

        $joinedMomenta = false;
        $joinedAddresses = false;

        foreach ($sortParams as $sortKey) {
            $sortKey = trim($sortKey);

            if (($sortKey === 'exercise' || $sortKey === 'exercise_desc' || $sortKey === 'exercise_asc' || $sortKey === 'calorie') && !$joinedMomenta) {
                $query->join('momenta', 'job_postings.id', '=', 'momenta.job_posting_id');
                
                if ($sortKey === 'calorie') {
                    $query->orderBy('momenta.calorie', 'desc');
                } else {
                    $query->orderBy('momenta.exercise_level', $sortKey === 'exercise_asc' ? 'asc' : 'desc');
                }
                $joinedMomenta = true;
            } elseif ($sortKey === 'distance' && $request->filled(['latitude', 'longitude']) && !$joinedAddresses) {
                $lat = $request->input('latitude');
                $lng = $request->input('longitude');

                $query->join('addresses', 'job_postings.address_id', '=', 'addresses.id')
                    ->selectRaw('
                        job_postings.*,
                        ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance
                    ', [$lat, $lng, $lat])
                    ->orderBy('distance');
                $joinedAddresses = true;
            } else {
                $sortConfig = self::SORT_OPTIONS[$sortKey] ?? null;
                if ($sortConfig) {
                    $query->orderBy($sortConfig['column'], $sortConfig['direction']);
                }
            }
        }

        // job_postingsテーブルのカラムを選択（既にselectRawなどで指定されていない場合）
        if (!$joinedAddresses && !$joinedMomenta) {
            // 特にJOINしていない場合はそのまま
        } elseif ($joinedMomenta && !$joinedAddresses) {
            $query->select('job_postings.*');
        }
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

        $addressId = null;
        if (!empty($validated['access'])) {
            $address = AddressService::createFromFullAddress($validated['access']);
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
