<div class="form-section">
    <div class="form-copy"><h3>Create company</h3><p>Create a company and its primary Admin together. The Admin receives a secure password setup email.</p></div>
    <form class="form-grid" action="{{ route('companies.store') }}" method="POST">
        @csrf
        <div class="field"><label for="company-name">Company name</label><input id="company-name" name="company_name" type="text" value="{{ old('company_name') }}" placeholder="Company name" maxlength="150" required></div>
        <div class="field"><label for="primary-admin-name">Primary Admin</label><input id="primary-admin-name" name="admin_name" type="text" value="{{ old('admin_name') }}" placeholder="Admin name" maxlength="100" required></div>
        <div class="field"><label for="primary-admin-email">Admin email</label><input id="primary-admin-email" name="admin_email" type="email" value="{{ old('admin_email') }}" placeholder="admin@example.com" required></div>
        <div class="form-actions"><button class="button" type="submit">Create company &amp; invite Admin</button></div>
    </form>
</div>

@if($companies->isNotEmpty())
    <div class="form-section">
        <div class="form-copy"><h3>Add company Admin</h3><p>Invite another administrator to an existing company without changing its primary setup.</p></div>
        <form class="form-grid" action="{{ route('admin-invitations.store') }}" method="POST">
            @csrf
            <input name="role" type="hidden" value="admin">
            <div class="field"><label for="admin-company">Company</label><select id="admin-company" name="company_id" required><option value="">Select company</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected((string)old('company_id') === (string)$company->id)>{{ $company->name }}</option>@endforeach</select></div>
            <div class="field"><label for="company-admin-name">Full name</label><input id="company-admin-name" name="name" type="text" value="{{ old('name') }}" placeholder="Admin name" maxlength="100" required></div>
            <div class="field"><label for="company-admin-email">Email address</label><input id="company-admin-email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@example.com" required></div>
            <div class="form-actions"><button class="button" type="submit">Invite company Admin</button></div>
        </form>
    </div>
@endif
