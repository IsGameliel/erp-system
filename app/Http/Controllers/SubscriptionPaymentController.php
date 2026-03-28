<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function status(Request $request)
    {
        $organization = $request->user()->organization;

        return view('subscriptions.status', [
            'plans' => SubscriptionPlan::query()->where('is_active', true)->orderBy('price')->get(),
            'organization' => $organization,
            'payments' => $organization
                ? $organization->subscriptionPayments()->with(['plan', 'user', 'approver'])->latest()->get()
                : collect(),
        ]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'payment_reference' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = SubscriptionPlan::query()->findOrFail($data['subscription_plan_id']);
        $organization = $request->user()->organization;

        abort_if(! $organization, 422, 'No organization is assigned to this user.');

        $payment = SubscriptionPayment::create([
            'organization_id' => $organization->id,
            'submitted_by' => $request->user()->id,
            'subscription_plan_id' => $plan->id,
            'status' => SubscriptionPayment::STATUS_PENDING,
            'payment_reference' => $data['payment_reference'],
            'amount' => $plan->price,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->activityLogService->log($request->user()->id, 'created', 'subscription_payments', "Submitted payment request for {$organization->name}.", $payment);

        return redirect()->route('subscriptions.status')->with('success', 'Plan selected and payment submitted. Awaiting owner approval.');
    }

    public function index(Request $request)
    {
        return view('subscriptions.index', [
            'payments' => SubscriptionPayment::query()
                ->with(['organization', 'user', 'plan', 'approver'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => SubscriptionPayment::STATUSES,
        ]);
    }

    public function approve(Request $request, SubscriptionPayment $subscriptionPayment)
    {
        $plan = $subscriptionPayment->plan;
        $durationMonths = max(1, (int) ($plan?->duration_months ?? 12));
        $startsAt = now()->startOfDay();
        $endsAt = $startsAt->copy()->addMonthsNoOverflow($durationMonths)->subDay();

        $subscriptionPayment->update([
            'status' => SubscriptionPayment::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'paid_at' => now(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        $organization = $subscriptionPayment->organization;
        abort_if(! $organization, 422, 'This payment is not linked to an organization.');

        $organization->update([
            'subscription_active' => true,
            'subscription_expires_at' => $endsAt,
            'current_subscription_plan_id' => $plan?->id,
        ]);

        $organization->users()->update([
            'access_enabled' => true,
            'access_expires_at' => $endsAt,
        ]);

        $this->activityLogService->log($request->user()->id, 'approved', 'subscription_payments', "Approved payment for {$organization->name}.", $subscriptionPayment);

        return back()->with('success', 'Payment approved and subscription access activated.');
    }

    public function cancel(Request $request, SubscriptionPayment $subscriptionPayment)
    {
        $subscriptionPayment->update([
            'status' => SubscriptionPayment::STATUS_CANCELLED,
            'approved_by' => $request->user()->id,
        ]);

        $organization = $subscriptionPayment->organization;
        abort_if(! $organization, 422, 'This payment is not linked to an organization.');

        $organization->update([
            'subscription_active' => false,
            'subscription_expires_at' => null,
        ]);

        $organization->users()->update([
            'access_enabled' => false,
        ]);

        $this->activityLogService->log($request->user()->id, 'cancelled', 'subscription_payments', "Cancelled subscription payment for {$organization->name}.", $subscriptionPayment);

        return back()->with('success', 'Subscription cancelled and access disabled.');
    }

    public function reject(Request $request, SubscriptionPayment $subscriptionPayment)
    {
        $subscriptionPayment->update([
            'status' => SubscriptionPayment::STATUS_REJECTED,
            'approved_by' => $request->user()->id,
        ]);

        $this->activityLogService->log($request->user()->id, 'rejected', 'subscription_payments', "Rejected payment for {$subscriptionPayment->organization?->name}.", $subscriptionPayment);

        return back()->with('success', 'Payment request rejected.');
    }

    public function extend(Request $request, User $user)
    {
        $data = $request->validate([
            'access_expires_at' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $organization = $user->organization;

        $user->update([
            'access_enabled' => true,
            'access_expires_at' => $data['access_expires_at'],
        ]);

        if ($organization) {
            $organization->update([
                'subscription_active' => true,
                'subscription_expires_at' => $data['access_expires_at'],
            ]);

            $organization->users()->update([
                'access_enabled' => true,
                'access_expires_at' => $data['access_expires_at'],
            ]);
        }

        $this->activityLogService->log($request->user()->id, 'extended', 'subscriptions', "Extended subscription for {$user->name} until {$data['access_expires_at']}.", $user);

        return back()->with('success', 'Subscription extended successfully.');
    }
}
