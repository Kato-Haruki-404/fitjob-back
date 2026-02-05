<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPosting;
use App\Models\Tag;
use App\Http\Requests\UpdateJobStatusRequest;
use App\Http\Requests\UpdateJobMetadataRequest;

class AdminController extends Controller
{
    public function getPendingJobs(Request $request)
    {
        $jobs = JobPosting::where('status', $request->status ?? 'pending')
            ->with('address', 'tags', 'momentum')
            ->get();
        return response()->json([
            'data' => $jobs
        ]);
    }

    public function updateJobStatus($id, UpdateJobStatusRequest $request)
    {
        $job = JobPosting::findOrFail($id);
        $job->status = $request->status;
        $job->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateJobMetadata($id, UpdateJobMetadataRequest $request)
    {
        $job = JobPosting::findOrFail($id);
        $job->momentum()->updateOrCreate(
            [],
            [
                'calorie' => $request->calorie,
                'steps' => $request->steps,
                'exercise_level' => $request->exercise_level,
            ]
        );
        $job->tags()->sync($request->tag_ids);
        return response()->json([
            'success' => true,
        ]);
    }

    public function getTags()
    {
        $tags = Tag::all();
        return response()->json([
            'data' => $tags
        ]);
    }
}
