<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeNote;
use App\Models\KnowledgeReviewLog;
use App\Services\KnowledgeTopicSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KnowledgeItemResearchController extends Controller
{
    private function checkItem(KnowledgeItem $item): void
    {
        // Routes require authentication; honour an existing item policy as well.
        if (Gate::getPolicyFor($item) !== null) Gate::authorize('update', $item);
    }

    private function checkSource(KnowledgeItem $item, KnowledgeSource $source): void
    {
        $this->checkItem($item);
        abort_unless((string) $source->knowledgeitemid === (string) $item->getKey(), 404);
    }

    private function content(KnowledgeSource $source): string
    {
        return (string) ($source->rawcontent ?? $source->importednotes ?? '');
    }

    private function version(KnowledgeSource $source): string
    {
        return hash('sha256', json_encode($source->getAttributes(), JSON_THROW_ON_ERROR));
    }

    private function payload(KnowledgeSource $source): array
    {
        return [
            'id' => $source->id,
            'version' => $this->version($source),
            'sourcetype' => $source->sourcetype ?? 'document',
            'sourcetitle' => $source->sourcetitle ?? '',
            'sourceurl' => $source->sourceurl ?? '',
            'sourcepublisher' => $source->sourcepublisher ?? '',
            'retrievedon' => $source->retrievedon?->format('Y-m-d') ?? '',
            'rawcontent' => $this->content($source),
            'contentformat' => $source->contentformat ?? 'markdown',
            'researchquery' => $source->researchquery ?? '',
        ];
    }

    public function search(Request $request, KnowledgeItem $knowledgeItem, KnowledgeTopicSearchService $search)
    {
        $this->checkItem($knowledgeItem);
        $data = $request->validate(['query' => ['required', 'string', 'max:500']]);
        $query = trim($data['query']);
        if ($query === '') throw ValidationException::withMessages(['query' => 'Enter a topic or question.']);
        return response()->json(['results' => $search->search($query)]);
    }

    public function showSource(KnowledgeItem $knowledgeItem, KnowledgeSource $knowledgeSource)
    {
        $this->checkSource($knowledgeItem, $knowledgeSource);
        return response()->json(['source' => $this->payload($knowledgeSource)]);
    }

    private function sourceData(Request $request): array
    {
        return $request->validate([
            'sourcetype' => ['required', Rule::in(KnowledgeSource::typeValues())],
            'sourcetitle' => ['required', 'string', 'max:255'],
            'sourceurl' => ['nullable', 'url:http,https', 'max:255'],
            'sourcepublisher' => ['nullable', 'string', 'max:150'],
            'retrievedon' => ['nullable', 'date_format:Y-m-d'],
            'rawcontent' => ['required', 'string', 'max:'.config('knowledge_research.max_content_characters', 500000)],
            'contentformat' => ['required', Rule::in(['text', 'markdown'])],
            'researchquery' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function assignSource(KnowledgeSource $source, array $data): void
    {
        $data['sourcetitle'] = trim($data['sourcetitle']);
        if ($data['sourcetitle'] === '' || trim($data['rawcontent']) === '') {
            throw ValidationException::withMessages(['rawcontent' => 'A title and non-empty research content are required.']);
        }
        foreach ($data as $field => $value) $source->{$field} = $value;
        $source->importstatus = 'pendingreview';
        $source->reviewedon = null;
        $source->reviewedby = null;
        $source->save();
    }

    public function storeSource(Request $request, KnowledgeItem $knowledgeItem)
    {
        $this->checkItem($knowledgeItem);
        $data = $this->sourceData($request);
        $source = new KnowledgeSource();
        $source->knowledgeitemid = $knowledgeItem->getKey();
        $this->assignSource($source, $data);
        return response()->json(['source' => $this->payload($source->fresh())], 201);
    }

    public function updateSource(Request $request, KnowledgeItem $knowledgeItem, KnowledgeSource $knowledgeSource)
    {
        $this->checkSource($knowledgeItem, $knowledgeSource);
        $data = $this->sourceData($request);
        $expected = $request->validate(['version' => ['required', 'string', 'size:64']])['version'];
        $source = DB::transaction(function () use ($knowledgeSource, $data, $expected) {
            $source = KnowledgeSource::query()->lockForUpdate()->findOrFail($knowledgeSource->getKey());
            abort_unless(hash_equals($this->version($source), $expected), 409,
                'This source has changed. Keep a copy of your draft, then reopen the source before saving.');
            $this->assignSource($source, $data);
            return $source->fresh();
        });
        return response()->json(['source' => $this->payload($source)]);
    }

    public function detailedNotes(KnowledgeItem $knowledgeItem)
    {
        $this->checkItem($knowledgeItem);
        $content = (string) $knowledgeItem->detailednotes;
        return response()->json(['content' => $content, 'hash' => hash('sha256', $content)]);
    }

    public function apply(Request $request, KnowledgeItem $knowledgeItem, KnowledgeSource $knowledgeSource)
    {
        $this->checkSource($knowledgeItem, $knowledgeSource);
        $data = $request->validate([
            'target' => ['required', Rule::in(['note', 'review', 'detailednotes'])],
            'content' => ['required', 'string', 'max:'.config('knowledge_research.max_content_characters', 500000)],
            'title' => ['nullable', 'string', 'max:255'],
            'include_source' => ['required', 'boolean'],
            'notetype' => ['required_if:target,note', 'nullable', Rule::in(KnowledgeNote::typeValues())],
            'isprivate' => ['required_if:target,note', 'nullable', 'boolean'],
            'reviewtype' => ['required_if:target,review', 'nullable', Rule::in(KnowledgeReviewLog::typeValues())],
            'reviewdate' => ['required_if:target,review', 'nullable', 'date_format:Y-m-d'],
            'outcome' => ['nullable', 'string', 'max:50'],
            'nextreviewdate' => ['nullable', 'date_format:Y-m-d'],
            'expected_hash' => ['required_if:target,detailednotes', 'nullable', 'string', 'size:64'],
        ]);
        $text = trim($data['content']);
        if ($text === '') throw ValidationException::withMessages(['content' => 'Select or enter some content.']);

        if ($data['include_source']) {
            $title = preg_replace('/\s+/u', ' ', (string) $knowledgeSource->sourcetitle);
            $url = (string) $knowledgeSource->sourceurl;
            $attribution = 'Source: '.$title.' (Knowledge source #'.$knowledgeSource->id.')';
            if (filter_var($url, FILTER_VALIDATE_URL)
                && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                $attribution .= "\n\n".$url;
            }
            $text .= "\n\n---\n\n".$attribution;
        }

        if ($data['target'] === 'review' && strlen($text) > 60000) {
            throw ValidationException::withMessages([
                'content' => 'This selection is too large for the review-summary TEXT column. Split it or create a Note instead.',
            ]);
        }

        $result = DB::transaction(function () use ($knowledgeItem, $knowledgeSource, $data, $text) {
            $item = KnowledgeItem::query()->lockForUpdate()->findOrFail($knowledgeItem->getKey());
            // Verify the source still belongs to this item and prevent deletion during application.
            $source = $item->sources()->lockForUpdate()->findOrFail($knowledgeSource->getKey());
            if ($data['target'] === 'note') {
                $note = $item->notes()->create([
                    'notetype' => $data['notetype'],
                    'title' => $data['title'] ?? null,
                    'notecontent' => $text,
                    'isprivate' => (bool) $data['isprivate'],
                    'isactive' => true,
                    'sortorder' => ((int) $item->notes()->max('sortorder')) + 1,
                ]);
                $note->knowledgesourceid = $source->getKey();
                $note->save();
                return ['id' => $note->id, 'message' => 'Note created.'];
            }
            if ($data['target'] === 'review') {
                $log = $item->reviewLogs()->create([
                    'reviewdate' => $data['reviewdate'],
                    'reviewtype' => $data['reviewtype'],
                    'outcome' => $data['outcome'] ?? null,
                    'summary' => $text,
                    'nextreviewdate' => $data['nextreviewdate'] ?? null,
                    'isactive' => true,
                ]);
                $log->knowledgesourceid = $source->getKey();
                $log->save();
                return ['id' => $log->id, 'message' => 'Review log created. The item next-review date was not changed.'];
            }
            $existing = (string) $item->detailednotes;
            abort_unless(hash_equals(hash('sha256', $existing), $data['expected_hash']), 409,
                'Detailed Notes changed after this draft was opened. Cancel and reopen the addition to review the latest text.');
            $heading = trim((string) ($data['title'] ?? ''));
            $addition = ($heading !== '' ? '## '.preg_replace('/\s+/u', ' ', $heading)."\n\n" : '').$text;
            $item->detailednotes = rtrim($existing).($existing !== '' ? "\n\n" : '').$addition;
            $item->save();
            return ['id' => $item->id, 'message' => 'Selection appended to Detailed Notes. Existing content was retained.'];
        });
        return response()->json($result, 201);
    }
}
