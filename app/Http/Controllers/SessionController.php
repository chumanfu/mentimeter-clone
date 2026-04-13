<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\Response;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function joinForm(Request $request)
    {
        return view('join', [
            'prefillCode' => $request->query('code'),
        ]);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'join_code' => ['required', 'digits:6'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        $presentation = Presentation::where('join_code', $validated['join_code'])
            ->where('is_live', true)
            ->first();

        if (! $presentation) {
            return back()->withErrors(['join_code' => 'That code is invalid or this presentation is not live.'])->withInput();
        }

        $request->session()->put("participant_name_{$presentation->id}", $validated['name']);
        $request->session()->put("participant_key_{$presentation->id}", sha1($validated['name'].'|'.$request->ip().'|'.$request->userAgent()));

        return redirect()->route('session.show', ['joinCode' => $presentation->join_code]);
    }

    public function show(Request $request, string $joinCode)
    {
        $presentation = Presentation::where('join_code', $joinCode)
            ->where('is_live', true)
            ->with(['questions.choices'])
            ->firstOrFail();

        $participantName = $request->session()->get("participant_name_{$presentation->id}");
        if (! $participantName) {
            return redirect()->route('join.form');
        }

        $activeQuestion = $presentation->questions->firstWhere('is_active', true)
            ?? $presentation->questions->first();

        $participantKey = $request->session()->get("participant_key_{$presentation->id}");

        $existingResponse = null;
        if ($activeQuestion && $participantKey) {
            $existingResponse = Response::query()
                ->where('question_id', $activeQuestion->id)
                ->where('participant_key', $participantKey)
                ->with('choice')
                ->first();
        }

        return view('session', [
            'presentation' => $presentation,
            'activeQuestion' => $activeQuestion,
            'participantName' => $participantName,
            'existingResponse' => $existingResponse,
        ]);
    }
}
