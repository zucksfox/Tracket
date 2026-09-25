@extends('layouts.app')

@section('title', 'Servis Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('services.index') }}" class="text-[13px] hover:underline" style="color: var(--act);">Kembali ke Daftar Servis</a>
            <h1 class="text-2xl font-bold t-ink tracking-tight mt-1">Terima servis baru</h1>
            <p class="text-[13px] t-muted mt-0.5">Catat unit masuk, keluhan kerusakan, kelengkapan, dan cetak surat tanda terima pelanggan.</p>
        </div>
        <div class="hidden sm:block shrink-0">
            <span class="tabular-nums text-[13px] font-semibold t-ink px-2.5 py-1 rounded-lg" style="border: 1px solid var(--line);">
                Kode otomatis: <span class="mono">SRV-{{ date('Ym') }}-XXXX</span>
            </span>
        </div>
    </div>

    <form action="{{ route('services.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between pb-3" style="border-bottom: 1px solid var(--line-soft);">
                <div class="flex items-center gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-6 h-6 flex items-center justify-center rounded-lg" style="border: 1px solid var(--line-soft);">1</span>
                    <h2 class="text-[14px] font-semibold t-ink">Identitas Pelanggan</h2>
                </div>
                <div id="lookup-badge" class="hidden text-[13px] px-2.5 py-1 rounded-lg font-semibold" style="background: var(--act-soft, #E3F3EA); border: 1px solid var(--line); color: var(--act-deep);"></div>
            </div>

            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="customer_phone" class="block mb-1">
                        Nomor WhatsApp / HP Pelanggan <span style="color: var(--rose);">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required
                            placeholder="Ketik No. HP (misal: 081234567890)..."
                            autocomplete="off" class="field tabular-nums">
                        <div id="phone-loading" class="hidden absolute right-3 top-2.5 t-muted text-[13px]">&#8635;</div>
                    </div>
                    <span class="text-[13px] t-muted mt-1 block">Ketik untuk pencarian otomatis pelanggan terdaftar.</span>
                </div>

                <div>
                    <label for="customer_name" class="block mb-1">
                        Nama Lengkap Pelanggan <span style="color: var(--rose);">*</span>
                    </label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required
                        placeholder="contoh: Hendra Pratama" class="field">
                </div>
            </div>

            <div>
                <label for="customer_address" class="block mb-1">
                    Alamat Domisili <span class="t-muted font-normal">(Opsional)</span>
                </label>
                <input type="text" id="customer_address" name="customer_address" value="{{ old('customer_address') }}"
                    placeholder="contoh: Jl. Merdeka No. 12, Jakarta" class="field">
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3" style="border-bottom: 1px solid var(--line-soft);">
                <span class="tabular-nums text-[13px] font-bold t-ink w-6 h-6 flex items-center justify-center rounded-lg" style="border: 1px solid var(--line-soft);">2</span>
                <h2 class="text-[14px] font-semibold t-ink">Detail Perangkat & Keluhan</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="device_name" class="block mb-1">
                        Tipe & Merk Perangkat <span style="color: var(--rose);">*</span>
                    </label>
                    <input type="text" id="device_name" name="device_name" value="{{ old('device_name') }}" required
                        placeholder="contoh: iPhone 13 128GB / Laptop ASUS TUF A15" class="field">
                </div>

                <div>
                    <label for="device_serial" class="block mb-1">
                        Nomor Seri / IMEI <span class="t-muted font-normal">(Opsional)</span>
                    </label>
                    <input type="text" id="device_serial" name="device_serial" value="{{ old('device_serial') }}"
                        placeholder="contoh: F2LWX891MD6P" class="field mono">
                </div>
            </div>

            <div>
                <label for="issue_description" class="block mb-1">
                    Deskripsi Keluhan / Gejala Kerusakan <span style="color: var(--rose);">*</span>
                </label>
                <textarea id="issue_description" name="issue_description" rows="3" required
                    placeholder="Jelaskan detail masalah perangkat, kronologi kejadian, atau gejala yang dirasakan pelanggan..." class="field">{{ old('issue_description') }}</textarea>
            </div>

            <div>
                <label for="accessories_included" class="block mb-1">
                    Kelengkapan Unit Dititipkan <span class="t-muted font-normal">(Opsional)</span>
                </label>
                <input type="text" id="accessories_included" name="accessories_included" value="{{ old('accessories_included') }}"
                    placeholder="contoh: Unit + Charger Original + Tas / Dus Box" class="field">
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3" style="border-bottom: 1px solid var(--line-soft);">
                <span class="tabular-nums text-[13px] font-bold t-ink w-6 h-6 flex items-center justify-center rounded-lg" style="border: 1px solid var(--line-soft);">3</span>
                <h2 class="text-[14px] font-semibold t-ink">Penugasan & Estimasi Awal</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="technician_id" class="block mb-1">
                        Tugaskan Teknisi <span class="t-muted font-normal">(Bisa diatur belakangan)</span>
                    </label>
                    <select id="technician_id" name="technician_id" class="field">
                        <option value="">-- Belum Ditugaskan (Masuk Antrian Umum) --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('technician_id') == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="labor_cost" class="block mb-1">
                        Estimasi Biaya Jasa Awal (Rp) <span class="t-muted font-normal">(Opsional, default 0)</span>
                    </label>
                    <input type="number" id="labor_cost" name="labor_cost" value="{{ old('labor_cost', 0) }}" min="0" step="5000" class="field tabular-nums">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-1">
            <a href="{{ route('services.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-act" style="padding: 10px 20px; font-size: 14px;">Simpan Penerimaan Servis</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Search-as-you-type Customer Phone Lookup
    const phoneInput = document.getElementById('customer_phone');
    const nameInput = document.getElementById('customer_name');
    const addressInput = document.getElementById('customer_address');
    const customerIdInput = document.getElementById('customer_id');
    const lookupBadge = document.getElementById('lookup-badge');
    const phoneLoading = document.getElementById('phone-loading');

    let debounceTimer;

    phoneInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 4) {
            lookupBadge.classList.add('hidden');
            customerIdInput.value = '';
            return;
        }

        phoneLoading.classList.remove('hidden');

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('customers.lookup') }}?phone=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    phoneLoading.classList.add('hidden');
                    if (data.found) {
                        nameInput.value = data.customer.name;
                        addressInput.value = data.customer.address || '';
                        customerIdInput.value = data.customer.id;
                        lookupBadge.textContent = `\u2713 Pelanggan Terdaftar: ${data.customer.name}`;
                        lookupBadge.classList.remove('hidden');
                    } else {
                        customerIdInput.value = '';
                        lookupBadge.classList.add('hidden');
                    }
                })
                .catch(() => {
                    phoneLoading.classList.add('hidden');
                });
        }, 350);
    });
</script>
@endpush
