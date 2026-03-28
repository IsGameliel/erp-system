<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        return view('subscription-plans.index', [
            'plans' => SubscriptionPlan::query()->latest()->paginate(12),
        ]);
    }

    public function create()
    {
        return view('subscription-plans.create', [
            'plan' => new SubscriptionPlan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $plan = SubscriptionPlan::create($data);

        $this->activityLogService->log($request->user()?->id, 'created', 'subscription_plans', "Created subscription plan {$plan->name}.", $plan);

        return redirect()->route('owner.subscription-plans.index')->with('success', 'Subscription plan created successfully.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('subscription-plans.edit', [
            'plan' => $subscriptionPlan,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $data = $this->validatedData($request, $subscriptionPlan->id);
        $subscriptionPlan->update($data);

        $this->activityLogService->log($request->user()?->id, 'updated', 'subscription_plans', "Updated subscription plan {$subscriptionPlan->name}.", $subscriptionPlan);

        return redirect()->route('owner.subscription-plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $name = $subscriptionPlan->name;
        $subscriptionPlan->delete();

        $this->activityLogService->log($request->user()?->id, 'deleted', 'subscription_plans', "Deleted subscription plan {$name}.");

        return redirect()->route('owner.subscription-plans.index')->with('success', 'Subscription plan deleted successfully.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $exists = SubscriptionPlan::query()
            ->where('slug', $data['slug'])
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            $data['slug'] .= '-'.Str::lower(Str::random(4));
        }

        return $data;
    }
}
