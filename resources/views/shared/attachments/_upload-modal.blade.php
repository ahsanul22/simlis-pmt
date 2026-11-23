{{-- Modal unggah lampiran. TODO(tahap-backend): route attachments.store --}}
<x-modal name="upload-attachment" title="Unggah Dokumen">
    <form id="form-upload-attachment" method="POST" action="#" enctype="multipart/form-data" class="space-y-4"
          x-data="{ fileName: '' }" @submit.prevent="$dispatch('close-modal', 'upload-attachment')">
        @csrf
        <x-form.select name="category" label="Jenis dokumen" required
                       :options="\App\Enums\AttachmentCategory::optionsFor(auth()->user())" />
        <div>
            <label class="form-label">File <span class="text-rose-500">*</span></label>
            <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-4 py-8 text-center hover:border-brand-400 hover:bg-brand-50/40 dark:border-slate-700 dark:hover:bg-brand-500/5">
                <x-icon name="arrow-up-tray" class="h-6 w-6 text-slate-400" />
                <span class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200" x-text="fileName || 'Pilih file atau seret ke sini'"></span>
                <span class="mt-1 text-xs text-slate-500">PDF, JPG, PNG — maks. 5 MB</span>
                <input type="file" name="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" @change="fileName = $event.target.files[0]?.name ?? ''">
            </label>
        </div>
        <x-form.input name="description" label="Keterangan" placeholder="Opsional" />
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'upload-attachment')">Batal</button>
        <button type="submit" form="form-upload-attachment" class="btn btn-primary">Unggah</button>
    </x-slot:footer>
</x-modal>
