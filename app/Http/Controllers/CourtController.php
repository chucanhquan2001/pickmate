<?php

namespace App\Http\Controllers;

use App\Enums\CourtStatus;
use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use App\Models\Court;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourtController extends Controller
{
    use ResolvesClubModels;

    public function index(Request $request): Response
    {
        $courts = $this->club($request)->courts()
            ->orderBy('code')
            ->paginate(50)
            ->through(fn (Court $court) => Records::court($court));

        return Inertia::render('Courts/Index', [
            'courts' => $courts,
        ]);
    }

    public function store(StoreCourtRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] ??= CourtStatus::Active->value;
        $this->club($request)->courts()->create($data);

        return to_route('courts.index');
    }

    public function update(UpdateCourtRequest $request, int $court): RedirectResponse
    {
        $record = $this->court($request, $court);
        $record->update($request->validated());

        return to_route('courts.index');
    }
}
