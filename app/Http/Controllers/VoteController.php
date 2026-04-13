<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Presentation;
use App\Models\Response;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function submit(Request $request, string $joinCode)
    {
        $presentation = Presentation::where('join_code', $joinCode)
            ->where('is_live', true)
            ->with(['questions.choices'])
            ->firstOrFail();

        $activeQuestion = $presentation->questions->firstWhere('is_active', true)
            ?? $presentation->questions->first();

        if (! $activeQuestion) {
            return back()->withErrors(['choice_id' => 'There is no active question yet.']);
        }

        $validated = $request->validate([
            'choice_id' => ['required', 'integer'],
        ]);

        $choice = Choice::where('question_id', $activeQuestion->id)
            ->find($validated['choice_id']);

        if (! $choice) {
            return back()->withErrors(['choice_id' => 'Invalid choice selected.']);
        }

        $participantKey = $request->session()->get("participant_key_{$presentation->id}");
        if (! $participantKey) {
            return redirect()->route('join.form');
        }

        $already = Response::query()
            ->where('question_id', $activeQuestion->id)
            ->where('participant_key', $participantKey)
            ->exists();

        if ($already) {
            return redirect()
                ->route('session.show', ['joinCode' => $presentation->join_code]);
        }

        $participantName = $request->session()->get("participant_name_{$presentation->id}");

        Response::create([
            'question_id' => $activeQuestion->id,
            'participant_key' => $participantKey,
            'participant_name' => $participantName,
            'choice_id' => $choice->id,
        ]);

        return redirect()
            ->route('session.show', ['joinCode' => $presentation->join_code])
            ->with('status', __('Your answer was recorded. You cannot change it for this question.'));
    }
}
