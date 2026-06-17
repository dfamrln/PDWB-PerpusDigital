<?php
// Dipakai di modal tambah dan edit buku
$val = $editBuku ?? [];
$input = function($key) use ($val) { return e($val[$key] ?? ($_POST[$key] ?? '')); };
$kategoriList = $kategoriList ?? ['Novel', 'Self-Help', 'Sejarah', 'Keuangan', 'Teknologi', 'Filsafat', 'Fiksi', 'Lainnya'];
?>
<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Judul Buku *</label>
        <input type="text" name="judul" value="<?= $input('judul') ?>" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Pengarang *</label>
        <input type="text" name="pengarang" value="<?= $input('pengarang') ?>" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Penerbit</label>
        <input type="text" name="penerbit" value="<?= $input('penerbit') ?>"
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" value="<?= $input('tahun_terbit') ?>" min="1900" max="2099"
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
        <select name="kategori" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">-- Pilih --</option>
            <?php foreach ($kategoriList as $kat): ?>
                <option value="<?= e($kat) ?>" <?= $input('kategori') === $kat ? 'selected' : '' ?>><?= e($kat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">ISBN</label>
        <input type="text" name="isbn" value="<?= $input('isbn') ?>"
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Stok</label>
        <input type="number" name="stok" value="<?= $input('stok') ?: 1 ?>" min="1" required
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div class="col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="3"
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"><?= $input('deskripsi') ?></textarea>
    </div>
</div>
