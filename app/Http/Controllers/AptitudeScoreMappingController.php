<?php

namespace App\Http\Controllers;

use App\Models\AptitudeScoreMapping;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AptitudeScoreMappingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AptitudeScoreMapping::with(['answerOption', 'domain', 'skill'])->get());
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
        return response()->json($mapping->load(['answerOption', 'domain', 'skill']), 201);
    }

    public function show(AptitudeScoreMapping $aptitudeScoreMapping): JsonResponse
    {
        return response()->json($aptitudeScoreMapping->load(['answerOption', 'domain', 'skill']));
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
        return response()->json($aptitudeScoreMapping->load(['answerOption', 'domain', 'skill']));
    }

    public function destroy(AptitudeScoreMapping $aptitudeScoreMapping): JsonResponse
    {
        $aptitudeScoreMapping->delete();
        return response()->json(['message' => 'Aptitude score mapping deleted successfully']);
    }
}
