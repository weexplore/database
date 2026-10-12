<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSource;
use App\Services\KnowledgeWebPageCaptureService;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KnowledgeItemSourceController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $this->validatedData($request);

        $source = $knowledgeItem->sources()->create($validated);

        return response()->json([
            'source' => $this->sourcePayload($source->fresh()),
        ], 201);
    }

    public function update(
        Request $request,
        KnowledgeItem $knowledgeItem,
        KnowledgeSource $knowledgeSource
    ) {
        $this->ensureOwnership($knowledgeItem, $knowledgeSource);

        $validated = $this->validatedData($request);

        $knowledgeSource->update($validated);

        return response()->json([
            'source' => $this->sourcePayload($knowledgeSource->fresh()),
        ]);
    }

    public function destroy(
        KnowledgeItem $knowledgeItem,
        KnowledgeSource $knowledgeSource
    ) {
        $this->ensureOwnership($knowledgeItem, $knowledgeSource);

        $knowledgeSource->delete();

        return response()->noContent();
    }

    public function fetchFromInternet(
    Request $request,
    KnowledgeItem $knowledgeItem,
    KnowledgeWebPageCaptureService $capture
) {
    if (
        \Illuminate\Support\Facades\Gate::getPolicyFor($knowledgeItem)
        !== null
    ) {
        \Illuminate\Support\Facades\Gate::authorize(
            'update',
            $knowledgeItem
        );
    }

    $validated = $request->validate([
        'fetch_url' => [
            'required',
            'url:http,https',
            'max:255',
        ],
    ]);

    $url = trim($validated['fetch_url']);

    try {
        $draft = $capture->capture($url);

        if (mb_strlen($draft['sourceurl']) > 255) {
            throw new \RuntimeException(
                'The final webpage URL exceeds the current 255-character source URL limit. Import its text manually instead.'
            );
        }

        $draft['retrievedon'] = now()->format('Y-m-d');
        $draft['researchquery'] = $knowledgeItem->itemname;

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'sources',
            ])
            ->with('knowledge_research_draft', $draft)
            ->with(
                'success',
                'Webpage text fetched. Review it in the Research Workspace, then save the source.'
            );
    } catch (\RuntimeException $exception) {
        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'sources',
                'show_fetch_source' => 1,
            ])
            ->withInput([
                'fetch_url' => $url,
            ])
            ->withErrors([
                'fetch_url' => $exception->getMessage(),
            ]);
    } catch (\Throwable $exception) {
        report($exception);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'sources',
                'show_fetch_source' => 1,
            ])
            ->withInput([
                'fetch_url' => $url,
            ])
            ->withErrors([
                'fetch_url' =>
                    'Unable to fetch or process that webpage. No source was saved.',
            ]);
    }
}

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'sourcetype' => [
                'required',
                'string',
                Rule::in(KnowledgeSource::typeValues()),
            ],
            'sourceurl' => ['nullable', 'url', 'max:255'],
            'sourcetitle' => ['required', 'string', 'max:255'],
            'sourcepublisher' => ['nullable', 'string', 'max:150'],
            'retrievedon' => ['nullable', 'date'],
            'importedsummary' => ['nullable', 'string'],
            'importednotes' => ['nullable', 'string'],
            'importstatus' => ['required', 'string', 'max:30'],
            'reviewedon' => ['nullable', 'date'],
            'reviewedby' => ['nullable', 'string', 'max:100'],
            'internalnotes' => ['nullable', 'string'],
        ]);
    }

    private function ensureOwnership(
        KnowledgeItem $knowledgeItem,
        KnowledgeSource $knowledgeSource
    ): void {
        abort_unless(
            (int) $knowledgeSource->knowledgeitemid === (int) $knowledgeItem->id,
            404
        );
    }

    private function sourcePayload(KnowledgeSource $source): array
    {
        $sourceTypeOptions = KnowledgeSource::typeOptions();

        return [
            'id' => $source->id,
            'sourcetype' => $source->sourcetype,
            'sourcetype_label' => $sourceTypeOptions[$source->sourcetype]
                ?? $source->sourcetype
                ?? 'Source',
            'sourceurl' => $source->sourceurl ?? '',
            'sourcetitle' => $source->sourcetitle ?? '',
            'sourcepublisher' => $source->sourcepublisher ?? '',
            'retrievedon' => $source->retrievedon?->format('Y-m-d'),
            'retrievedon_display' => $source->retrievedon?->format('d M Y'),
            'importedsummary' => $source->importedsummary ?? '',
            'importedsummary_html' => app(Markdown::class)
                ->parse($source->importedsummary ?? '')
                ->toHtml(),
            'importednotes' => $source->importednotes ?? '',
            'importednotes_html' => app(Markdown::class)
                ->parse($source->importednotes ?? '')
                ->toHtml(),
            'importstatus' => $source->importstatus ?? '',
            'reviewedon' => $source->reviewedon?->format('Y-m-d'),
            'reviewedon_display' => $source->reviewedon?->format('d M Y'),
            'reviewedby' => $source->reviewedby ?? '',
            'internalnotes' => $source->internalnotes ?? '',
            'internalnotes_html' => app(Markdown::class)
                ->parse($source->internalnotes ?? '')
                ->toHtml(),
        ];
    }
}