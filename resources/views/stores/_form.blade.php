<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Store name</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $store->name) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="code">Store code</label>
        <input class="form-input" id="code" name="code" type="text" value="{{ old('code', $store->code) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="location">Location</label>
        <input class="form-input" id="location" name="location" type="text" value="{{ old('location', $store->location) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="sales_officer_id">Sales officer</label>
        <select class="form-select" id="sales_officer_id" name="sales_officer_id">
            <option value="">No sales officer assigned</option>
            @foreach ($salesOfficers as $salesOfficer)
                <option value="{{ $salesOfficer->id }}" @selected((string) old('sales_officer_id', $selectedSalesOfficerIds[0] ?? null) === (string) $salesOfficer->id)>{{ $salesOfficer->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="procurement_officer_id">Procurement officer</label>
        <select class="form-select" id="procurement_officer_id" name="procurement_officer_id">
            <option value="">No procurement officer assigned</option>
            @foreach ($procurementOfficers as $procurementOfficer)
                <option value="{{ $procurementOfficer->id }}" @selected((string) old('procurement_officer_id', $selectedProcurementOfficerIds[0] ?? null) === (string) $procurementOfficer->id)>{{ $procurementOfficer->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="description">Description</label>
        <textarea class="form-textarea" id="description" name="description">{{ old('description', $store->description) }}</textarea>
    </div>
</div>
