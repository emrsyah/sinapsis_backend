<?php

namespace App\Http\Controllers;

use App\Data\StudyTool\StoreStudyToolData;
use App\Data\StudyTool\StudyToolData;
use App\Models\Note;
use App\Models\StudyTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudyToolController extends Controller
{
    public function showOne(Request $request): StudyToolData
    {
        $studyTool = StudyTool::where('note_id', $request->query('note_id'))
            ->where('type', $request->query('type'))
            ->firstOrFail();

        $this->authorize('view', $studyTool);

        return StudyToolData::fromModel($studyTool);
    }

    public function index(Request $request, string $id): JsonResponse
    {
        $note = Note::findOrFail($id);
        $this->authorize('view', $note);

        $studyTools = StudyTool::where('note_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->where('type', $request->query('type'))
            ->get();

        return response()->json(StudyToolData::collect($studyTools));
    }

    public function store(StoreStudyToolData $data, Request $request): StudyToolData
    {
        $note = Note::findOrFail($data->note_id);
        $this->authorize('update', $note);

        $studyTool = StudyTool::updateOrCreate(
            [
                'note_id' => $data->note_id,
                'user_id' => $request->user()->user_id,
                'type' => $data->type,
            ],
            [
                'content' => $data->content,
                'status' => $data->status,
                'image_url' => $data->image_url,
            ]
        );

        return StudyToolData::fromModel($studyTool);
    }
}
