<?php

namespace App\Http\Controllers;

use App\Models\AptitudeScoreMapping;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AptitudeScoreMappingResource;

class AptitudeScoreMappingController extends Controller
{
    public function index(): JsonResponse
    {
        return AptitudeScoreMappingResource::collection(AptitudeScoreMapping::with(['answerOption', 'domain', 'skill'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'answer_option_id' => 'required|exists:answer_options,id',
            'domain_id' => 'required|exists:domains,id',
            'skill_id' => 'required|exists:skills,id',
            'weight_score' => 'required|numeric',
        ]);

        $mapping = AptitudeScoreMapping::create($validated);
        return (new AptitudeScoreMappingResource($mapping->load(['answerOption', 'domain', 'skill'])))->response()->setStatusCode(201);
    }

    public function show(AptitudeScoreMapping $aptitudeScoreMapping): JsonResponse
    {
        return (new AptitudeScoreMappingResource($aptitudeScoreMapping->load(['answerOption', 'domain', 'skill'])))->response();
    }

    public function update(Request $request, AptitudeScoreMapping $aptitudeScoreMapping): JsonResponse
    {
        $validated = $request->validate([
            'answer_option_id' => 'required|exists:answer_options,id',
            'domain_id' => 'required|exists:domains,id',
            'skill_id' => 'required|exists:skills,id',
            'weight_score' => 'required|numeric',
        ]);

        $aptitudeScoreMapping->update($validated);
        return (new AptitudeScoreMappingResource($aptitudeScoreMapping->load(['answerOption', 'domain', 'skill'])))->response();
    }

    public function destroy(AptitudeScoreMapping $aptitudeScoreMapping): JsonResponse
    {
        $aptitudeScoreMapping->delete();
        return response()->json(['message' => 'Aptitude score mapping deleted successfully']);
    }
}
