<?php

namespace App\Http\Controllers\Children;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Condition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Todas las acciones de este controlador están protegidas por la Gate
 * 'access-children-module' a nivel de grupo de rutas (routes/web.php) — ni
 * siquiera 'admin' entra por defecto, así que no hace falta repetir la
 * autorización acá.
 */
class ChildController extends Controller
{
    public function index(): Response
    {
        $children = Child::query()
            ->with('conditions:id,name')
            ->withCount([
                'needs as pending_needs_count' => fn ($query) => $query->where('status', 'pendiente'),
                'needs as covered_needs_count' => fn ($query) => $query->where('status', 'cubierta'),
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('Children/Index', [
            'children' => $children,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Children/Create', [
            'conditions' => Condition::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateChild($request);
        [$conditionIds, $newConditionNames] = $this->extractConditions($request);

        DB::transaction(function () use ($validated, $conditionIds, $newConditionNames, $request): void {
            $child = Child::create([
                ...$validated,
                'created_by' => $request->user()->id,
            ]);

            $child->conditions()->sync($this->resolveConditionIds($conditionIds, $newConditionNames));
        });

        return redirect()->route('children.index');
    }

    public function show(Child $child): Response
    {
        $child->load('conditions:id,name');

        $needs = $child->needs()
            ->with([
                'creator:id,name',
                'fulfillments' => fn ($query) => $query->latest('fulfilled_at'),
                'fulfillments.fulfilledBy:id,name',
                'fulfillments.donation:id,status',
            ])
            ->latest()
            ->get();

        return Inertia::render('Children/Show', [
            'child' => $child,
            'needs' => $needs,
        ]);
    }

    public function edit(Child $child): Response
    {
        $child->load('conditions:id,name');

        return Inertia::render('Children/Edit', [
            'child' => $child,
            'conditions' => Condition::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Child $child): RedirectResponse
    {
        $validated = $this->validateChild($request);
        [$conditionIds, $newConditionNames] = $this->extractConditions($request);

        DB::transaction(function () use ($child, $validated, $conditionIds, $newConditionNames): void {
            $child->update($validated);

            $child->conditions()->sync($this->resolveConditionIds($conditionIds, $newConditionNames));
        });

        return redirect()->route('children.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateChild(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'condition_ids' => ['sometimes', 'array'],
            'condition_ids.*' => ['string', Rule::exists('conditions', 'id')],
            'new_condition_names' => ['sometimes', 'array'],
            'new_condition_names.*' => ['string', 'max:255'],
        ]);
    }

    /**
     * @return array{0: list<string>, 1: list<string>}
     */
    private function extractConditions(Request $request): array
    {
        return [
            $request->input('condition_ids', []),
            $request->input('new_condition_names', []),
        ];
    }

    /**
     * Las condiciones nuevas se crean en el catálogo antes de asociarlas —
     * así quedan disponibles para el próximo niño sin que nadie tenga que
     * ir a crearlas aparte primero.
     *
     * @param  list<string>  $conditionIds
     * @param  list<string>  $newConditionNames
     * @return list<string>
     */
    private function resolveConditionIds(array $conditionIds, array $newConditionNames): array
    {
        $createdIds = collect($newConditionNames)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => Condition::firstOrCreate(['name' => $name])->id)
            ->all();

        return array_values(array_unique([...$conditionIds, ...$createdIds]));
    }
}
