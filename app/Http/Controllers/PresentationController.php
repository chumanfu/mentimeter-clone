<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PresentationController extends Controller
{
    public function index(): View
    {
        $presentations = auth()->user()
            ->presentations()
            ->withCount('responses')
            ->latest()
            ->get();

        return view('dashboard', compact('presentations'));
    }

    public function create(): View
    {
        return view('presentations.create');
    }

    public function store(Request $request)
    {
        $choices = collect($request->input('choices', []))
            ->map(fn ($choice) => trim((string) $choice))
            ->filter()
            ->values()
            ->all();

        $request->merge(['choices' => $choices]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'prompt' => ['required', 'string', 'max:255'],
            'choices' => ['required', 'array', 'min:2', 'max:12'],
            'choices.*' => ['required', 'string', 'max:120'],
        ]);

        $presentation = DB::transaction(function () use ($validated) {
            $presentation = Presentation::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'join_code' => null,
                'manage_token' => Str::uuid()->toString(),
                'is_live' => false,
            ]);

            $question = $presentation->questions()->create([
                'prompt' => $validated['prompt'],
                'type' => 'multiple_choice',
                'is_active' => true,
            ]);

            foreach ($validated['choices'] as $choice) {
                $question->choices()->create(['label' => $choice]);
            }

            return $presentation;
        });

        return redirect()->route('host.show', ['token' => $presentation->manage_token]);
    }

    public function host(string $token): View
    {
        $presentation = $this->presentationForHost($token);
        $presentation->load(['questions.choices.responses']);
        $presentation->loadCount('responses');

        $activeQuestion = $presentation->questions->firstWhere('is_active', true)
            ?? $presentation->questions->first();

        $totals = [];
        $totalVotes = 0;

        if ($activeQuestion) {
            foreach ($activeQuestion->choices as $choice) {
                $count = $choice->responses->count();
                $totals[] = [
                    'label' => $choice->label,
                    'count' => $count,
                ];
                $totalVotes += $count;
            }
        }

        return view('host', [
            'presentation' => $presentation,
            'activeQuestion' => $activeQuestion,
            'totals' => $totals,
            'totalVotes' => $totalVotes,
        ]);
    }

    public function present(string $token): View
    {
        $presentation = $this->presentationForHost($token);
        abort_unless($presentation->is_live && $presentation->join_code, 404);

        $presentation->load(['questions.choices.responses']);

        $activeQuestion = $presentation->questions->firstWhere('is_active', true)
            ?? $presentation->questions->first();

        $totals = [];
        $totalVotes = 0;

        if ($activeQuestion) {
            foreach ($activeQuestion->choices as $choice) {
                $count = $choice->responses->count();
                $totals[] = [
                    'label' => $choice->label,
                    'count' => $count,
                ];
                $totalVotes += $count;
            }
        }

        $joinUrl = route('join.form', ['code' => $presentation->join_code]);
        $qrImageUrl = 'https://quickchart.io/qr?text='.urlencode($joinUrl).'&size=280&margin=2';

        return view('present', [
            'presentation' => $presentation,
            'activeQuestion' => $activeQuestion,
            'totals' => $totals,
            'totalVotes' => $totalVotes,
            'joinUrl' => $joinUrl,
            'qrImageUrl' => $qrImageUrl,
        ]);
    }

    public function start(string $token)
    {
        $presentation = $this->presentationForHost($token);

        $presentation->join_code = $this->generateJoinCode();
        $presentation->is_live = true;
        $presentation->save();

        return redirect()->route('host.present', ['token' => $presentation->manage_token]);
    }

    public function stop(string $token)
    {
        $presentation = $this->presentationForHost($token);

        $presentation->is_live = false;
        $presentation->join_code = null;
        $presentation->save();

        return redirect()
            ->route('host.show', ['token' => $presentation->manage_token])
            ->with('status', 'Presentation ended. Participants can no longer join with the previous code.');
    }

    public function activateQuestion(Request $request, string $token, Question $question)
    {
        $presentation = $this->presentationForHost($token);

        abort_unless($question->presentation_id === $presentation->id, 404);

        $presentation->questions()->update(['is_active' => false]);
        $question->update(['is_active' => true]);

        return redirect()->route('host.show', ['token' => $presentation->manage_token]);
    }

    public function summary(Presentation $presentation): JsonResponse
    {
        $this->authorizeOwnedPresentation($presentation);

        $presentation->load(['questions.choices.responses', 'questions.responses.choice']);

        $questions = $presentation->questions->map(function (Question $question) {
            $totals = $question->choices->map(function ($choice) {
                return [
                    'label' => $choice->label,
                    'count' => $choice->responses->count(),
                ];
            })->values()->all();

            $participants = $question->responses
                ->sortBy('participant_name')
                ->values()
                ->map(function (Response $response) {
                    return [
                        'participant_name' => $response->participant_name ?? __('Anonymous'),
                        'choice_label' => $response->choice->label,
                    ];
                })
                ->all();

            return [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'totals' => $totals,
                'participants' => $participants,
            ];
        })->values()->all();

        return response()->json([
            'title' => $presentation->title,
            'questions' => $questions,
        ]);
    }

    public function resetResults(Request $request, Presentation $presentation)
    {
        $this->authorizeOwnedPresentation($presentation);

        $questionIds = $presentation->questions()->pluck('id');

        Response::query()->whereIn('question_id', $questionIds)->delete();

        $redirect = $request->input('redirect_to');

        if ($redirect === 'dashboard') {
            return redirect()
                ->route('dashboard')
                ->with('status', __('All poll results for this presentation have been cleared. You can start fresh.'));
        }

        return redirect()
            ->route('host.show', ['token' => $presentation->manage_token])
            ->with('status', __('All poll results have been cleared. You can start again.'));
    }

    private function authorizeOwnedPresentation(Presentation $presentation): void
    {
        abort_unless($presentation->user_id === auth()->id(), 403);
    }

    private function presentationForHost(string $token): Presentation
    {
        return Presentation::where('manage_token', $token)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    private function generateJoinCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (Presentation::where('join_code', $code)->exists());

        return $code;
    }
}
