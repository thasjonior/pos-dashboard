<x-admin-layout title="Register Machine">
    <div class="mb-4"><a href="{{ route('admin.machines.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Machines</a></div>
    <x-admin.page-header title="Register Machine" subtitle="Creates machine and collector account in one transaction." />

    <form method="POST" action="{{ route('admin.machines.store') }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-2xl"
          x-data="machineForm()">
        @csrf

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm space-y-1">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Company <span class="text-red-500">*</span></label>
                <select name="company_id" @change="suggestName()" x-model="companyName" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('company_id') border-red-400 @enderror">
                    <option value="">Select company…</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" data-name="{{ $c->name }}" @selected(old('company_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('company_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Serial Number <span class="text-red-500">*</span></label>
                <input type="text" name="serial_number" value="{{ old('serial_number') }}" @input="suggestName()" x-model="serial" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('serial_number') border-red-400 @enderror">
                @error('serial_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Machine Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" x-model="machineName" value="{{ old('name') }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none @error('name') border-red-400 @enderror">
            <p class="text-xs text-gray-400 mt-1">Auto-suggested from company + serial (e.g. Sateki52). Edit if needed.</p>
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="mobile" @selected(old('type','mobile')==='mobile')>Mobile</option>
                    <option value="terminal" @selected(old('type')==='terminal')>Terminal</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="active" @selected(old('status','active')==='active')>Active</option>
                    <option value="inactive" @selected(old('status')==='inactive')>Inactive</option>
                    <option value="maintenance" @selected(old('status')==='maintenance')>Maintenance</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Installation Date <span class="text-red-500">*</span></label>
                <input type="date" name="installation_date" value="{{ old('installation_date', now()->toDateString()) }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('installation_date') border-red-400 @enderror">
                @error('installation_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
        </div>

        <hr class="border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Device Account</h3>
        <p class="text-xs text-gray-400 -mt-2">Login credentials for this device. Display name defaults to the machine name.</p>

        <div>
            <label class="block text-sm font-medium mb-1">Phone <span class="text-gray-400 font-normal">(optional)</span></label>
            <input type="text" name="account_phone" value="{{ old('account_phone') }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('account_phone') border-red-400 @enderror">
            @error('account_phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Password <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <input type="text" name="account_password" x-model="password" value="{{ old('account_password') }}" required
                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none @error('account_password') border-red-400 @enderror">
                <button type="button" @click="generatePassword()"
                        class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition">Generate</button>
            </div>
            @error('account_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
                Register Machine
            </button>
        </div>
    </form>

    <script>
        function machineForm() {
            return {
                companyName: '{{ old('company_id') }}',
                serial: '{{ old('serial_number') }}',
                machineName: '{{ old('name') }}',
                password: '{{ old('collector_password') }}',
                suggestName() {
                    const sel = document.querySelector('[name=company_id]');
                    const opt = sel.options[sel.selectedIndex];
                    const compName = opt?.dataset?.name ?? '';
                    const prefix = compName.split(' ')[0] ?? '';
                    if (prefix && this.serial) {
                        this.machineName = prefix + this.serial;
                    }
                },
                generatePassword() {
                    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#';
                    this.password = Array.from({length: 12}, () => chars[Math.floor(Math.random() * chars.length)]).join('');
                },
            };
        }
    </script>
</x-admin-layout>
