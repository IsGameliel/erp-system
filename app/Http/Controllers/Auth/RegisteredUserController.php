<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\InstallationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly InstallationManager $installationManager)
    {
    }

    public function create(): View|RedirectResponse
    {
        abort_if($this->installationManager->installed(), 403);

        return redirect()->route('onboarding.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($this->installationManager->installed(), 403);

        return redirect()->route('onboarding.create');
    }
}
