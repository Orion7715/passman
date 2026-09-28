<div class="flex flex-wrap gap-3 mb-10 items-center max-w-6xl mx-auto">
    <span class="text-xs text-[#d4af37] uppercase tracking-[0.2em] font-black mr-2 opacity-80">
        Filters:
    </span>
    <button onclick="filterCategory('all', this)" 
        class="category-pill active px-6 py-2 bg-white/5 border border-white/10 text-white/60 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 hover:border-[#926a2d]/50 hover:bg-white/10 active:scale-95 shadow-sm">
        All Units
    </button>
    <?php 
    $cats = ['Work', 'Education', 'Social', 'Finance', 'Personal', 'Shopping', 'General'];
    foreach($cats as $c): ?>
        <button onclick="filterCategory('<?= $c ?>', this)" 
            class="category-pill px-6 py-2 bg-white/5 border border-white/10 text-white/60 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 hover:border-[#d4af37]/50 hover:bg-white/10 active:scale-95 shadow-sm">
            <?= $c ?>
        </button>
    <?php endforeach; ?>
</div>

<style>
.category-pill.active {
    background: linear-gradient(to right, #926a2d, #d4af37) !important;
    color: black !important;
    border-color: transparent !important;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}
</style>